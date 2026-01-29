<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user && mb_strtolower((string) $user->email) === 'contato@kitamo.com.br';

        $query = NewsItem::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at');

        if (! $isAdmin) {
            $query->where('visibility', 'public');
        }

        $items = $query
            ->limit(100)
            ->get()
            ->map(fn (NewsItem $n) => [
                'id' => (int) $n->id,
                'title' => (string) $n->title,
                'category' => $n->category,
                'type' => (string) $n->type,
                'published_at' => $n->published_at?->toISOString(),
                'content' => $n->content,
                'image_url' => $n->image_url,
                'cta_text' => $n->cta_text,
                'cta_url' => $n->cta_url,
            ]);

        return response()->json([
            'items' => $items,
        ]);
    }
}

