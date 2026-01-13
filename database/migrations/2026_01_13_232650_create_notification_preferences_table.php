<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 64); // 'balance', 'credit_card', 'goals', 'recurring', 'budget', 'summary'
            $table->json('channels')->nullable(); // ['database', 'mail', 'sms']
            $table->boolean('enabled')->default(true);
            $table->decimal('threshold', 12, 2)->nullable(); // For threshold-based notifications
            $table->json('config')->nullable(); // Additional category-specific settings
            $table->timestamps();

            $table->unique(['user_id', 'category']);
            $table->index(['user_id', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
