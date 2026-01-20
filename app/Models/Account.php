<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'moeda',
        'institution',
        'bank_account_type',
        'icon',
        'color',
        'card_brand',
        'initial_balance',
        'current_balance',
        'credit_limit',
        'closing_day',
        'due_day',
        'is_archived',
        'incluir_soma',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'is_archived' => 'boolean',
            'incluir_soma' => 'boolean',
            'is_primary' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeCashLike(Builder $query): Builder
    {
        return $query->where('type', '!=', 'credit_card');
    }

    public function scopeIncludedInNetWorth(Builder $query): Builder
    {
        return $query
            ->cashLike()
            ->where('is_archived', false)
            ->where(function (Builder $q) {
                $q->whereNull('incluir_soma')->orWhere('incluir_soma', true);
            });
    }
}
