<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecorrenciaGrupo extends Model
{
    use HasFactory;

    protected $table = 'recorrencia_grupos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'account_id',
        'category_id',
        'kind',
        'amount',
        'descricao',
        'periodicidade',
        'intervalo_dias',
        'data_inicio',
        'data_fim',
        'is_active',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'intervalo_dias' => 'integer',
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'is_active' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'recorrencia_grupo_id');
    }
}

