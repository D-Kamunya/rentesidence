<?php

namespace App\Services\Screening;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\TenantCreditProfile;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The payment-behaviour aggregation engine — Step 1 of the Global Tenant ID.
 *
 * For a tenant PERSON (resolved across every owner/tenancy by phone), it aggregates the
 * OBJECTIVE facts the system already observed — how they actually paid their invoices (on-time
 * vs late, arrears, tenure) — into a TenantCreditProfile. This is the factual, non-defamatory
 * backbone; landlord opinions and the compound SCORE layer on top later.
 *
 * Payment date is taken from the order that settled each paid invoice (invoice.order_id) —
 * every paid invoice, online or manual, has one.
 */
class TenantCreditProfileService
{
    /** Compute (or refresh) the profile for the person owning this phone. */
    public function computeForPhone(?string $phone): ?TenantCreditProfile
    {
        $key = $this->normalizePhone((string) $phone);
        return $key === '' ? null : $this->computeForIdentity($key);
    }

    /** Compute for the person behind a specific tenancy/user. */
    public function computeForTenant(Tenant $tenant): ?TenantCreditProfile
    {
        $phone = optional($tenant->user)->contact_number;
        return $this->computeForPhone($phone);
    }

    /**
     * Aggregate every tenancy + invoice belonging to the person with this canonical identity
     * (normalised phone) across ALL owners, and upsert their credit profile. Returns null when
     * the person has no tenancy on record.
     */
    public function computeForIdentity(string $identityKey): ?TenantCreditProfile
    {
        $variants = $this->phoneVariants($identityKey);

        // 1. The person's tenant logins across owners (matched by phone). withTrashed so a
        //    historically-removed account still contributes its payment history (identity is
        //    permanent — see TenantService::delete).
        $users = User::withTrashed()->where('role', USER_ROLE_TENANT)
            ->whereIn('contact_number', $variants)
            ->get(['id', 'first_name', 'last_name', 'contact_number', 'created_at']);

        if ($users->isEmpty()) {
            return null;
        }
        $userIds = $users->pluck('id')->all();

        // 2. Their tenancies (breadth signals + landlord ratings). withTrashed so a removed
        //    tenancy still counts.
        $tenants = Tenant::withTrashed()->whereIn('user_id', $userIds)
            ->get(['id', 'owner_user_id', 'created_at', 'rent_payment_rating', 'discipline_rating']);
        if ($tenants->isEmpty()) {
            return null;
        }
        $tenantIds = $tenants->pluck('id')->all();

        // 3. Every invoice raised against those tenancies.
        $invoices = Invoice::whereIn('tenant_id', $tenantIds)
            ->get(['id', 'order_id', 'amount', 'due_date', 'billing_period', 'status', 'created_at', 'updated_at']);

        // Settlement dates come from the linked orders (one per paid invoice).
        $orderDates = $invoices->pluck('order_id')->filter()->unique()->isNotEmpty()
            ? Order::whereIn('id', $invoices->pluck('order_id')->filter()->unique()->all())->pluck('created_at', 'id')
            : collect();

        // 4. Aggregate.
        $m = [
            'invoices_total' => 0, 'invoices_paid' => 0, 'on_time_count' => 0, 'late_count' => 0, 'overdue_count' => 0,
            'total_billed' => 0.0, 'total_paid' => 0.0, 'outstanding' => 0.0,
        ];
        $daysLateSum = 0.0;
        $now = Carbon::now();
        $firstActivity = null;
        $lastActivity  = null;

        foreach ($invoices as $inv) {
            $amount = (float) $inv->amount;
            $m['invoices_total']++;
            $m['total_billed'] += $amount;

            $due = $this->parseSafe($inv->due_date);
            $activityDate = $this->parseSafe($inv->billing_period) ?? $this->parseSafe($inv->created_at) ?? $now;
            $firstActivity = $firstActivity ? $firstActivity->min($activityDate) : $activityDate;
            $lastActivity  = $lastActivity ? $lastActivity->max($activityDate) : $activityDate;

            if ((int) $inv->status === INVOICE_STATUS_PAID) {
                $m['invoices_paid']++;
                $m['total_paid'] += $amount;

                $paidAt = ($inv->order_id && isset($orderDates[$inv->order_id]) ? $this->parseSafe($orderDates[$inv->order_id]) : null)
                    ?? $this->parseSafe($inv->updated_at);
                if ($paidAt) {
                    $lastActivity = $lastActivity->max($paidAt);
                }

                if ($due && $paidAt) {
                    if ($paidAt->lessThanOrEqualTo($due)) {
                        $m['on_time_count']++;
                    } else {
                        $m['late_count']++;
                        $daysLateSum += $due->diffInDays($paidAt); // whole days late
                    }
                } else {
                    $m['on_time_count']++; // no reliable due/paid date → treat as met (don't penalise)
                }
            } else {
                $m['outstanding'] += $amount;
                if ($due && $due->lessThan($now)) {
                    $m['overdue_count']++;
                }
            }
        }

        // Tenancy dates also count toward first/last activity.
        foreach ($tenants as $t) {
            $c = $this->parseSafe($t->created_at);
            if (! $c) {
                continue;
            }
            $firstActivity = $firstActivity ? $firstActivity->min($c) : $c;
            $lastActivity  = $lastActivity ? $lastActivity->max($c) : $c;
        }

        // Aggregated landlord ratings — average the numeric rent/discipline ratings (1–5) across
        // every rated tenancy. Secondary signal; individual ratings never surfaced.
        $ratingSum = 0; $ratingN = 0; $ratedTenancies = 0;
        foreach ($tenants as $t) {
            $r1 = $this->parseRating($t->rent_payment_rating);
            $r2 = $this->parseRating($t->discipline_rating);
            if ($r1 !== null) { $ratingSum += $r1; $ratingN++; }
            if ($r2 !== null) { $ratingSum += $r2; $ratingN++; }
            if ($r1 !== null || $r2 !== null) { $ratedTenancies++; }
        }
        $landlordAvg = $ratingN > 0 ? round($ratingSum / $ratingN, 2) : null; // 1–5

        $latest = $users->sortByDesc('created_at')->first();

        $data = array_merge($m, [
            'landlord_rating_avg' => $landlordAvg,
            'ratings_count'       => $ratedTenancies,
            'phone'             => $identityKey,
            'display_name'      => trim(($latest->first_name ?? '') . ' ' . ($latest->last_name ?? '')) ?: null,
            'tenancies_count'   => $tenants->count(),
            'owners_count'      => $tenants->pluck('owner_user_id')->unique()->count(),
            'on_time_rate'      => $m['invoices_paid'] > 0 ? round($m['on_time_count'] / $m['invoices_paid'] * 100, 2) : null,
            'avg_days_late'     => $m['late_count'] > 0 ? round($daysLateSum / $m['late_count'], 2) : ($m['invoices_paid'] > 0 ? 0 : null),
            'first_activity_at' => $firstActivity,
            'last_activity_at'  => $lastActivity,
            'computed_at'       => $now,
        ]);

        // Compound score from the objective metrics (Step 2 — behaviour-weighted, versioned).
        $scored = (new TenantScoreService())->score([
            'invoices_total' => $m['invoices_total'],
            'invoices_paid'  => $m['invoices_paid'],
            'overdue_count'  => $m['overdue_count'],
            'total_billed'   => $m['total_billed'],
            'outstanding'    => $m['outstanding'],
            'on_time_rate'   => $data['on_time_rate'],
            'avg_days_late'  => $data['avg_days_late'],
            'ratings_count'  => $ratedTenancies,
            'landlord_rating' => $landlordAvg !== null ? round($landlordAvg / 5 * 100, 2) : null, // 1–5 → 0–100
        ]);
        $data['score']         = $scored['score'];
        $data['score_band']    = $scored['band'];
        $data['score_grade']   = $scored['grade'];
        $data['score_version'] = $scored['version'];
        $data['is_thin_file']  = $scored['thin_file'];
        $data['score_factors'] = $scored['factors'];

        return DB::transaction(function () use ($identityKey, $data) {
            $profile = TenantCreditProfile::firstOrNew(['identity_key' => $identityKey]);
            $profile->fill($data);
            $profile->save();
            return $profile;
        });
    }

