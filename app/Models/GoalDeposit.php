<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'title',
        'subtitle',
        'amount',
        'deposited_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'deposited_at' => 'datetime',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
