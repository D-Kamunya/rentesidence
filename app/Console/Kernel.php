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

        // Comms safety net: surface persistent job failures (a send that didn't happen) to admins
        // daily, and prune old failed rows weekly so the table stays clean.
        $schedule->command('queue:alert-failed')->dailyAt('07:00')->withoutOverlapping();
        $schedule->command('queue:prune-failed --hours=168')->weekly();

        // Affiliate commission digest — 1st of each month, for the previous month.
        $schedule->command('affiliate:commission-digest')->monthlyOn(1, '08:00')->withoutOverlapping();
        $schedule->command('leads:generate-suggestions')->everyFourHours()
        ->appendOutputTo(storage_path('logs/generate_suggestions_scheduler.log'));
        $schedule->command('leads:generate-suggestions --notify')->dailyAt('09:00')
        ->appendOutputTo(storage_path('logs/generate_suggestions_emails_scheduler.log'));
        $schedule->command('leads:expire')->dailyAt('02:00')->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/leads_expire_scheduler.log'));
        $schedule->command('trials:expire')->dailyAt('02:30')->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/trials_expire_scheduler.log'));

        // Global Tenant ID backbone — refresh tenant payment-behaviour profiles nightly.
        $schedule->command('screening:recompute')->dailyAt('03:00')->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/screening_recompute_scheduler.log'));

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
