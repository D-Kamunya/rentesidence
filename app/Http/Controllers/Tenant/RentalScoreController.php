<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantCreditDispute;
use App\Models\TenantScreeningLookup;
use App\Services\Screening\TenantCreditProfileService;
use Illuminate\Http\Request;

/**
 * The tenant-owned view of their Global Tenant ID — "My Rental Score". Transparency (they see
 * their own factual profile), ownership (they can ACTIVATE it to use for faster applications +
 * loan offers), and fairness (they can DISPUTE what it shows). This is the onboarding magnet.
 */
class RentalScoreController extends Controller
{
    public function __construct(private TenantCreditProfileService $service)
    {
    }

    public function index()
    {
        $user = auth()->user();

        // Always show current data — recompute this one person's profile on view.
        $profile = $this->service->computeForPhone($user->contact_number);

        $disputes = $profile
            ? TenantCreditDispute::where('tenant_credit_profile_id', $profile->id)
                ->where('user_id', $user->id)->latest()->get()
            : collect();

        // Transparency: which owners have looked up this person's record (bureau posture —
        // they can see who screened them). Only charged/shown lookups, not the audit-only miss.
        $lookups = $profile
            ? TenantScreeningLookup::where('tenant_credit_profile_id', $profile->id)
                ->where('billed_as', '!=', 'none')
                ->latest()->take(10)->get()
            : collect();

        return view('tenant.rental-score.index', [
            'profile'  => $profile,
            'disputes' => $disputes,
            'lookups'  => $lookups,
            'pageTitle' => __('My Rental Score'),
        ]);
    }

    /** Opt in / out of actively using the Rental ID (the value-exchange consent). */
    public function activate(Request $request)
    {
        $user    = auth()->user();
        $profile = $this->service->computeForPhone($user->contact_number);
        if (! $profile) {
            return back()->with('error', __('No rental profile is available yet.'));
        }

        if ($profile->activated_at) {
            $profile->update(['activated_at' => null, 'activated_by_user_id' => null]);
            return back()->with('success', __('Your Rental ID sharing has been turned off.'));
        }

        $profile->update(['activated_at' => now(), 'activated_by_user_id' => $user->id]);
        return back()->with('success', __('Your Rental ID is active — you can now share it with landlords and unlock loan offers.'));
    }

    /** Raise a dispute against what the profile shows (fairness path — admin reviews). */
    public function dispute(Request $request)
    {
        $request->validate(['message' => 'required|string|min:10|max:1000']);

        $user    = auth()->user();
        $profile = $this->service->computeForPhone($user->contact_number);
        if (! $profile) {
            return back()->with('error', __('No rental profile is available to dispute.'));
        }

        TenantCreditDispute::create([
            'tenant_credit_profile_id' => $profile->id,
            'user_id'                  => $user->id,
            'message'                  => $request->message,
            'status'                   => 'open',
        ]);

        return back()->with('success', __('Your dispute has been submitted. Our team will review it and get back to you.'));
    }

    /**
     * Tenant's side of the loop after an admin reply: acknowledge that it helped, or push back
     * with a follow-up (which reopens the dispute for another look). Scoped to their own dispute.
     */
    public function disputeReply(Request $request)
    {
        $request->validate([
            'dispute_id' => 'required|integer',
            'action'     => 'required|in:ack,reply',
            'message'    => 'nullable|required_if:action,reply|string|min:5|max:1000',
        ]);

        $dispute = TenantCreditDispute::where('id', $request->dispute_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($request->action === 'ack') {
            $dispute->update(['tenant_ack_at' => now()]);
            return back()->with('success', __('Thanks — glad we could help.'));
        }

        // Follow-up → reopen for another review.
        $dispute->update([
            'tenant_reply'  => $request->message,
            'status'        => 'open',
            'tenant_ack_at' => null,
        ]);
        return back()->with('success', __('Thanks — we\'ve reopened your dispute and will take another look.'));
    }
}
