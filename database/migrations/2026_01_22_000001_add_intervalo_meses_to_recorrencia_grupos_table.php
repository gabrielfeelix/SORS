<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recorrencia_grupos', function (Blueprint $table) {
            $table->integer('intervalo_meses')->nullable()->after('intervalo_dias');
            $table->index(['periodicidade', 'intervalo_meses']);
        });
    }

    public function down(): void
    {
        Schema::table('recorrencia_grupos', function (Blueprint $table) {
            $table->dropIndex(['periodicidade', 'intervalo_meses']);
            $table->dropColumn('intervalo_meses');
        });
    }
};

