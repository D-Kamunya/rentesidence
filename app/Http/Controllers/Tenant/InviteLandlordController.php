<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\LandlordReferral;
use App\Services\LandlordReferralService;
use Illuminate\Http\Request;

/**
 * The tenant-side of the invite-a-landlord funnel: a tenant's shareable invite link, the
 * landlords they've invited and where each stands, their reward balance, and the graduation
 * offer once they've proven they're a channel.
 *
 * Available to every tenant (linked or ownerless) — it sits OUTSIDE the tenant.owned guard,
 * because inviting a landlord is a growth action, not an owner-bound surface.
 */
class InviteLandlordController extends Controller
{
    public function __construct(private LandlordReferralService $referrals)
    {
    }

    public function index()
    {
        if (! $this->referrals->enabled()) {
            return redirect()->route('tenant.dashboard');
        }

        $user = auth()->user();
        $code = $this->referrals->codeForTenant($user->id);

        // Connected tenants (landlord already on CS) refer OTHER landlords; ownerless tenants
        // invite their own. The heading/nav/title differ so "Invite your landlord" never misleads.
        $isConnected = ! $user->isOwnerlessTenant();

        $referralList = LandlordReferral::where('referrer_user_id', $user->id)
            ->latest()
            ->get();

        $payable   = $this->referrals->payableBalance($user->id);
        $confirmed = $this->referrals->confirmedCashTotal($user->id);
        $paid      = $this->referrals->paidTotal($user->id);

        return view('tenant.invite-landlord.index', [
            'pageTitle'       => $isConnected ? __('Refer a Landlord') : __('Invite Your Landlord'),
            'isConnected'     => $isConnected,
            'code'            => $code,
            'inviteUrl'       => route('referral.invite', $code),
            'referralList'    => $referralList,
            'payableBalance'  => $payable,
            'pendingBalance'  => max(0, $confirmed - $payable), // confirmed but still within the hold window
            'paidBalance'     => $paid,
            'totalEarned'     => $confirmed + $paid,
            'confirmedCount'  => $this->referrals->confirmedCount($user->id),
            'cashEnabled'     => $this->referrals->cashEnabled(),
            'cashAmount'      => (float) config('referrals.cash_amount', 0),
            'currency'        => config('referrals.currency', 'KES'),
            'canGraduate'     => $this->referrals->graduationEligible($user->id),
            'graduationGoal'  => (int) config('referrals.graduation_threshold', 3),
        ]);
    }

    /** Record a landlord the tenant is inviting (velocity-capped in the service). */
    public function store(Request $request)
    {
        if (! $this->referrals->enabled()) {
            return redirect()->route('tenant.dashboard');
        }

        $validated = $request->validate([
            'invitee_name'  => ['nullable', 'string', 'max:120'],
            'invitee_phone' => ['nullable', 'string', 'max:32'],
            'invitee_email' => ['nullable', 'email', 'max:160'],
        ]);

        if (empty($validated['invitee_phone']) && empty($validated['invitee_email'])) {
            return back()->with('error', __('Add your landlord\'s phone or email so we can track the invite.'));
        }

        $referral = $this->referrals->startInvite(auth()->user(), [
            'name'  => $validated['invitee_name'] ?? null,
            'phone' => $validated['invitee_phone'] ?? null,
            'email' => $validated['invitee_email'] ?? null,
        ]);

        if (! $referral) {
            return back()->with('error', __('You\'ve sent a lot of invites today — please try again tomorrow.'));
        }

        // Dedupe guard: an existing invite for this landlord comes back not-recently-created —
        // don't re-notify (prevents repeat SMS/email to the same person).
        if (! $referral->wasRecentlyCreated) {
            return back()->with('success', __('You\'ve already invited this landlord — share your link to remind them.'));
        }

        // Reach the landlord on the tenant's behalf (platform-paid SMS + on-brand email).
        // No account is created — they still fill the vetted public form at /invite/{code}.
        $user = auth()->user();
        \App\Jobs\SendLandlordInviteJob::dispatch(
            trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: null,
            route('referral.invite', $referral->code),
            $referral->invitee_phone,
            $referral->invitee_email,
            $referral->invitee_name,
        );

        return back()->with('success', __('Invite sent! We\'ve reached out to your landlord, and you can share your link too.'));
    }
}
