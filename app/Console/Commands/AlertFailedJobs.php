<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Surfaces persistent background-job failures so a broken email/SMS/notification send never rots
 * unseen in the failed_jobs table. Runs daily: if anything is in failed_jobs, it drops an in-app
 * notification to every admin (a digest, not per-failure spam) and logs a warning. Housekeeping
 * (pruning old rows) is handled separately by the scheduled queue:prune-failed.
 */
class AlertFailedJobs extends Command
{
    protected $signature = 'queue:alert-failed';
    protected $description = 'Notify admins if there are failed background jobs needing attention.';

    public function handle(): int
    {
        if (! DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            return self::SUCCESS;
        }

        $count = DB::table('failed_jobs')->count();
        if ($count === 0) {
            return self::SUCCESS;
        }

        $message = trans_choice(
            '{1}:count background job has failed and needs attention (e.g. an email or SMS may not have sent).|[2,*]:count background jobs have failed and need attention (e.g. emails or SMS may not have sent).',
            $count,
            ['count' => $count]
        );

        // Raise a matching System Health incident for the backlog so the bell notification and the
        // System Health page RECONCILE. (The per-failure Queue::failing incidents can be resolved by
        // an admin while the failed_jobs rows linger, which previously left this daily notification
        // pointing to an empty page.) Dedup'd to one growing row; clearing the backlog — `php
        // artisan queue:flush` — is the actual fix, after which the next run raises nothing.
        try {
            app(\App\Services\SystemIncidentService::class)->report(
                \App\Models\SystemIncident::TYPE_JOB_FAILED,
                \App\Models\SystemIncident::SEVERITY_WARNING,
                'Jobs in the failed queue',
                $count . ' background job(s) are sitting in the failed queue. Investigate, then clear them (queue:flush) once handled.',
                ['failed_jobs' => $count],
                'failed_jobs_backlog',
            );
        } catch (\Throwable $e) {
            // never let the incident write break the alert
        }

        User::where('role', USER_ROLE_ADMIN)->pluck('id')->each(function ($adminId) use ($message) {
            // Link to System Health, where the backlog now shows as a "Jobs in the failed queue"
            // incident (above) — so the admin lands on an actual, matching entry.
            addNotification(__('Background jobs need attention'), $message, route('admin.incidents.index'), null, $adminId, $adminId);
        });

        Log::channel('sms-mail')->warning("Failed-jobs alert: {$count} job(s) in failed_jobs.");
        $this->info("Alerted admins about {$count} failed job(s).");

        return self::SUCCESS;
    }
}
