<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\AffiliateApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Public "become an affiliate" page — the shareable link the team sends to prospects in the field.
 * A submission creates an AffiliateApplication (never an account) and pings admin; admin turns it
 * into a full affiliate in one click from their inbox.
 *
 * The apply page leads with the commission-only Partner Terms on purpose: reaching the form means
 * the applicant has seen the hard terms, and the `agree` checkbox is a RECORDED consent to them
 * (timestamp + IP + version stored on the row) — the structural fix for V1's "I didn't read the JD
 * and thought there was a salary" commission disputes.
 */
class AffiliateApplicationController extends Controller
{
    public function create()
    {
        return view('affiliate-apply.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'       => 'required|string|max:60',
            'last_name'        => 'required|string|max:60',
            'email'            => 'required|email|max:120',
            'phone'            => 'required|string|max:20',
            'country'          => ['required', 'string', Rule::in(csMarketCountries())],
            // County must belong to the chosen country's divisions (market-aware).
            'county'           => ['required', 'string', Rule::in(csMarketDivisions($request->input('country')))],
            'town'             => 'nullable|string|max:120',
            'experience'       => 'nullable|string|max:2000',
            'finance_contacts' => 'nullable|string|max:2000',
            'pitch'            => 'nullable|string|max:2000',
            'agree'            => 'accepted',
        ], [
            'country.required' => __('Please select your country.'),
            'country.in'       => __('Please select a country we currently operate in.'),
            'county.required'  => __('Please select your county.'),
            'county.in'        => __('Please choose a county from the list for your country.'),
            'agree.accepted'   => __('Please confirm you\'ve read and accept the commission-only Partner Terms to continue.'),
        ]);

        // Already an affiliate? Point them to sign in rather than creating a duplicate application.
        $existingAffiliate = User::where('email', $data['email'])->where('role', USER_ROLE_AFFILIATE)->exists();
        if ($existingAffiliate) {
            return redirect()->route('affiliate.apply')->with('already', true);
        }

        unset($data['agree']); // the checkbox gates submission; it isn't a column — consent is recorded below.

        // Recorded consent to the commission-only Partner Terms (auditable e-consent on the row).
        $consent = [
            'agreed_at'       => now(),
            'consent_ip'      => $request->ip(),
            'consent_version' => AffiliateApplication::CONSENT_VERSION,
        ];

        // Idempotent: a pending application with this email re-confirms (no duplicate) — refresh both
        // their latest details and the recorded consent rather than stacking rows.
        $existing = AffiliateApplication::where('email', $data['email'])
            ->where('status', AffiliateApplication::STATUS_PENDING)->first();

        if ($existing) {
            $existing->update($data + $consent);
        } else {
            $application = AffiliateApplication::create(
                $data + $consent + ['status' => AffiliateApplication::STATUS_PENDING]
            );
            $this->notifyAdmins($application);
        }

        return redirect()->route('affiliate.apply')->with('applied', true);
    }

    /** In-app notification to every admin that a new application arrived. */
    private function notifyAdmins(AffiliateApplication $application): void
    {
        // The notification bell INNER-JOINs users on sender_id, so a null sender is created-but-never-
        // shown. Use the earliest admin as the system sender so the notice actually surfaces.
        $systemSenderId = User::where('role', USER_ROLE_ADMIN)->min('id');
        if (! $systemSenderId) {
            return; // no admins → no recipients anyway
        }

        User::where('role', USER_ROLE_ADMIN)->pluck('id')->each(function ($adminId) use ($application, $systemSenderId) {
            addNotification(
                __('New affiliate application'),
                $application->name . ' — ' . $application->email,
                route('admin.affiliates.applications.index'),
                null,
                $adminId,
                (int) $systemSenderId
            );
        });
    }
}
