<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_parcelado')->default(false)->after('is_recurring');
            $table->unsignedSmallInteger('parcela_atual')->nullable()->after('installment_total');
            $table->unsignedSmallInteger('parcela_total')->nullable()->after('parcela_atual');
            $table->uuid('parcelamento_grupo_id')->nullable()->after('recorrencia_grupo_id');

            $table->index('parcelamento_grupo_id');
            $table->index(['parcelamento_grupo_id', 'parcela_atual']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['parcelamento_grupo_id']);
            $table->dropIndex(['parcelamento_grupo_id', 'parcela_atual']);
            $table->dropColumn(['is_parcelado', 'parcela_atual', 'parcela_total', 'parcelamento_grupo_id']);
        });
    }
};

