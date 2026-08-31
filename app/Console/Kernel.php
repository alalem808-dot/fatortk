<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // نسخ احتياطي كامل يومياً الساعة 2 صباحاً
        $schedule->command('backup:run --type=full --prune=30')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // نسخ احتياطي لكل المشتركين أسبوعياً الأحد الساعة 3 صباحاً
        $schedule->command('backup:run --type=tenants --prune=60')
            ->weeklyOn(0, '03:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
