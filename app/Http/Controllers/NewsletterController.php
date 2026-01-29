<?php

namespace App\Http\Controllers;

use App\Models\NewsletterLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'source' => ['nullable', 'string', 'max:64'],
        ]);

        $lead = NewsletterLead::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'] ?? null,
                'source' => $data['source'] ?? 'newsletter',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ],
        );

        return response()->json([
            'ok' => true,
            'id' => (int) $lead->id,
        ]);
    }
}

