<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $table = 'exchange_rates';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'moeda_base',
        'moeda_destino',
        'taxa',
        'data_atualizacao',
    ];

    protected function casts(): array
    {
        return [
            'taxa' => 'decimal:8',
            'data_atualizacao' => 'datetime',
        ];
    }
}
