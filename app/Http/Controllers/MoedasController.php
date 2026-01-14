<?php

namespace App\Http\Controllers;

use App\Services\MoedaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MoedasController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'moedas' => [
                ['codigo' => 'BRL', 'nome' => 'Real Brasileiro', 'simbolo' => 'R$'],
                ['codigo' => 'USD', 'nome' => 'Dólar Americano', 'simbolo' => '$'],
                ['codigo' => 'EUR', 'nome' => 'Euro', 'simbolo' => '€'],
                ['codigo' => 'GBP', 'nome' => 'Libra Esterlina', 'simbolo' => '£'],
                ['codigo' => 'JPY', 'nome' => 'Iene Japonês', 'simbolo' => '¥'],
            ],
        ]);
    }

    public function converter(Request $request, MoedaService $service): JsonResponse
    {
        $data = $request->validate([
            'valor' => ['required', 'numeric'],
            'moeda_origem' => ['required', 'string', 'size:3'],
            'moeda_destino' => ['required', 'string', 'size:3'],
        ]);

        $valorOriginal = (float) $data['valor'];
        $valorConvertido = $service->converter(
            $valorOriginal,
            $data['moeda_origem'],
            $data['moeda_destino'],
        );

        return response()->json([
            'valor_original' => $valorOriginal,
            'moeda_origem' => strtoupper($data['moeda_origem']),
            'valor_convertido' => round($valorConvertido, 2),
            'moeda_destino' => strtoupper($data['moeda_destino']),
        ]);
    }
}

