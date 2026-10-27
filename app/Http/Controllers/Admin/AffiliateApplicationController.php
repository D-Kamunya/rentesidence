<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\AffiliateService;
use App\Support\AffiliateInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Admin inbox for prospective-affiliate applications + one-click conversion into a full account. */
class AffiliateApplicationController extends Controller
{
    public function __construct(private AffiliateService $affiliates)
    {
    }

    public function index(Request $request)
    {
        $status = $request->input('status', AffiliateApplication::STATUS_PENDING);

        $applications = AffiliateApplication::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => AffiliateApplication::where('status', AffiliateApplication::STATUS_PENDING)->count(),
            'approved' => AffiliateApplication::where('status', AffiliateApplication::STATUS_APPROVED)->count(),
            'rejected' => AffiliateApplication::where('status', AffiliateApplication::STATUS_REJECTED)->count(),
        ];

        return view('admin.affiliates.applications.index', [
            'applications' => $applications,
            'counts'       => $counts,
            'status'       => $status,
            'pageTitle'    => __('Affiliate Applications'),
            'invite'       => [
                'link'      => AffiliateInvite::link(),
                'expiresAt' => AffiliateInvite::expiresAt(),
                'active'    => AffiliateInvite::isActive(),
                'default_days' => AffiliateInvite::DEFAULT_WINDOW_DAYS,
            ],
        ]);
    }

    /**
     * Rotate / close the invited-campaign link. Rotating generates a fresh token (invalidating every
     * old link) and reopens the window; closing sets the expiry to the past so invited links fall back
     * to normal review immediately. The window also auto-closes on its own — this is the manual control.
     */
    public function updateInvite(Request $request)
    {
        if ($request->input('action') === 'close') {
            AffiliateInvite::setExpiry(now()->subMinute());
            return back()->with('success', __('Invitation link closed — invited links now go to normal review.'));
        }

        $days = (int) $request->input('days', AffiliateInvite::DEFAULT_WINDOW_DAYS);
        AffiliateInvite::rotate($days > 0 ? $days : AffiliateInvite::DEFAULT_WINDOW_DAYS);

        return back()->with('success', __('New invitation link generated. Old links no longer auto-provision.'));
    }

    /**
     * One click: turn an approved application into a full affiliate account. Reuses the single
     * source of truth (AffiliateService::registerAffiliate) — temp password, email+SMS creds,
     * forced reset on first login — then marks the application converted.
     */
    public function approve(AffiliateApplication $application)
    {
        if ($application->status !== AffiliateApplication::STATUS_PENDING) {
            return back()->with('error', __('This application has already been handled.'));
        }
        // Recovery for a half-finished prior approval: before the delivery fix, a suppressed/invalid
        // email or an SMS-gateway failure could throw AFTER the account was committed but BEFORE the
        // application was linked — leaving a committed affiliate the admin then couldn't re-create
        // ("already exists"). If the existing user is an UNLINKED affiliate, finish the job (re-issue
        // credentials + link) instead of hard-blocking. Anything else is a genuine clash.
        // Resolve the applicant against existing accounts by EMAIL *or* PHONE, INCLUDING soft-deleted
        // rows: both users_email_unique and users_contact_number_unique count trashed rows, so a
        // soft-deleted V1 affiliate reserves BOTH — and a returning affiliate may re-apply with a new
        // email but the same phone (or vice-versa). A plain where()->first() misses trashed rows
        // (global soft-delete scope) and the insert then collides (1062). Restore/reuse, don't re-insert.
        $existing = User::withTrashed()
            ->where(function ($q) use ($application) {
                $q->where('email', $application->email);
                if (filled($application->phone)) {
                    $q->orWhere('contact_number', $application->phone);
                }
            })
            ->first();
        if ($existing) {
            $isAffiliate   = (int) $existing->role === USER_ROLE_AFFILIATE;
            $alreadyLinked = AffiliateApplication::where('converted_user_id', $existing->id)->exists();

            // A non-affiliate account (live or deleted) owns this email/phone — a genuine cross-role
            // collision the admin must resolve, never something to silently repurpose.
            if (! $isAffiliate) {
                return back()->with('error', $existing->trashed()
                    ? __('This email or phone belonged to a previously-deleted account and still reserves it. Use different details, or permanently remove that account first.')
                    : __('An account already exists with this email or phone number.'));
            }
            // A live affiliate already linked to an application = a duplicate application, nothing to do.
            if ($alreadyLinked && ! $existing->trashed()) {
                return back()->with('error', __('An affiliate account already exists for this email/phone.'));
            }

            // Otherwise: a soft-deleted affiliate (e.g. a returning V1 affiliate), or a live unlinked
            // affiliate from a half-finished prior approval → restore (if needed), refresh to the
            // applicant's CURRENT details, re-issue credentials, ensure an affiliate record, and link.
            $plainPassword = Str::random(10);
            DB::beginTransaction();
            try {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                // Bring the account up to the applicant's current details — a returning affiliate may
                // re-apply with a new email/phone. Only adopt a field if no OTHER account holds it.
                if (filled($application->email)
                    && ! User::withTrashed()->where('email', $application->email)->where('id', '!=', $existing->id)->exists()) {
                    $existing->email = $application->email;
                }
                if (filled($application->phone)
                    && ! User::withTrashed()->where('contact_number', $application->phone)->where('id', '!=', $existing->id)->exists()) {
                    $existing->contact_number = $application->phone;
                }
                if (filled($application->first_name)) {
                    $existing->first_name = $application->first_name;
                }
                if (filled($application->last_name)) {
                    $existing->last_name = $application->last_name;
                }

                $existing->password = Hash::make($plainPassword);
                $existing->must_change_password = 1;
                $existing->status = USER_STATUS_UNVERIFIED;
                $existing->save();

                if (! Affiliate::where('user_id', $existing->id)->exists()) {
                    $affiliate = new Affiliate();
                    $affiliate->user_id = $existing->id;
                    $affiliate->referral_code = strtoupper(Str::random(12));
                    $affiliate->country = $application->country;
                    $affiliate->county  = $application->county;
                    $affiliate->town    = $application->town;
                    $affiliate->save();
                }

                $application->update([
                    'status'            => AffiliateApplication::STATUS_APPROVED,
                    'converted_user_id' => $existing->id,
                ]);
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Affiliate recovery failed: ' . $e->getMessage(), ['application_id' => $application->id]);
                return back()->with('error', __('Could not restore the existing affiliate account: ') . $e->getMessage());
            }

            try {
                sendLoginDetails($existing, $plainPassword);
            } catch (\Throwable $e) {
                Log::warning('Affiliate recovery credential delivery failed: ' . $e->getMessage(), ['user_id' => $existing->id]);
            }

            if (config('app.debug')) {
                session()->flash('dev_credentials', [
                    'name'  => $application->name,
                    'email' => $application->email,
                    'note'  => __('Existing affiliate account restored + linked — fresh credentials re-sent.'),
                ]);
            }

            return back()->with('success', __('This email already had an affiliate account (from a previous attempt) — restored and linked it to this application, and re-sent login details.'));
        }

        try {
            $user = $this->affiliates->registerAffiliate([
                'first_name'     => $application->first_name,
                'last_name'      => $application->last_name,
                'email'          => $application->email,
                'contact_number' => $application->phone,
                'country'        => $application->country,
                'county'         => $application->county,
                'town'           => $application->town,
            ]);

            $application->update([
                'status'            => AffiliateApplication::STATUS_APPROVED,
                'converted_user_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Affiliate application approval failed: ' . $e->getMessage(), ['application_id' => $application->id]);
            // Surface the actual reason to the admin (a trusted, internal screen) so a real failure
            // is diagnosable instead of a dead-end "try again". Credential-delivery problems no
            // longer land here (they're caught in registerAffiliate) — so anything reaching this is
            // a genuine account-creation error worth seeing.
            return back()->with('error', __('Could not create the affiliate account: ') . $e->getMessage());
        }

        // DEV ONLY: surface the temp password locally (no live email/SMS in dev).
        if (config('app.debug')) {
            session()->flash('dev_credentials', [
                'name'  => $application->name,
                'email' => $application->email,
                'note'  => __('Affiliate account created — credentials sent by email + SMS.'),
            ]);
        }

        return back()->with('success', __('Affiliate account created for :name — login details sent by email and SMS.', ['name' => $application->name]));
    }

    public function reject(Request $request, AffiliateApplication $application)
    {
        if ($application->status !== AffiliateApplication::STATUS_PENDING) {
            return back()->with('error', __('This application has already been handled.'));
        }

        $application->update([
            'status'     => AffiliateApplication::STATUS_REJECTED,
            'admin_note' => $request->input('reason'),
        ]);

        // Polite applicant-facing "thanks, not this time" note (never exposes the internal admin note).
        if ($application->email) {
            \App\Jobs\Mail\SendAffiliateApplicationDeclinedMail::dispatch($application->email, (string) $application->first_name);
        }

        return back()->with('success', __('Application declined — the applicant has been emailed a polite notice.'));
    }

    /**
     * Remove a stale/duplicate application (e.g. someone who applied organically AND later signed up
     * through the invite link appears under both Pending and Approved). Only non-approved rows can be
     * deleted — an approved one is the audit record of a created account. Soft-delete keeps the trail.
     */
    public function destroy(AffiliateApplication $application)
    {
        if ($application->status === AffiliateApplication::STATUS_APPROVED) {
            return back()->with('error', __('An approved application can\'t be deleted — it\'s the record of a created account. Manage the account under Affiliates instead.'));
        }

        $application->delete();

        return back()->with('success', __('Application removed.'));
    }
}
