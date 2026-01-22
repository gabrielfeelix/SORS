<?php

namespace App\Services\Recorrencias;

use App\Models\RecorrenciaGrupo;
use Carbon\CarbonImmutable;

class RecorrenciaScheduler
{
    public function nextDate(CarbonImmutable $from, RecorrenciaGrupo $grupo): CarbonImmutable
    {
        return match ($grupo->periodicidade) {
            'mensal' => $from->addMonthNoOverflow(),
            'quinzenal' => $from->addDays(15),
            'a_cada_x_meses' => $from->addMonthsNoOverflow(max(1, (int) ($grupo->intervalo_meses ?? 1))),
            'a_cada_x_dias' => $from->addDays(max(1, (int) ($grupo->intervalo_dias ?? 1))),
            default => $from->addMonthNoOverflow(),
        };
    }

    public function isActiveOn(RecorrenciaGrupo $grupo, CarbonImmutable $date): bool
    {
        if (!$grupo->is_active) {
            return false;
        }

        if ($grupo->data_fim && $date->greaterThan(CarbonImmutable::parse($grupo->data_fim))) {
            return false;
        }

        return true;
    }
}
