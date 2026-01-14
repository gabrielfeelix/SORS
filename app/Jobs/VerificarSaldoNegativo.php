<?php

namespace App\Jobs;

use App\Models\KitamoNotification;
use App\Models\User;
use App\Services\ProjecaoService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class VerificarSaldoNegativo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ProjecaoService $projecaoService): void
    {
        $today = CarbonImmutable::today();

        User::query()
            ->where('notif_alerta_saldo', true)
            ->chunkById(200, function ($users) use ($projecaoService, $today) {
                foreach ($users as $user) {
                    $projecao = $projecaoService->calcularProjecao30Dias($user->id);
                    $primeiro = $projecao['primeiro_dia_negativo'] ?? null;
                    if (!$primeiro) {
                        continue;
                    }

                    [$day, $month] = explode('/', $primeiro);
                    $year = $today->year;
                    $date = CarbonImmutable::createFromDate($year, (int) $month, (int) $day);
                    if ($date->lessThan($today)) {
                        $date = $date->addYear();
                    }

                    $dias = $today->diffInDays($date, false);
                    if ($dias > 7) {
                        continue;
                    }

                    $recent = KitamoNotification::query()
                        ->where('user_id', $user->id)
                        ->where('tipo', 'alerta_saldo')
                        ->where('created_at', '>', now()->subHours(24))
                        ->exists();

                    if ($recent) {
                        continue;
                    }

                    KitamoNotification::create([
                        'id' => (string) Str::uuid(),
                        'user_id' => $user->id,
                        'tipo' => 'alerta_saldo',
                        'prioridade' => 'alta',
                        'titulo' => 'Atenção: saldo ficará negativo',
                        'mensagem' => "No ritmo atual, seu saldo ficará negativo dia {$primeiro}. Revise seus gastos!",
                        'lida' => false,
                        'expirada' => false,
                        'data_expiracao' => $date->addDay()->startOfDay(),
                        'acao_primaria_tipo' => 'ver_analise',
                        'acao_primaria_url' => 'kitamo://analytics',
                        'metadata' => [
                            'saldo_dia_30' => $projecao['saldo_dia_30'] ?? null,
                            'primeiro_dia_negativo' => $primeiro,
                        ],
                    ]);
                }
            });
    }
}

