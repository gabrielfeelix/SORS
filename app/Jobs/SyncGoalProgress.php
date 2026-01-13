<?php

namespace App\Jobs;

use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncGoalProgress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = Carbon::today();

        Goal::query()->with('deposits')->chunkById(100, function ($goals) use ($today) {
            foreach ($goals as $goal) {
                $current = $goal->deposits->sum('amount');

                $status = 'on_track';
                if ($goal->target_amount > 0 && $current >= $goal->target_amount) {
                    $status = 'ahead';
                } elseif ($goal->due_date && Carbon::parse($goal->due_date)->lt($today)) {
                    $status = 'late';
                }

                $goal->forceFill([
                    'current_amount' => $current,
                    'status' => $status,
                ])->save();
            }
        });
    }
}
