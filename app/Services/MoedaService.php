<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MoedaService
{
    private string $apiUrl = 'https://api.exchangerate-api.com/v4/latest/';

    public function atualizarTaxas(string $moedaBase = 'BRL'): bool
    {
        $response = Http::timeout(10)->get($this->apiUrl . $moedaBase);
        if (!$response->successful()) {
            return false;
        }

        $data = $response->json();
        $rates = $data['rates'] ?? null;
        if (!is_array($rates)) {
            return false;
        }

        foreach ($rates as $moeda => $taxa) {
            ExchangeRate::updateOrCreate(
                ['moeda_base' => $moedaBase, 'moeda_destino' => (string) $moeda],
                [
                    'id' => (string) Str::uuid(),
                    'taxa' => $taxa,
                    'data_atualizacao' => now(),
                ]
            );
        }

        return true;
    }

    public function converter(float $valor, string $moedaOrigem, string $moedaDestino): float
    {
        $moedaOrigem = strtoupper($moedaOrigem);
        $moedaDestino = strtoupper($moedaDestino);

        if ($moedaOrigem === $moedaDestino) {
            return $valor;
        }

        // Usa taxa moedaOrigem -> moedaDestino. Se não existir, tenta atualizar com base=moedaOrigem.
        $rate = ExchangeRate::query()
            ->where('moeda_base', $moedaOrigem)
            ->where('moeda_destino', $moedaDestino)
            ->first();

        if (!$rate) {
            $this->atualizarTaxas($moedaOrigem);
            $rate = ExchangeRate::query()
                ->where('moeda_base', $moedaOrigem)
                ->where('moeda_destino', $moedaDestino)
                ->first();
        }

        if (!$rate) {
            return $valor;
        }

        return $valor * (float) $rate->taxa;
    }

    public function converterParaBRL(float $valor, string $moeda): float
    {
        $moeda = strtoupper($moeda);
        if ($moeda === 'BRL') {
            return $valor;
        }

        $rate = ExchangeRate::query()
            ->where('moeda_base', 'BRL')
            ->where('moeda_destino', $moeda)
            ->first();

        if (!$rate) {
            return $valor;
        }

        $taxa = (float) $rate->taxa;
        if ($taxa <= 0) {
            return $valor;
        }

        return $valor / $taxa;
    }
}

