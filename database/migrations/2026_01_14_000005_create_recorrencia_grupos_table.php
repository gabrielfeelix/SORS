<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recorrencia_grupos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind');
            $table->decimal('amount', 12, 2);
            $table->string('descricao');
            $table->string('periodicidade');
            $table->integer('intervalo_dias')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'account_id']);
            $table->index(['data_inicio', 'data_fim']);
            $table->index(['periodicidade', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recorrencia_grupos');
    }
};
