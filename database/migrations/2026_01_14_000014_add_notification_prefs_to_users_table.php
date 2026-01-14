<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notif_vencimento')->default(true)->after('last_backup_at');
            $table->boolean('notif_alerta_saldo')->default(true)->after('notif_vencimento');
            $table->boolean('notif_resumo_semanal')->default(true)->after('notif_alerta_saldo');
            $table->unsignedTinyInteger('notif_antecedencia_dias')->default(3)->after('notif_resumo_semanal');

            $table->boolean('notif_meta_atingida')->default(true)->after('notif_antecedencia_dias');
            $table->boolean('notif_gasto_anomalo')->default(true)->after('notif_meta_atingida');
            $table->boolean('notif_limite_categoria')->default(true)->after('notif_gasto_anomalo');
            $table->boolean('notif_conquistas')->default(true)->after('notif_limite_categoria');
            $table->boolean('notif_fatura_fechada')->default(true)->after('notif_conquistas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notif_vencimento',
                'notif_alerta_saldo',
                'notif_resumo_semanal',
                'notif_antecedencia_dias',
                'notif_meta_atingida',
                'notif_gasto_anomalo',
                'notif_limite_categoria',
                'notif_conquistas',
                'notif_fatura_fechada',
            ]);
        });
    }
};

