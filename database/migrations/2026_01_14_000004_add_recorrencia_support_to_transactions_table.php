<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('recorrencia_grupo_id')->nullable()->after('recurrence_end_at');
            $table->timestamp('data_pagamento')->nullable()->after('recorrencia_grupo_id');

            $table->index('recorrencia_grupo_id');
            $table->index('data_pagamento');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['recorrencia_grupo_id']);
            $table->dropIndex(['data_pagamento']);
            $table->dropColumn(['recorrencia_grupo_id', 'data_pagamento']);
        });
    }
};

