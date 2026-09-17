<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandlordReferral;
use App\Models\ReferralPayout;
use App\Models\User;
use App\Services\LandlordReferralService;
use App\Services\Payment\MpesaB2CService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $history = ReferralPayout::with('referrer')->latest()->limit(100)->get();

        // Rewards held for review (self-referral / velocity) — the admin can clear or claw back.
        $flagged = LandlordReferral::where('status', LandlordReferral::STATUS_CONFIRMED)
            ->where('needs_review', true)
            ->with(['referrer', 'owner'])
            ->latest()
            ->get();

        // Full-funnel visibility: every referral tenants have made, not just the payable ones.
        $statusCounts   = LandlordReferral::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
        $totalReferrers = (int) LandlordReferral::distinct('referrer_user_id')->count('referrer_user_id');
        $recent         = LandlordReferral::with('referrer')->latest()->limit(50)->get();

        return view('admin.referral-payouts.index', [
            'pageTitle'      => __('Referral Payouts'),
            'eligible'       => $eligible,
            'history'        => $history,
            'flagged'        => $flagged,
            'statusCounts'   => $statusCounts,
            'totalReferrers' => $totalReferrers,
            'recent'         => $recent,
            'currency'       => config('referrals.currency', 'KES'),
            'minPayout'      => (float) config('referrals.min_payout', 0),
        ]);
    }

    /** Pay a tenant's payable balance — via M-Pesa B2C, or record a manual out-of-band settlement. */
    public function payout(Request $request, int $userId)
    {
        $request->validate(['method' => 'required|in:b2c,manual', 'notes' => 'nullable|string|max:500']);

        $payout = $this->referrals->openPayout($userId);
        if (! $payout) {
            return back()->with('error', __('Nothing payable for this tenant (below the minimum, no phone, or already being paid).'));
        }

        if ($request->method === 'manual') {
            $this->referrals->settlePayoutManually($payout, $request->notes);
            return back()->with('success', __('Payout recorded as manually settled.'));
        }

        // ── B2C: send BEFORE the state change and outside a transaction — an accepted request
        //    must never be lost to a rollback. On reject, release the reserved referrals. ──
        try {
            $result = app(MpesaB2CService::class)->send($payout->phone, (float) $payout->amount, 'Referral reward', 'ReferralReward');
        } catch (\Throwable $e) {
            Log::error('Referral payout B2C send threw: ' . $e->getMessage(), ['payout_id' => $payout->id]);
            $this->referrals->cancelPayout($payout, 'B2C send error');
            return back()->with('error', __('M-Pesa payout could not be initiated. Please try again.'));
        }

        if (! ($result['success'] ?? false)) {
            $this->referrals->cancelPayout($payout, 'B2C rejected on send');
            return back()->with('error', __('M-Pesa rejected the payout: ') . ($result['message'] ?? __('unknown error')));
        }

        $this->referrals->markPayoutProcessing($payout, $result['reference'] ?? null);

        return back()->with('success', __('M-Pesa payout initiated — it will confirm once M-Pesa completes the transfer.'));
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
