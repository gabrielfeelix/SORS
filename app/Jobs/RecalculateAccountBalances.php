<?php

namespace App\Jobs;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateAccountBalances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Account::query()->chunkById(100, function ($accounts) {
            foreach ($accounts as $account) {
                $income = $account->transactions()
                    ->where('kind', 'income')
                    ->whereIn('status', ['received', 'paid'])
                    ->sum('amount');

                $expense = $account->transactions()
                    ->where('kind', 'expense')
                    ->where('status', 'paid')
                    ->sum('amount');

                $balance = (float) $account->initial_balance + (float) $income - (float) $expense;

                $account->forceFill([
                    'current_balance' => $balance,
                ])->save();
            }
        });
    }
}
