<?php

namespace App\Console;

use App\Services\CompareChallengeReadmes;
use App\Services\ExpiredPlanService;
use App\Services\SyncIsProWithPlans;
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
        $schedule
            ->command("backup:run --only-to-disk=s3-backups-db --only-db")
            ->dailyAt("02:00");

        $schedule
            ->call(function () {
                ExpiredPlanService::handle();
            })
            ->dailyAt("03:00");

        $schedule
            ->call(function () {
                SyncIsProWithPlans::handle();
            })
            ->dailyAt("03:30");

        $schedule
            ->call(function () {
                (new \App\Services\VimeoThumbnailService())->CheckAllVideoThumbnails();
            })
            ->dailyAt("04:00");

        $schedule
            ->call(function () {
                (new CompareChallengeReadmes())->checkAll();
            })
            ->weeklyOn(1, "04:30");

        $schedule
            ->call(function () {
                (new \App\Services\SaveAvatarsFromGithub())->handle();
            })
            ->dailyAt("04:45");
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
