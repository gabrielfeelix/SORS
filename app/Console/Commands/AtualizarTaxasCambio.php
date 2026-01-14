<?php

namespace App\Console\Commands;

use App\Services\MoedaService;
use Illuminate\Console\Command;

class AtualizarTaxasCambio extends Command
{
    protected $signature = 'cambio:atualizar {--base=BRL}';

    protected $description = 'Atualiza taxas de câmbio via API externa';

    public function handle(MoedaService $service): int
    {
        $base = strtoupper((string) $this->option('base'));
        $this->info("Atualizando taxas ({$base})...");

        if ($service->atualizarTaxas($base)) {
            $this->info('Taxas atualizadas com sucesso!');
            return self::SUCCESS;
        }

        $this->error('Erro ao atualizar taxas.');
        return self::FAILURE;
    }
}

