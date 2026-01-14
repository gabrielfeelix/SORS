<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelamento_grupos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('descricao');
            $table->decimal('valor_total', 12, 2);
            $table->unsignedSmallInteger('quantidade_parcelas');
            $table->date('data_primeira_parcela');
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'account_id']);
            $table->index('data_primeira_parcela');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelamento_grupos');
    }
};

