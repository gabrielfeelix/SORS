<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\KitamoBootstrap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['string'],
        ]);

        $query = Transaction::query()
            ->with(['category', 'account', 'recorrenciaGrupo'])
            ->where('user_id', $user->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if (!empty($data['tag_ids'])) {
            $query->whereHas('tagsRelation', function ($q) use ($data) {
                $q->whereIn('tags.id', $data['tag_ids']);
            });
        }

        $entries = $query->get()->map(fn (Transaction $t) => app(KitamoBootstrap::class)->entry($t))->values();

        return response()->json([
            'entries' => $entries,
        ]);
    }
}
