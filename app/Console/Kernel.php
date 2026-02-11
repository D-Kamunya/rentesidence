<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reminder:invoice')->dailyAt('06:00')
        ->appendOutputTo(storage_path('logs/invoice_reminder_scheduler.log'));
        $schedule->command('reminder:subscription')->dailyAt('06:00')
        ->appendOutputTo(storage_path('logs/reminder_scheduler.log'));
        $schedule->command('generate:invoice')->dailyAt('06:00')
        ->appendOutputTo(storage_path('logs/generate_invoice_scheduler.log'));
        $schedule->command('queue:work --stop-when-empty --timeout=120 --tries=3 --memory=512')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/queue_worker.log'));
        $schedule->command('backup:database')
            ->dailyAt('00:00')
            ->appendOutputTo(storage_path('logs/db_backup.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
