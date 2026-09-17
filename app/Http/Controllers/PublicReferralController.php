<?php

namespace App\Http\Controllers;

use App\Services\LandlordReferralService;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The public face of the invite-a-landlord funnel — the /invite/{code} landing an invited
 * landlord opens, and the intake form they submit.
 *
 * Owners can NEVER self-register: this form creates a LEAD (via LeadService), which enters the
 * same marketplace/vetting pipeline as any other, and links back to the referring tenant through
 * the attribution ledger. It never creates an owner account directly.
 */
class PublicReferralController extends Controller
{
    public function __construct(
        private LandlordReferralService $referrals,
        private LeadService $leads,
    ) {
    }

    /** The invite landing: who invited them + what Centresidence does + the intake form. */
    public function landing(string $code)
    {
        // Funnel off → don't advertise a dead link; fall back to the home page.
        if (! $this->referrals->enabled()) {
            return redirect()->route('frontend');
        }

        $referrer = $this->referrals->resolveReferrer($code);

        // An unknown code still shows the "get started" form (never hard-error a shared link);
        // it just won't be attributed to a tenant.
        $referrerName = $referrer
            ? trim(($referrer->first_name ?? '') . ' ' . substr((string) ($referrer->last_name ?? ''), 0, 1))
            : null;

        return view('referral.invite', [
            'pageTitle'    => __('You\'re invited to Centresidence'),
            'code'         => $code,
            'referrerName' => $referrerName ?: null,
            'hasReferrer'  => (bool) $referrer,
        ]);
    }

    /** Handle the intake submission: create the lead + attribute it to the referrer. */
    public function submit(Request $request, string $code)
    {
        if (! $this->referrals->enabled()) {
            return redirect()->route('frontend');
        }

        $validated = $request->validate([
            'contact_person_name' => ['required', 'string', 'max:120'],
            'company_name'        => ['required', 'string', 'max:160'],
            'phone'               => ['required', 'string', 'max:32'],
            'email'               => ['nullable', 'email', 'max:160'],
            'city'                => ['nullable', 'string', 'max:120'],
            'country'             => ['nullable', 'string', 'max:120'],
            'estimated_units'     => ['nullable', 'integer', 'min:0', 'max:100000'],
            'property_type'       => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($validated, $code) {
            $lead = $this->leads->createReferralMarketplaceLead($validated);

            $this->referrals->attachLead($code, $lead, [
                'name'    => $validated['contact_person_name'],
                'phone'   => $validated['phone'],
                'email'   => $validated['email'] ?? null,
                'company' => $validated['company_name'],
            ]);
        });

        return view('referral.submitted', [
            'pageTitle' => __('Thank you'),
        ]);
    }
}
