<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadsController extends Controller
{
    public function index(): Response
    {
        $leads = NewsletterLead::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (NewsletterLead $lead) => [
                'id' => (int) $lead->id,
                'name' => $lead->name,
                'email' => (string) $lead->email,
                'status' => $lead->unsubscribed_at ? 'unsubscribed' : 'subscribed',
                'subscribed_at' => $lead->subscribed_at?->toISOString() ?? $lead->created_at?->toISOString(),
                'created_at' => $lead->created_at?->toISOString(),
                'source' => $lead->source,
            ]);

        return Inertia::render('Admin/Leads', [
            'leads' => $leads,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'source' => ['nullable', 'string', 'max:64'],
        ]);

        NewsletterLead::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'] ?? null,
                'source' => $data['source'] ?? 'admin',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ],
        );

        return back()->with('success', 'Lead salvo.');
    }

    public function update(Request $request, NewsletterLead $lead): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'status' => ['nullable', 'in:subscribed,unsubscribed'],
        ]);

        $lead->name = $data['name'] ?? null;
        $lead->email = $data['email'];
        if (($data['status'] ?? null) === 'unsubscribed') {
            $lead->unsubscribed_at = now();
        } elseif (($data['status'] ?? null) === 'subscribed') {
            $lead->unsubscribed_at = null;
            $lead->subscribed_at = $lead->subscribed_at ?? now();
        }
        $lead->save();

        return back()->with('success', 'Lead atualizado.');
    }

    public function destroy(NewsletterLead $lead): RedirectResponse
    {
        $lead->delete();
        return back()->with('success', 'Lead excluído.');
    }
}

