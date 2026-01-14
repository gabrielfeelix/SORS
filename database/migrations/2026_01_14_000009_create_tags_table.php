<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nome', 50);
            $table->string('cor', 7);
            $table->timestamps();

            $table->unique(['user_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};

