<?php

namespace App\Console\Commands;

use App\Services\Screening\TenantCreditProfileService;
use Illuminate\Console\Command;

/**
 * Build/refresh the tenant credit profiles (Global Tenant ID backbone) from observed payment
 * behaviour. Run with no argument to recompute everyone, or pass a phone for a single person.
 *
 *   php artisan screening:recompute
 *   php artisan screening:recompute 0712345678
 */
class RecomputeTenantCreditProfiles extends Command
{
    protected $signature = 'screening:recompute {phone? : A single tenant phone to recompute}';
    protected $description = 'Aggregate tenant payment behaviour into credit profiles (Global Tenant ID)';

    public function handle(TenantCreditProfileService $service): int
    {
        $phone = $this->argument('phone');

        if ($phone) {
            $profile = $service->computeForPhone($phone);
            if (! $profile) {
                $this->warn('No tenancy found for that phone — nothing to profile.');
                return self::SUCCESS;
            }
            $this->info('Profile built for ' . ($profile->display_name ?: $profile->identity_key) . ':');
            $this->line(sprintf(
                '  <fg=cyan>Score: %s / 100  (%s, grade %s)%s</>',
                $profile->score ?? '—',
                ucfirst(str_replace('_', ' ', $profile->score_band ?? 'unrated')),
                $profile->score_grade ?? '—',
                $profile->is_thin_file ? '  [thin file — provisional]' : ''
            ));
            foreach (($profile->score_factors['notes'] ?? []) as $note) {
                $this->line('    • ' . $note);
            }
            $this->table(
                ['Tenancies', 'Owners', 'Invoices', 'Paid', 'On-time', 'Late', 'Overdue', 'On-time %', 'Avg days late', 'Outstanding'],
                [[
                    $profile->tenancies_count, $profile->owners_count, $profile->invoices_total, $profile->invoices_paid,
                    $profile->on_time_count, $profile->late_count, $profile->overdue_count,
                    $profile->on_time_rate ?? '—', $profile->avg_days_late ?? '—', number_format((float) $profile->outstanding, 2),
                ]]
            );
            return self::SUCCESS;
        }

        $this->info('Recomputing all tenant credit profiles…');
        $count = $service->recomputeAll(function ($key, $n) {
            if ($n % 100 === 0) {
                $this->line("  … {$n} profiles");
            }
        });
        $this->info("Done. {$count} profile(s) built/refreshed.");
        return self::SUCCESS;
    }
}
