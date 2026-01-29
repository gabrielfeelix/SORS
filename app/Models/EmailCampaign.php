<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = [
        'created_by_user_id',
        'type',
        'title',
        'subject',
        'content',
        'audience',
        'status',
        'scheduled_at',
        'sent_at',
        'sent_count',
        'last_error',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'sent_count' => 'integer',
    ];
}

