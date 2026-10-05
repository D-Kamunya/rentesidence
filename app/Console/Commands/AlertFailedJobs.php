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

        User::where('role', USER_ROLE_ADMIN)->pluck('id')->each(function ($adminId) use ($message) {
            // Link to System Health (incidents), where each failure is recorded as a JOB_FAILED
            // incident (AppServiceProvider's Queue::failing hook) — so the admin lands on the actual
            // failed jobs instead of the notification page linking back to itself.
            addNotification(__('Background jobs need attention'), $message, route('admin.incidents.index'), null, $adminId, $adminId);
        });

        Log::channel('sms-mail')->warning("Failed-jobs alert: {$count} job(s) in failed_jobs.");
        $this->info("Alerted admins about {$count} failed job(s).");

        return self::SUCCESS;
    }
}
