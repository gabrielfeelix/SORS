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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind');
            $table->string('status');
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->date('transaction_date');
            $table->boolean('priority')->default(false);
            $table->string('installment_label')->nullable();
            $table->unsignedTinyInteger('installment_index')->nullable();
            $table->unsignedTinyInteger('installment_total')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_interval')->nullable();
            $table->date('next_run_at')->nullable();
            $table->date('recurrence_end_at')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['transaction_date', 'kind']);
            $table->index(['user_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
