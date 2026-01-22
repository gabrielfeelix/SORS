<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix common category icons (keeps user custom icons unless they are null/clearly wrong).
        // This is important because some users may end up with duplicated category names (income/expense)
        // and missing/wrong icons.

        // Alimentação should never be heart.
        DB::table('categories')
            ->where('name', 'Alimentação')
            ->where(function ($q) {
                $q->whereNull('icon')->orWhereIn('icon', ['food', 'heart']);
            })
            ->update(['icon' => 'cart']);

        DB::table('categories')->where('name', 'Transporte')->whereNull('icon')->update(['icon' => 'car']);
        DB::table('categories')->where('name', 'Moradia')->whereNull('icon')->update(['icon' => 'home']);
        DB::table('categories')->where('name', 'Lazer')->whereNull('icon')->update(['icon' => 'game']);
        DB::table('categories')->where('name', 'Saúde')->whereNull('icon')->update(['icon' => 'heart']);
        DB::table('categories')->where('name', 'Salário')->whereNull('icon')->update(['icon' => 'money']);

        // Outros: prefer "bolt" for despesas and "briefcase" for receitas when missing.
        DB::table('categories')
            ->where('name', 'Outros')
            ->where('type', 'expense')
            ->whereNull('icon')
            ->update(['icon' => 'bolt']);
        DB::table('categories')
            ->where('name', 'Outros')
            ->where('type', 'income')
            ->whereNull('icon')
            ->update(['icon' => 'briefcase']);
    }

    public function down(): void
    {
        // No-op: we can't safely restore previous user-selected icons.
    }
};

