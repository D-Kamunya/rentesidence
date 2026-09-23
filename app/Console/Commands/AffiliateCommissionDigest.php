<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Models\AffiliateCommissionPayment;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Monthly commission digest for affiliates — one email + in-app summary per affiliate for the
 * period, instead of a notification per commission event (which, with rent commissions firing per
 * payment × owner, would be a lot of sending at scale). Email + in-app only (no SMS — cost).
 * Idempotent per period via affiliate_commission_payments.digest_notified_at.
 *
 * Scheduled on the 1st of each month for the PREVIOUS month.
 */
class AffiliateCommissionDigest extends Command
{
    protected $signature = 'affiliate:commission-digest {--month=} {--year=}';
    protected $description = 'Email affiliates a monthly summary of the commissions they earned.';

    public function handle(): int
    {
        $target = now()->subMonthNoOverflow();
        $month  = (int) ($this->option('month') ?: $target->month);
        $year   = (int) ($this->option('year') ?: $target->year);
        $label  = Carbon::create($year, $month, 1)->format('F Y');

        $rows = AffiliateCommissionPayment::where('period_month', $month)
            ->where('period_year', $year)
            ->where('total_commission_payout', '>', 0)
            ->whereNull('digest_notified_at')
            ->get();

        $sent = 0;
        foreach ($rows as $row) {
            $user = Affiliate::find($row->affiliate_id)?->user;
            if (! $user) {
                continue;
            }

            $amount = currencyPrice($row->total_commission_payout);
            $title  = __('Your :month commission summary', ['month' => $label]);
            $body   = __('You earned :amount in commissions in :month. View the full breakdown on your affiliate dashboard.', ['amount' => $amount, 'month' => $label]);

            try {
                addNotification($title, $body, route('affiliate.dashboard'), null, $user->id, $user->id);
                if (! empty($user->email)) {
                    MailService::sendMail([$user->email], $title, $body, null);
                }
                $row->update(['digest_notified_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Affiliate commission digest failed', ['payment_id' => $row->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Affiliate commission digest: notified {$sent} affiliate(s) for {$label}.");

        return self::SUCCESS;
    }
}
