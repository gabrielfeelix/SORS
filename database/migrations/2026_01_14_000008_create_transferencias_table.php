<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conta_origem_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('conta_destino_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('valor', 12, 2);
            $table->string('descricao')->nullable();
            $table->timestamp('transferido_em');
            $table->timestamps();

            $table->index(['user_id', 'transferido_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};

