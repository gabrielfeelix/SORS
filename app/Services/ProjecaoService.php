<?php

namespace App\Services;

use App\Models\Account;
use App\Models\RecorrenciaGrupo;
use App\Models\Transaction;
use App\Services\Recorrencias\RecorrenciaScheduler;
use Carbon\CarbonImmutable;

class ProjecaoService
{
    public function __construct(
        private readonly RecorrenciaScheduler $scheduler,
    ) {
    }

    public function calcularProjecao30Dias(int $userId): array
    {
        $today = CarbonImmutable::today();
        $end = $today->addDays(30);

        $saldoAtual = (float) Account::query()
            ->where('user_id', $userId)
            ->includedInNetWorth()
            ->sum('current_balance');

        $transacoes = Transaction::query()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->whereBetween('transaction_date', [$today->toDateString(), $end->toDateString()])
            ->orderBy('transaction_date', 'asc')
            ->get(['id', 'kind', 'amount', 'transaction_date', 'recorrencia_grupo_id']);

        $extrasRecorrencias = $this->simularRecorrenciasNaoCriadas($userId, $today, $end);

        $porDia = [];
        foreach ($transacoes as $t) {
            $key = CarbonImmutable::parse($t->transaction_date)->toDateString();
            $porDia[$key][] = ['kind' => $t->kind, 'amount' => (float) $t->amount];
        }
        foreach ($extrasRecorrencias as $extra) {
            $porDia[$extra['date']][] = ['kind' => $extra['kind'], 'amount' => (float) $extra['amount']];
        }

        $projecaoDiaria = [];
        $saldoProjetado = $saldoAtual;
        $primeiroDiaNegativo = null;

        for ($i = 0; $i <= 30; $i++) {
            $dia = $today->addDays($i);
            $key = $dia->toDateString();

            foreach (($porDia[$key] ?? []) as $item) {
                if ($item['kind'] === 'income') {
                    $saldoProjetado += $item['amount'];
                } else {
                    $saldoProjetado -= $item['amount'];
                }
            }

            $projecaoDiaria[] = [
                'data' => $key,
                'saldo' => round($saldoProjetado, 2),
            ];

            if ($saldoProjetado < 0 && $primeiroDiaNegativo === null) {
                $primeiroDiaNegativo = $dia->format('d/m');
            }
        }

        $alerta = null;
        if ($primeiroDiaNegativo !== null) {
            $alerta = [
                'tipo' => 'negativo',
                'mensagem' => "Cuidado! No ritmo atual, seu saldo ficará negativo dia {$primeiroDiaNegativo}",
            ];
        }

        return [
            'projecao_diaria' => $projecaoDiaria,
            'saldo_dia_30' => round($saldoProjetado, 2),
            'primeiro_dia_negativo' => $primeiroDiaNegativo,
            'alerta' => $alerta,
        ];
    }

