<?php

namespace App\Jobs;

use App\Models\KitamoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpirarNotificacoes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        KitamoNotification::query()
            ->where('expirada', false)
            ->whereNotNull('data_expiracao')
            ->where('data_expiracao', '<', now())
            ->update(['expirada' => true]);
    }
}

