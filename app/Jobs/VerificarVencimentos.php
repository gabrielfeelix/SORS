<?php

namespace App\Jobs;

use App\Models\KitamoNotification;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class VerificarVencimentos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = CarbonImmutable::today();

        User::query()
            ->where('notif_vencimento', true)
            ->chunkById(200, function ($users) use ($today) {
                foreach ($users as $user) {
                    $days = max(0, (int) ($user->notif_antecedencia_dias ?? 3));

                    $targets = [
                        $today->addDays($days),
                        $today,
                        $today->subDay(),
                    ];

                    foreach ($targets as $targetDate) {
                        $transactions = Transaction::query()
                            ->where('user_id', $user->id)
                            ->where('kind', 'expense')
                            ->where('status', 'pending')
                            ->whereDate('transaction_date', $targetDate->toDateString())
                            ->orderBy('transaction_date')
                            ->get();

                        foreach ($transactions as $t) {
                            $diasRestantes = $today->diffInDays(CarbonImmutable::parse($t->transaction_date), false);
                            $prioridade = $this->prioridadeVencimento($diasRestantes);

                            $key = "vencimento:{$t->id}:{$targetDate->toDateString()}";
                            $exists = KitamoNotification::query()
                                ->where('user_id', $user->id)
                                ->where('tipo', 'vencimento')
                                ->whereRaw("JSON_EXTRACT(metadata, '$.dedupe_key') = ?", [$key])
                                ->exists();

                            if ($exists) {
                                continue;
                            }

                            KitamoNotification::create([
                                'id' => (string) Str::uuid(),
                                'user_id' => $user->id,
                                'tipo' => 'vencimento',
                                'prioridade' => $prioridade,
                                'titulo' => $diasRestantes >= 0
                                    ? "Conta vence em {$diasRestantes} dia(s)"
                                    : 'Conta em atraso',
                                'mensagem' => "{$t->description} - R$ " . number_format((float) $t->amount, 2, ',', '.'),
                                'lida' => false,
                                'expirada' => false,
                                'data_expiracao' => CarbonImmutable::parse($t->transaction_date)->addDay()->startOfDay(),
                                'acao_primaria_tipo' => 'ver_transacao',
                                'acao_primaria_url' => "kitamo://transaction/{$t->id}",
                                'metadata' => [
                                    'transaction_id' => (int) $t->id,
                                    'dias_restantes' => $diasRestantes,
                                    'dedupe_key' => $key,
                                ],
                            ]);
                        }
                    }
                }
            });
    }

    private function prioridadeVencimento(int $diasRestantes): string
    {
        if ($diasRestantes < 0) return 'urgente';
        if ($diasRestantes === 0) return 'alta';
        if ($diasRestantes <= 2) return 'media';
        return 'baixa';
    }
}