    public function gerarInsights(int $userId): array
    {
        $today = CarbonImmutable::today();
        $inicioMes = $today->startOfMonth();
        $fimMes = $today->endOfMonth();

        $projecao = $this->calcularProjecao30Dias($userId);
        $primeiroDiaNegativo = $projecao['primeiro_dia_negativo'];

        $diasPassados = max(1, $today->diffInDays($inicioMes) + 1);

        $totalDespesasMes = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('kind', 'expense')
            ->where('status', 'paid')
            ->whereBetween('data_pagamento', [$inicioMes->startOfDay(), $today->endOfDay()])
            ->sum('amount');

        $mediaGastoDiario = $diasPassados > 0 ? ($totalDespesasMes / $diasPassados) : 0.0;

        $diasRestantes = max(0, $today->diffInDays($fimMes));
        $gastoProjetado = $mediaGastoDiario * $diasRestantes;

        $receitasPendentes = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('kind', 'income')
            ->where('status', 'pending')
            ->whereBetween('transaction_date', [$today->toDateString(), $fimMes->toDateString()])
            ->sum('amount');

        $saldoAtual = (float) Account::query()
            ->where('user_id', $userId)
            ->includedInNetWorth()
            ->sum('current_balance');

        $extrasRecorrenciasMes = $this->simularRecorrenciasNaoCriadas($userId, $today, $fimMes);
        $despesasRecorrenciasMes = array_sum(array_map(
            fn (array $extra) => $extra['kind'] === 'expense' ? (float) $extra['amount'] : 0.0,
            $extrasRecorrenciasMes
        ));

        $despesasPendentesMes = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('kind', 'expense')
            ->where('status', 'pending')
            ->whereBetween('transaction_date', [$today->toDateString(), $fimMes->toDateString()])
            ->sum('amount');

        $saldoFinalEstimado = $saldoAtual + $receitasPendentes - ($despesasPendentesMes + $despesasRecorrenciasMes);

        $insights = [];

        if ($primeiroDiaNegativo) {
            $insights[] = [
                'tipo' => 'alerta',
                'icone' => 'alert-triangle',
                'cor' => 'red',
                'titulo' => 'Atenção ao saldo!',
                'mensagem' => "No ritmo atual, seu saldo ficará negativo dia {$primeiroDiaNegativo}",
            ];
        } elseif ($saldoFinalEstimado > 0) {
            $margem = round($saldoFinalEstimado, 2);
            $insights[] = [
                'tipo' => 'positivo',
                'icone' => 'check-circle',
                'cor' => 'green',
                'titulo' => 'Tá tranquilo!',
                'mensagem' => "Você pode gastar até R$ {$margem} até o fim do mês",
            ];
        } else {
            $deficit = abs(round($saldoFinalEstimado, 2));
            $insights[] = [
                'tipo' => 'atencao',
                'icone' => 'alert-circle',
                'cor' => 'yellow',
                'titulo' => 'Atenção!',
                'mensagem' => "Você está R$ {$deficit} acima do que tem disponível para este mês",
            ];
        }

        $mesPassado = $today->subMonthNoOverflow();
        $totalDespesasMesPassado = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('kind', 'expense')
            ->where('status', 'paid')
            ->whereBetween('data_pagamento', [$mesPassado->startOfMonth()->startOfDay(), $mesPassado->endOfMonth()->endOfDay()])
            ->sum('amount');

        if ($totalDespesasMes > 0 && $totalDespesasMesPassado > 0) {
            $variacao = (($totalDespesasMes - $totalDespesasMesPassado) / $totalDespesasMesPassado) * 100;
            $variacaoFormatada = (int) round(abs($variacao));

            if ($variacao > 10) {
                $insights[] = [
                    'tipo' => 'info',
                    'icone' => 'trending-up',
                    'cor' => 'red',
                    'titulo' => "Você gastou {$variacaoFormatada}% a mais que o mês passado",
                    'mensagem' => "Suas despesas aumentaram. Revise seus gastos em 'Análise'",
                ];
            } elseif ($variacao < -10) {
                $insights[] = [
                    'tipo' => 'positivo',
                    'icone' => 'trending-down',
                    'cor' => 'green',
                    'titulo' => "Você economizou {$variacaoFormatada}% em relação ao mês passado",
                    'mensagem' => 'Continue assim! Suas finanças estão melhorando',
                ];
            }
        }

        return [
            'insights' => $insights,
            'metricas' => [
                'media_gasto_diario' => round($mediaGastoDiario, 2),
                'gasto_projetado_mes' => round($gastoProjetado, 2),
                'saldo_final_estimado' => round($saldoFinalEstimado, 2),
            ],
        ];
    }

    private function simularRecorrenciasNaoCriadas(int $userId, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $grupos = RecorrenciaGrupo::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($q) use ($start) {
                $q->whereNull('data_fim')->orWhere('data_fim', '>=', $start->toDateString());
            })
            ->get();

        if ($grupos->isEmpty()) {
            return [];
        }

        $existing = Transaction::query()
            ->where('user_id', $userId)
            ->whereNotNull('recorrencia_grupo_id')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->get(['recorrencia_grupo_id', 'transaction_date'])
            ->groupBy('recorrencia_grupo_id')
            ->map(fn ($items) => $items->map(fn ($i) => CarbonImmutable::parse($i->transaction_date)->toDateString())->all())
            ->all();

        $extras = [];

        foreach ($grupos as $grupo) {
            $knownDates = $existing[$grupo->id] ?? [];

            $cursor = CarbonImmutable::parse($grupo->data_inicio);
            if ($cursor->lessThan($start)) {
                while ($cursor->lessThan($start)) {
                    $cursor = $this->scheduler->nextDate($cursor, $grupo);
                    if (!$this->scheduler->isActiveOn($grupo, $cursor)) {
                        break;
                    }
                }
            }

            while ($cursor->lessThanOrEqualTo($end) && $this->scheduler->isActiveOn($grupo, $cursor)) {
                $key = $cursor->toDateString();
                if ($cursor->greaterThanOrEqualTo($start) && !in_array($key, $knownDates, true)) {
                    $extras[] = [
                        'date' => $key,
                        'kind' => $grupo->kind,
                        'amount' => (float) $grupo->amount,
                    ];
                }
                $cursor = $this->scheduler->nextDate($cursor, $grupo);
            }
        }

        return $extras;
    }
}
