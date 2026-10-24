<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\AffiliateApplication;
use App\Models\User;
use App\Services\AffiliateService;
use App\Support\AffiliateInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
    public function create(Request $request)
    {
        // Invited visitors (pre-vetted, arriving via the campaign link) get the fast-track experience;
        // an expired link degrades to a gentle "invite closed" notice; everyone else is organic.
        $inviteState = AffiliateInvite::classify($request->query('invite'));

        return view('affiliate-apply.index', [
            'inviteState' => $inviteState,
            'inviteToken' => $inviteState === 'none' ? null : $request->query('invite'),
        ]);
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

        $inviteState = AffiliateInvite::classify($request->input('invite'));
        $emailTaken  = User::where('email', $data['email'])->exists(); // any role — can't provision over it

        // INVITED + window open + a clean new email → these were pre-vetted, so skip the review queue
        // and provision the account immediately (same path the admin approve button uses).
        if ($inviteState === 'valid' && ! $emailTaken) {
            try {
                $user = app(AffiliateService::class)->registerAffiliate([
                    'first_name'     => $data['first_name'],
                    'last_name'      => $data['last_name'],
                    'email'          => $data['email'],
                    'contact_number' => $data['phone'],
                    'country'        => $data['country'],
                    'county'         => $data['county'],
                    'town'           => $data['town'] ?? null,
                ]);

                AffiliateApplication::create($data + $consent + [
                    'status'            => AffiliateApplication::STATUS_APPROVED,
                    'converted_user_id' => $user->id,
                    'source'            => 'invite',
                ]);

                return redirect()->route('affiliate.apply')->with('provisioned', true);
            } catch (\Throwable $e) {
                // Never lose the applicant — fall through to a normal pending application.
                Log::error('Invited affiliate auto-provision failed: ' . $e->getMessage(), ['email' => $data['email']]);
            }
        }

        // Otherwise: normal pending application (organic, expired/rotated-out invite, email collision,
        // or a provision that errored). Idempotent — refresh an existing pending row rather than stack.
        $existing = AffiliateApplication::where('email', $data['email'])
            ->where('status', AffiliateApplication::STATUS_PENDING)->first();

        if ($existing) {
            $existing->update($data + $consent);
        } else {
            $application = AffiliateApplication::create(
                $data + $consent + [
                    'status' => AffiliateApplication::STATUS_PENDING,
                    'source' => $inviteState === 'expired' ? 'invite_expired' : null,
                ]
            );
            $this->notifyAdmins($application);
        }

        // 'closed' = an expired/rotated invite (gentle notice + assistance); 'applied' = organic review.
        return redirect()->route('affiliate.apply')->with($inviteState === 'expired' ? 'closed' : 'applied', true);
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
