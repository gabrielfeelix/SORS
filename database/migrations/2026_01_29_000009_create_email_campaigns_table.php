<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->string('type', 24); // announcement | newsletter
            $table->string('title', 160);
            $table->string('subject', 200)->nullable();
            $table->longText('content')->nullable();
            $table->string('audience', 32)->default('all_users'); // all_users | admins | role:<key>
            $table->string('status', 24)->default('draft'); // draft | scheduled | sent | failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->longText('last_error')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'created_at']);
            $table->index(['scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};

