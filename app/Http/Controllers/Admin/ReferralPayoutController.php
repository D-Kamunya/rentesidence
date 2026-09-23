<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendLoginDetailsJob;
use App\Models\LandlordReferral;
use App\Models\LeadActivity;
use App\Models\Owner;
use App\Models\Package;
use App\Models\ReferralPayout;
use App\Models\User;
use App\Services\LandlordReferralService;
use App\Services\OwnerOnboardingService;
use App\Services\Payment\MpesaB2CService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Admin review + settlement of invite-a-landlord reward payouts.
 *
 * Payouts are admin-initiated and batched (we control how and when money leaves — the tenant
 * never "requests"). Each pays a tenant's confirmed, held-period-elapsed rewards to their
 * registered M-Pesa, reusing MpesaB2CService and the shared B2CResult/B2CTimeout callback.
 */
class ReferralPayoutController extends Controller
{
    public function __construct(private LandlordReferralService $referrals)
    {
    }

    public function index()
    {
        // The work-list: tenants at or above the min-payout floor, enriched with who they are.
        $eligible = $this->referrals->tenantsEligibleForPayout()
            ->map(function ($row) {
                $user = User::find($row->referrer_user_id);
                return (object) [
                    'user_id'      => $row->referrer_user_id,
                    'name'         => $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : ('#' . $row->referrer_user_id),
                    'phone'        => $user->contact_number ?? null,
                    'payable'      => (float) $row->payable,
                    'reward_count' => (int) $row->reward_count,
                ];
            });

        // Tenant-initiated payout requests awaiting admin review/release (mirrors affiliate withdrawals).
        $payoutRequests = ReferralPayout::with('referrer')
            ->whereIn('status', [ReferralPayout::STATUS_PENDING, ReferralPayout::STATUS_PROCESSING])
            ->latest()
            ->get();

        $history = ReferralPayout::with('referrer')
            ->whereIn('status', [ReferralPayout::STATUS_PAID, ReferralPayout::STATUS_FAILED, ReferralPayout::STATUS_CANCELLED])
            ->latest()->limit(100)->get();

        // Rewards held for review (self-referral / velocity) — the admin can clear or claw back.
        $flagged = LandlordReferral::where('status', LandlordReferral::STATUS_CONFIRMED)
            ->where('needs_review', true)
            ->with(['referrer', 'owner'])
            ->latest()
            ->get();

        // Owner sign-up requests — a referred landlord filled the form; admin onboards them into
        // an owner in one click (a referral is the platform's lead, never an affiliate's to claim).
        // Only those whose lead exists and hasn't become an owner yet.
        $onboardRequests = LandlordReferral::where('status', LandlordReferral::STATUS_LEAD_CREATED)
            ->whereNull('owner_id')
            ->whereHas('lead', fn ($q) => $q->whereNull('owner_id'))
            ->with(['referrer', 'lead.company'])
            ->latest()
            ->get();

        // Full-funnel visibility: every referral tenants have made, not just the payable ones.
        $statusCounts   = LandlordReferral::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
        $totalReferrers = (int) LandlordReferral::distinct('referrer_user_id')->count('referrer_user_id');
        $recent         = LandlordReferral::with('referrer')->latest()->limit(50)->get();

        // Graduations — tenants who upgraded to affiliate (conversion tracking).
        $graduationsCount = (int) \App\Models\Affiliate::whereNotNull('origin_tenant_user_id')->count();
        $graduations = \App\Models\Affiliate::whereNotNull('origin_tenant_user_id')
            ->orderByDesc('graduated_at')->limit(50)->get()
            ->map(function ($a) {
                $t = User::find($a->origin_tenant_user_id);
                return (object) [
                    'name'         => $t ? (trim($t->first_name . ' ' . $t->last_name) ?: ('#' . $a->origin_tenant_user_id)) : ('#' . $a->origin_tenant_user_id),
                    'user_id'      => $a->origin_tenant_user_id,
                    'code'         => $a->referral_code,
                    'graduated_at' => $a->graduated_at,
                ];
            });

        return view('admin.referral-payouts.index', [
            'pageTitle'       => __('Referrals'),
            'eligible'        => $eligible,
            'payoutRequests'  => $payoutRequests,
            'onboardRequests' => $onboardRequests,
            'history'         => $history,
            'flagged'         => $flagged,
            'statusCounts'    => $statusCounts,
            'totalReferrers'  => $totalReferrers,
            'recent'          => $recent,
            'graduationsCount' => $graduationsCount,
            'graduations'     => $graduations,
            'currency'        => config('referrals.currency', 'KES'),
            'minPayout'       => (float) config('referrals.min_payout', 0),
        ]);
    }