    /**
     * Recompute every tenant person's profile. Chunks through distinct tenant phones. Intended
     * for a scheduled backbone refresh / one-off backfill; returns the number of profiles built.
     */
    public function recomputeAll(?callable $progress = null): int
    {
        $seen  = [];
        $count = 0;

        User::withTrashed()->where('role', USER_ROLE_TENANT)
            ->whereNotNull('contact_number')
            ->orderBy('id')
            ->chunk(500, function ($users) use (&$seen, &$count, $progress) {
                foreach ($users as $user) {
                    $key = $this->normalizePhone((string) $user->contact_number);
                    if ($key === '' || isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    if ($this->computeForIdentity($key)) {
                        $count++;
                        if ($progress) {
                            $progress($key, $count);
                        }
                    }
                }
            });

        return $count;
    }

    /** A stored rating like "4 - Good" → its leading 1–5 integer, or null if absent/invalid. */
    private function parseRating($value): ?int
    {
        if (empty($value)) {
            return null;
        }
        $n = (int) $value; // leading digit
        return ($n >= 1 && $n <= 5) ? $n : null;
    }

    /** Parse a date, rejecting empty / zero-dates / garbage (year outside 2000–2100) → null. */
    private function parseSafe($value): ?Carbon
    {
        if (empty($value) || (is_string($value) && str_starts_with($value, '0000'))) {
            return null;
        }
        try {
            $c = Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
        return ($c->year < 2000 || $c->year > 2100) ? null : $c;
    }

    /** 07XX / 01XX / 7XXXXXXXX / +2547XX / 2547XX → 2547XXXXXXXX (canonical). */
    public function normalizePhone(string $raw): string
    {
        $d = preg_replace('/\D/', '', $raw);
        if ($d === '') {
            return '';
        }
        if (str_starts_with($d, '0')) {
            return '254' . substr($d, 1);
        }
        if (strlen($d) === 9 && (str_starts_with($d, '7') || str_starts_with($d, '1'))) {
            return '254' . $d;
        }
        return $d;
    }

    /** The stored contact_number formats that map to one canonical key (for whereIn matching). */
    private function phoneVariants(string $canonical): array
    {
        $out = [$canonical];
        if (preg_match('/^254(\d{9})$/', $canonical, $m)) {
            $local = $m[1];
            $out[] = '0' . $local;
            $out[] = '+' . $canonical;
            $out[] = $local;
            $out[] = '+254' . $local;
        }
        return array_values(array_unique($out));
    }

    /**
     * Distinct owner_user_ids of every landlord this person has a tenancy with (across all their
     * tenancies, including removed ones). Used to route a dispute to the owner(s) who can actually
     * reconcile off-system payments. Returns [] when none resolve.
     */
    public function ownerUserIdsFor(TenantCreditProfile $profile): array
    {
        if (empty($profile->identity_key)) {
            return [];
        }
        $variants = $this->phoneVariants($profile->identity_key);
        $userIds  = User::withTrashed()->where('role', USER_ROLE_TENANT)
            ->whereIn('contact_number', $variants)->pluck('id');
        if ($userIds->isEmpty()) {
            return [];
        }
        return Tenant::withTrashed()->whereIn('user_id', $userIds)
            ->pluck('owner_user_id')->filter()->unique()->values()->all();
    }
}
