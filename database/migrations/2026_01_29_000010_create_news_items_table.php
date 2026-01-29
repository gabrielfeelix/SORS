<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->string('title', 160);
            $table->string('category', 80)->nullable(); // ex: Biblioteca, Perfil, Admin...
            $table->string('type', 24)->default('new'); // new | improvement | fix | announcement
            $table->string('visibility', 24)->default('public'); // public | admin
            $table->string('status', 24)->default('draft'); // draft | scheduled | published
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->longText('content')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('cta_text', 80)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['visibility', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_items');
    }
};

