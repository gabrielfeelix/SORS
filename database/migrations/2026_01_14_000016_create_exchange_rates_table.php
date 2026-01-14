<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('moeda_base', 3);
            $table->string('moeda_destino', 3);
            $table->decimal('taxa', 10, 6);
            $table->timestamp('data_atualizacao')->nullable();
            $table->timestamps();

            $table->unique(['moeda_base', 'moeda_destino']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};

