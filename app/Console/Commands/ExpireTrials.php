<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Owner;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\Mail\SendTrialExpiredMail;

class ExpireTrials extends Command
{
    protected $signature   = 'trials:expire {--dry-run : Preview what would expire without making changes}';
    protected $description = 'Expire trial packages and revert leads to pending_conversion status';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        // Only leads in trial status with an owner attached
        $trialLeads = Lead::where('status', 'trial')
            ->whereNotNull('owner_id')
            ->with(['company', 'affiliate', 'owner'])
            ->get();

        $this->info("Trial leads found: {$trialLeads->count()}");

        $expiredCount = 0;
        $skippedCount = 0;

        foreach ($trialLeads as $lead) {
            $owner = $lead->owner;

            // No owner record — data integrity issue, skip and flag it
            if (! $owner) {
                $this->warn("Lead #{$lead->id} has owner_id set but no matching owner record — skipping.");
                $skippedCount++;
                continue;
            }

            // Find the trial package via owner → user_id (correct column)
            $trialPackage = DB::table('owner_packages')
                ->join('packages', 'owner_packages.package_id', '=', 'packages.id')
                ->where('owner_packages.user_id', $owner->user_id)  // fixed: was owner_id
                ->where('packages.is_trail', ACTIVE)
                ->orderByDesc('owner_packages.end_date')
                ->select('owner_packages.*')
                ->first();

            // No trial package found — skip
            if (! $trialPackage) {
                $this->warn("Lead #{$lead->id} — no trial package found for owner user_id {$owner->user_id} — skipping.");
                $skippedCount++;
                continue;
            }

            $trialEndsAt = Carbon::parse($trialPackage->end_date);

            // Trial still active — skip
            if (! $trialEndsAt->isPast()) {
                $skippedCount++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [dry-run] Would expire trial for lead #{$lead->id} — {$lead->company->company_name} (trial ended {$trialEndsAt->format('M d, Y')})");
                $expiredCount++;
                continue;
            }

            DB::beginTransaction();

            try {
                $lead->update([
                    'status'           => 'pending_conversion',
                    'last_activity_at' => now(),
                ]);

                LeadActivity::create([
                    'lead_id'     => $lead->id,
                    'user_id'     => null,
                    'type'        => 'trial_expired',
                    'description' => 'Trial period ended. Lead reverted to pending conversion.',
                ]);

                DB::commit();

                SendTrialExpiredMail::dispatch($lead->id);

                $expiredCount++;
                $this->info("Trial expired — lead #{$lead->id} ({$lead->company->company_name})");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to expire trial for lead #{$lead->id}: {$e->getMessage()}");
            }
        }

        $this->info("Expired:  {$expiredCount}");
        $this->info("Skipped:  {$skippedCount}");

        Log::info('trials:expire completed', [
            'expired' => $expiredCount,
            'skipped' => $skippedCount,
        ]);

        return self::SUCCESS;
    }
}