<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transferencia extends Model
{
    use HasFactory;

    protected $table = 'transferencias';

    protected $fillable = [
        'user_id',
        'conta_origem_id',
        'conta_destino_id',
        'valor',
        'descricao',
        'transferido_em',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'transferido_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contaOrigem(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'conta_origem_id');
    }

    public function contaDestino(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'conta_destino_id');
    }
}

