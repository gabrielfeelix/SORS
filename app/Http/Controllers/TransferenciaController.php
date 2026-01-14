<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transferencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferenciaController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'conta_origem_id' => ['required', 'integer'],
            'conta_destino_id' => ['required', 'integer', 'different:conta_origem_id'],
            'valor' => ['required', 'numeric', 'min:0.01'],
        ]);

        $contaOrigem = Account::where('user_id', $user->id)->findOrFail($data['conta_origem_id']);
        $contaDestino = Account::where('user_id', $user->id)->findOrFail($data['conta_destino_id']);
        $valor = (float) $data['valor'];

        $saldoOrigemAtual = (float) $contaOrigem->current_balance;
        $saldoDestinoAtual = (float) $contaDestino->current_balance;

        $saldoOrigemProjetado = $saldoOrigemAtual - $valor;
        $saldoDestinoProjetado = $saldoDestinoAtual + $valor;

        return response()->json([
            'conta_origem' => [
                'id' => (string) $contaOrigem->id,
                'nome' => $contaOrigem->name,
                'saldo_atual' => round($saldoOrigemAtual, 2),
                'saldo_projetado' => round($saldoOrigemProjetado, 2),
                'ficara_negativo' => $saldoOrigemProjetado < 0,
            ],
            'conta_destino' => [
                'id' => (string) $contaDestino->id,
                'nome' => $contaDestino->name,
                'saldo_atual' => round($saldoDestinoAtual, 2),
                'saldo_projetado' => round($saldoDestinoProjetado, 2),
            ],
        ]);
    }

    public function executar(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'conta_origem_id' => ['required', 'integer'],
            'conta_destino_id' => ['required', 'integer', 'different:conta_origem_id'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ]);

        $valor = (float) $data['valor'];

        $transferencia = DB::transaction(function () use ($user, $data, $valor) {
            $contaOrigem = Account::where('user_id', $user->id)->lockForUpdate()->findOrFail($data['conta_origem_id']);
            $contaDestino = Account::where('user_id', $user->id)->lockForUpdate()->findOrFail($data['conta_destino_id']);

            $contaOrigem->current_balance = (float) $contaOrigem->current_balance - $valor;
            $contaOrigem->save();

            $contaDestino->current_balance = (float) $contaDestino->current_balance + $valor;
            $contaDestino->save();

            return Transferencia::create([
                'user_id' => $user->id,
                'conta_origem_id' => $contaOrigem->id,
                'conta_destino_id' => $contaDestino->id,
                'valor' => $valor,
                'descricao' => $data['descricao'] ?? 'Transferência',
                'transferido_em' => now(),
            ]);
        });

        return response()->json([
            'ok' => true,
            'transferencia_id' => (string) $transferencia->id,
        ]);
    }
}

