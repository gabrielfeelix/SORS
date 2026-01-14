<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id');
            $table->uuid('tag_id');

            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();

            $table->primary(['transaction_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_tags');
    }
};

