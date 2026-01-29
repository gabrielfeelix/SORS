<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmailCampaign $campaign)
    {
    }

    public function build(): self
    {
        $subject = $this->campaign->subject ?: $this->campaign->title;

        return $this
            ->subject($subject)
            ->view('emails.admin-campaign', [
                'campaign' => $this->campaign,
            ]);
    }
}