    /**
     * Release a tenant's payout REQUEST — via M-Pesa B2C, or record a manual out-of-band
     * settlement. The tenant already requested it (a pending ReferralPayout with the reserved
     * rewards + their M-Pesa number); this reviews and releases it, mirroring affiliate withdrawals.
     */
    public function approvePayout(Request $request, ReferralPayout $payout)
    {
        $request->validate(['method' => 'required|in:b2c,manual', 'notes' => 'nullable|string|max:500']);

        if ($payout->status !== ReferralPayout::STATUS_PENDING) {
            return back()->with('error', __('This payout is not awaiting release.'));
        }

        if ($request->method === 'manual') {
            $this->referrals->settlePayoutManually($payout, $request->notes);
            return back()->with('success', __('Payout recorded as manually settled.'));
        }

        if (! $payout->phone) {
            return back()->with('error', __('This request has no M-Pesa number — settle it manually.'));
        }

        // ── B2C: send BEFORE the state change and outside a transaction. On send-reject we leave
        //    the request PENDING (referrals stay reserved) so it can be retried — mirrors affiliates. ──
        try {
            $result = app(MpesaB2CService::class)->send($payout->phone, (float) $payout->amount, 'Referral reward', 'ReferralReward');
        } catch (\Throwable $e) {
            Log::error('Referral payout B2C send threw: ' . $e->getMessage(), ['payout_id' => $payout->id]);
            return back()->with('error', __('M-Pesa payout could not be initiated. Please try again.'));
        }

        if (! ($result['success'] ?? false)) {
            return back()->with('error', __('M-Pesa rejected the payout: ') . ($result['message'] ?? __('unknown error')));
        }

        $this->referrals->markPayoutProcessing($payout, $result['reference'] ?? null);

        return back()->with('success', __('M-Pesa payout initiated — it will confirm once M-Pesa completes the transfer.'));
    }

    /** Decline a payout request — releases the reserved rewards back to the tenant's payable balance. */
    public function rejectPayout(Request $request, ReferralPayout $payout)
    {
        if ($payout->status !== ReferralPayout::STATUS_PENDING) {
            return back()->with('error', __('Only a pending request can be declined.'));
        }

        $this->referrals->cancelPayout($payout, 'Declined by admin: ' . (string) $request->input('reason', ''));

        return back()->with('success', __('Payout request declined — the rewards are back in the tenant\'s balance.'));
    }

    /**
     * One-click: turn a referred landlord's sign-up request into a full owner account.
     *
     * Reuses the same onboarding lifecycle as a lead conversion — an auto-generated temporary
     * password, credentials delivered by email + SMS, and a forced reset on first login
     * (must_change_password) — but WITHOUT requiring an affiliate (a referral is the platform's
     * lead). Links the lead and the referral to the new owner so the reward confirms when the
     * owner later pays. Atomic; credentials go out only after the commit.
     */
    public function createOwner(Request $request, int $referralId)
    {
        $referral = LandlordReferral::with('lead.company')->findOrFail($referralId);
        $lead = $referral->lead;

        if (! $lead) {
            return back()->with('error', __('This request has no lead attached.'));
        }
        if ($lead->owner_id || $referral->owner_id) {
            return back()->with('error', __('An owner account already exists for this request.'));
        }

        $company = $lead->company;
        $email = $company->email ?? $referral->invitee_email;
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', __('This request has no valid email — the account and its setup link are keyed on it.'));
        }
        if (User::where('email', $email)->exists()) {
            return back()->with('error', __('An account already exists with this email address.'));
        }

        DB::beginTransaction();
        try {
            $nameParts = explode(' ', trim($referral->invitee_name ?: $company->company_name), 2);

            // Shared owner-onboarding machinery (temp password, forced reset, trial + defaults).
            ['user' => $user, 'owner' => $owner, 'password' => $plainPassword] =
                app(OwnerOnboardingService::class)->create([
                    'first_name'   => $nameParts[0] ?? 'Owner',
                    'last_name'    => $nameParts[1] ?? '',
                    'phone'        => $company->phone ?: $referral->invitee_phone,
                    'email'        => $email,
                    'affiliate_id' => null, // a referral has no affiliate
                ]);

            $lead->update([
                'owner_id'         => $owner->id,
                'status'           => 'converted',
                'converted_at'     => now(),
                'last_activity_at' => now(),
            ]);
            optional($company)->update(['sales_status' => 'client']);

            // Link the referral to the owner so the reward confirms when they transact.
            $referral->update(['owner_id' => $owner->id]);

            LeadActivity::create([
                'lead_id'     => $lead->id,
                'user_id'     => auth()->id(),
                'type'        => 'trial_started',
                'description' => 'Owner account created by admin from a tenant invite-a-landlord referral.',
            ]);

            DB::commit();

            // Deliver the login credentials (email + SMS, forced reset on first login).
            SendLoginDetailsJob::dispatch($user, $plainPassword);

            // DEV ONLY: surface the temp password in a persistent panel so the flow can be tested
            // without live email/SMS. Never in prod.
            if (config('app.debug')) {
                session()->flash('dev_credentials', app(OwnerOnboardingService::class)->devCredentials($user, $plainPassword));
            }

            return back()->with('success', __('Owner account created — login details sent to :email.', ['email' => $email]));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Referral owner creation failed: ' . $e->getMessage(), ['referral_id' => $referralId]);
            return back()->with('error', __('Could not create the owner account. Please try again.'));
        }
    }

    /** Claw back a confirmed (not-yet-paid) reward — e.g. the referred owner churned or refunded. */
    public function clawback(Request $request, int $ownerId)
    {
        $referral = $this->referrals->clawback($ownerId, $request->input('reason', 'admin'));

        return $referral
            ? back()->with('success', __('Reward clawed back.'))
            : back()->with('error', __('No confirmed reward to claw back for that owner.'));
    }
}
