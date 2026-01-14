<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParcelamentoGrupo extends Model
{
    use HasFactory;

    protected $table = 'parcelamento_grupos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'account_id',
        'category_id',
        'descricao',
        'valor_total',
        'quantidade_parcelas',
        'data_primeira_parcela',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'valor_total' => 'decimal:2',
            'quantidade_parcelas' => 'integer',
            'data_primeira_parcela' => 'date',
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
        return $this->hasMany(Transaction::class, 'parcelamento_grupo_id');
    }
}

