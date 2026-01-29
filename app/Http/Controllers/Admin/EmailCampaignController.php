<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminCampaignMail;
use App\Models\EmailCampaign;
use App\Models\NewsletterLead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class EmailCampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = EmailCampaign::query()
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn (EmailCampaign $c) => [
                'id' => (int) $c->id,
                'type' => (string) $c->type,
                'title' => (string) $c->title,
                'subject' => $c->subject,
                'content' => $c->content,
                'audience' => (string) $c->audience,
                'status' => (string) $c->status,
                'scheduled_at' => $c->scheduled_at?->toISOString(),
                'sent_at' => $c->sent_at?->toISOString(),
                'sent_count' => (int) $c->sent_count,
                'last_error' => $c->last_error,
                'created_at' => $c->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/Emails', [
            'campaigns' => $campaigns,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:announcement,newsletter'],
            'title' => ['required', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
            'audience' => ['nullable', 'string', 'max:32'],
            'status' => ['nullable', 'in:draft,scheduled'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $campaign = new EmailCampaign();
        $campaign->created_by_user_id = $request->user()?->id;
        $campaign->type = $data['type'];
        $campaign->title = $data['title'];
        $campaign->subject = $data['subject'] ?? null;
        $campaign->content = $data['content'] ?? null;
        $campaign->audience = $data['audience'] ?? ($data['type'] === 'newsletter' ? 'newsletter_leads' : 'all_users');
        $campaign->status = $data['status'] ?? 'draft';
        $campaign->scheduled_at = isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;
        $campaign->save();

        return back()->with('success', 'Campanha criada.');
    }

    public function update(Request $request, EmailCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
            'audience' => ['nullable', 'string', 'max:32'],
            'status' => ['nullable', 'in:draft,scheduled'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        if (array_key_exists('title', $data)) $campaign->title = $data['title'];
        $campaign->subject = $data['subject'] ?? null;
        $campaign->content = $data['content'] ?? null;
        if (array_key_exists('audience', $data) && $data['audience']) {
            $campaign->audience = $data['audience'];
        }
        if (array_key_exists('status', $data) && $data['status']) {
            $campaign->status = $data['status'];
        }
        $campaign->scheduled_at = isset($data['scheduled_at']) && $data['scheduled_at']
            ? Carbon::parse($data['scheduled_at'])
            : null;
        $campaign->save();

        return back()->with('success', 'Campanha atualizada.');
    }

    public function destroy(EmailCampaign $campaign): RedirectResponse
    {
        $campaign->delete();
        return back()->with('success', 'Campanha excluída.');
    }

    public function sendNow(Request $request, EmailCampaign $campaign): RedirectResponse
    {
        if (! in_array($campaign->status, ['draft', 'scheduled'], true)) {
            return back()->withErrors(['default' => 'Esta campanha não pode ser enviada.']);
        }

        $recipients = [];
        if ($campaign->type === 'newsletter') {
            $recipients = NewsletterLead::query()
                ->whereNull('unsubscribed_at')
                ->pluck('email')
                ->filter()
                ->values()
                ->all();
        } else {
            $recipients = User::query()
                ->whereNull('disabled_at')
                ->pluck('email')
                ->filter()
                ->values()
                ->all();
        }

        $campaign->last_error = null;
        $campaign->sent_count = 0;
        $campaign->save();

        $sent = 0;
        try {
            foreach (array_chunk($recipients, 50) as $chunk) {
                if (count($chunk) === 0) continue;
                $to = config('mail.from.address') ?: $chunk[0];
                Mail::to($to)->bcc($chunk)->send(new AdminCampaignMail($campaign));
                $sent += count($chunk);
            }
            $campaign->status = 'sent';
            $campaign->sent_at = now();
            $campaign->sent_count = $sent;
            $campaign->save();
        } catch (\Throwable $e) {
            $campaign->status = 'failed';
            $campaign->last_error = (string) $e;
            $campaign->sent_count = $sent;
            $campaign->save();
            return back()->withErrors(['default' => 'Falha ao enviar a campanha. Verifique as configurações de e-mail.']);
        }

        return back()->with('success', 'Campanha enviada.');
    }
}
