<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitamo_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->string('prioridade', 20)->default('media');
            $table->string('titulo');
            $table->text('mensagem');
            $table->boolean('lida')->default(false);
            $table->timestamp('data_leitura')->nullable();
            $table->boolean('expirada')->default(false);
            $table->timestamp('data_expiracao')->nullable();
            $table->string('acao_primaria_tipo', 50)->nullable();
            $table->string('acao_primaria_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lida']);
            $table->index(['user_id', 'prioridade']);
            $table->index('expirada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitamo_notifications');
    }
};

