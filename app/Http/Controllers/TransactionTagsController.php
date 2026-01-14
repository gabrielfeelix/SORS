<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionTagsController extends Controller
{
    public function sync(Request $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();
        if ($transaction->user_id !== $user->id) {
            abort(404);
        }

        $data = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['string'],
        ]);

        $validTagIds = Tag::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $data['tag_ids'])
            ->pluck('id')
            ->all();

        $transaction->tagsRelation()->sync($validTagIds);

        return response()->json([
            'message' => 'Tags atualizadas',
            'tags' => $transaction->tagsRelation()->get(),
        ]);
    }
}

