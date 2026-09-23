<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The invite-a-landlord GRADUATION path: a tenant who has proven they're a channel (enough
 * confirmed referrals) upgrades into a full affiliate — WITHOUT losing their tenant account.
 *
 * We keep the app's one-role-per-user model intact by creating a SEPARATE affiliate account,
 * LINKED to the origin tenant (affiliates.origin_tenant_user_id + graduated_at). The person
 * moves between the two with a session-swap switch — they never log in to the affiliate account
 * directly, so it needs no deliverable credentials. Its email is a +aff alias of the tenant's,
 * only to satisfy the unique constraint (it delivers to the same inbox on +-aliasing providers).
 */
class AffiliateGraduationService
{
    /** The affiliate account a tenant graduated into, if any. */
    public function affiliateForTenant(int $tenantUserId): ?Affiliate
    {
        return Affiliate::where('origin_tenant_user_id', $tenantUserId)->first();
    }

    /** Whether this tenant has already graduated. */
    public function hasGraduated(int $tenantUserId): bool
    {
        return Affiliate::where('origin_tenant_user_id', $tenantUserId)->exists();
    }

    /**
     * The linked account the current user can switch INTO, or null. Tenant → their affiliate;
     * affiliate → the origin tenant. Only ever resolves the same person's two linked accounts.
     *
     * @return array{user: User, route: string, label: string}|null
     */
    public function switchTargetFor(?User $user): ?array
    {
        if (! $user) {
            return null;
        }
        $role = (int) $user->role;

        if ($role === USER_ROLE_TENANT) {
            $aff = $this->affiliateForTenant($user->id);
            $target = $aff ? User::find($aff->user_id) : null;
            if ($target) {
                return ['user' => $target, 'route' => 'affiliate.dashboard', 'label' => __('Switch to affiliate')];
            }
        } elseif ($role === USER_ROLE_AFFILIATE) {
            $aff = Affiliate::where('user_id', $user->id)->whereNotNull('origin_tenant_user_id')->first();
            $target = $aff ? User::find($aff->origin_tenant_user_id) : null;
            if ($target) {
                return ['user' => $target, 'route' => 'tenant.dashboard', 'label' => __('Switch to tenant')];
            }
        }

        return null;
    }

    /**
     * Graduate a tenant into a linked affiliate account. Idempotent: returns the existing
     * affiliate if they've already graduated. Returns null only if the tenant has no email
     * (the alias is derived from it). Notifies admin so conversions are tracked.
     */
    public function graduate(User $tenant): ?Affiliate
    {
        if ($existing = $this->affiliateForTenant($tenant->id)) {
            return $existing;
        }
        if (empty($tenant->email)) {
            return null;
        }

        $affiliate = DB::transaction(function () use ($tenant) {
            $user = new User();
            $user->first_name = $tenant->first_name;
            $user->last_name = $tenant->last_name;
            // contact_number is unique on users and already held by the tenant account; the
            // affiliate account is reached via the session-swap (never its own login) and asks
            // for an M-Pesa number at withdrawal time, so it's left null.
            $user->contact_number = null;
            $user->email = $this->aliasEmail($tenant); // unique; the tenant reaches this via the switch
            $user->password = Hash::make(Str::random(24)); // never used directly (session-swap)
            $user->must_change_password = 0;
            $user->status = USER_STATUS_ACTIVE;
            $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
            $user->role = USER_ROLE_AFFILIATE;
            $user->verify_token = str_replace('-', '', Str::uuid()->toString());
            $user->save();

            $affiliate = new Affiliate();
            $affiliate->user_id = $user->id;
            $affiliate->referral_code = $this->uniqueReferralCode();
            $affiliate->origin_tenant_user_id = $tenant->id;
            $affiliate->graduated_at = now();
            if (\Illuminate\Support\Facades\Schema::hasColumn('affiliates', 'status')) {
                $affiliate->status = AFFILIATE_STATUS_ACTIVE;
            }
            $affiliate->save();

            return $affiliate;
        });

        $this->notifyAdmin($tenant, $affiliate);

        return $affiliate;
    }

    /** FYI notification to admin(s) — conversions are tracked, never gated. */
    private function notifyAdmin(User $tenant, Affiliate $affiliate): void
    {
        try {
            $admin = User::where('role', USER_ROLE_ADMIN)->first();
            if (! $admin || ! function_exists('addNotification')) {
                return;
            }
            $name = trim($tenant->first_name . ' ' . $tenant->last_name) ?: ('#' . $tenant->id);
            addNotification(
                __('New affiliate graduation'),
                __(':name graduated from tenant to affiliate.', ['name' => $name]),
                route('admin.referral-payouts.index'),
                null,
                $admin->id,
                $admin->id,
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Graduation admin notify failed: ' . $e->getMessage());
        }
    }

    /** A unique +aff alias of the tenant's email (satisfies the users.email unique constraint). */
    private function aliasEmail(User $tenant): string
    {
        $email = (string) $tenant->email;
        $at = strpos($email, '@');
        if ($at === false) {
            return 'aff' . $tenant->id . '@centresidence.local';
        }
        $local = substr($email, 0, $at);
        $domain = substr($email, $at + 1);

        return $local . '+aff' . $tenant->id . '@' . $domain;
    }

    private function uniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (Affiliate::where('referral_code', $code)->exists());

        return $code;
    }
}
