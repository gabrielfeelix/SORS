<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('moeda', 3)->default('BRL')->after('type');
            $table->index('moeda');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('moeda', 3)->default('BRL')->after('amount');
            $table->index('moeda');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['moeda']);
            $table->dropColumn('moeda');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['moeda']);
            $table->dropColumn('moeda');
        });
    }
};

