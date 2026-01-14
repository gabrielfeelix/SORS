<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'notif_vencimento' => ['required', 'boolean'],
            'notif_alerta_saldo' => ['required', 'boolean'],
            'notif_resumo_semanal' => ['required', 'boolean'],
            'notif_antecedencia_dias' => ['required', 'integer', 'min:0', 'max:30'],
            'notif_meta_atingida' => ['nullable', 'boolean'],
            'notif_gasto_anomalo' => ['nullable', 'boolean'],
            'notif_limite_categoria' => ['nullable', 'boolean'],
            'notif_conquistas' => ['nullable', 'boolean'],
            'notif_fatura_fechada' => ['nullable', 'boolean'],
        ]);

        $user->fill($data);
        $user->save();

        return response()->json(['message' => 'Preferências atualizadas']);
    }
}

