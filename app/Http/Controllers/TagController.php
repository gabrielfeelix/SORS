<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:50'],
            'cor' => ['required', 'regex:/^#[0-9A-F]{6}$/i'],
        ]);

        $tag = Tag::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'nome' => $data['nome'],
            'cor' => strtoupper($data['cor']),
        ]);

        return response()->json($tag, 201);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $user = $request->user();

        if ($tag->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:50'],
            'cor' => ['required', 'regex:/^#[0-9A-F]{6}$/i'],
        ]);

        $tag->update([
            'nome' => $data['nome'],
            'cor' => strtoupper($data['cor']),
        ]);

        return response()->json($tag);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $user = request()->user();

        if ($tag->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }
}

