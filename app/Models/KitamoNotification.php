<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitamoNotification extends Model
{
    use HasFactory;

    protected $table = 'kitamo_notifications';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'tipo',
        'prioridade',
        'titulo',
        'mensagem',
        'lida',
        'data_leitura',
        'expirada',
        'data_expiracao',
        'acao_primaria_tipo',
        'acao_primaria_url',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'lida' => 'boolean',
            'expirada' => 'boolean',
            'data_leitura' => 'datetime',
            'data_expiracao' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

