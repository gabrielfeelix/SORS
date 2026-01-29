<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->nullable();
            $table->string('email', 190);
            $table->string('source', 64)->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique('email');
            $table->index(['subscribed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_leads');
    }
};

