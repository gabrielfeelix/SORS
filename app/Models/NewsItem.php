<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $fillable = [
        'created_by_user_id',
        'title',
        'category',
        'type',
        'visibility',
        'status',
        'scheduled_at',
        'published_at',
        'content',
        'image_url',
        'cta_text',
        'cta_url',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];
}

