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
        $schedule->command("backup:clean")->dailyAt("01:00");
        // $schedule->command('backup:run --only-to-disk=s3-backup')->dailyAt('02:00');
        $schedule
            ->command("backup:run --only-to-disk=s3-backups-db --only-db")
            ->dailyAt("02:00");
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . "/Commands");

        require base_path("routes/console.php");
    }
}
