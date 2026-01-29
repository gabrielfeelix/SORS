<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterLead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'source',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];
}

