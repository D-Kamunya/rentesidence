<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Public self-signup for the "Tenant Helper" — a tenant whose landlord is NOT (yet) on
 * Centresidence, or who is just house-hunting. This is the deliberate exception to the
 * no-self-register posture: OWNERS still cannot self-register (anti-phantom + vetting), but a
 * FREE tenant can, because a tenant account holds no financial power until a verified action
 * (rewards/payouts carry their own KYC) — and the free-tenant population is the growth funnel.
 *
 * The account is created OWNERLESS by reusing the existing "no active tenancy" machinery: a
 * Tenant row with status = CLOSE and users.owner_user_id = null. That flips
 * User::isOwnerlessTenant() to true with no predicate change, so the already-built standalone
 * ("Helper") dashboard, nav gating and route guards apply immediately.
 *
 * The signup captures the tenant's SITUATION and routes them straight to the value that fits:
 *   - landlord_off  → invite-a-landlord funnel (bring their landlord on board)
 *   - moving        → House Hunt (find a home)
 *
 * NOTE — deliberately NO "my landlord is on Centresidence" branch. Onboarding is owner-driven: an
 * owner adds their tenants directly (accounts + credentials issued then), and users.email /
 * users.contact_number are UNIQUE — so a tenant an owner already added cannot self-register a
 * duplicate anyway. That case is a sign-in / password-reset, not a signup; the form signposts it
 * (see the view) instead of a redundant lookup/claim module.
 */
class TenantSelfRegisterController extends Controller
{
    /** Guests only — an authenticated user has no business on the signup form. */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function form()
    {
        return view('auth.tenant_register', [
            'situation' => request('for'), // optional deep-link preselect (e.g. ?for=moving)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:60',
            'last_name'      => 'required|string|max:60',
            'contact_number' => 'required|string|max:20|unique:users,contact_number',
            'email'          => 'required|email|max:120|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'situation'      => 'required|in:landlord_off,moving',
            'agree'          => 'accepted',
        ], [
            'agree.accepted'         => __('Please accept the terms to continue.'),
            'contact_number.unique'  => __('An account already exists with this phone number.'),
            'email.unique'           => __('An account already exists with this email address.'),
        ]);

        DB::beginTransaction();
        try {
            // The user — a tenant, OWNERLESS (no landlord). Active + verified on signup keeps the
            // growth funnel friction-free; the account carries no financial power until a KYC-gated
            // action, so email-verification gating is deferred (a fast-follow if abuse warrants it).
            $user = new User();
            $user->first_name       = $validated['first_name'];
            $user->last_name        = $validated['last_name'];
            $user->contact_number   = $validated['contact_number'];
            $user->email            = $validated['email'];
            $user->password         = Hash::make($validated['password']);
            $user->role             = USER_ROLE_TENANT;
            $user->status           = USER_STATUS_ACTIVE;
            $user->owner_user_id    = null; // never had a landlord — the Helper signal
            $user->email_verified_at = Carbon::now();
            $user->save();

            // A CLOSE tenancy row with no owner/property/unit = the standalone "Helper" state, reusing
            // the exact machinery a moved-out tenant lands in. job/family_member are NOT NULL columns.
            $tenant = new Tenant();
            $tenant->user_id       = $user->id;
            $tenant->job           = '';
            $tenant->family_member = 0;
            $tenant->status        = TENANT_STATUS_CLOSE;
            $tenant->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Tenant self-registration failed: ' . $e->getMessage());
            return back()->withInput()->with('error', __('Something went wrong creating your account. Please try again.'));
        }

        Auth::login($user);

        return $this->routeBySituation($validated['situation']);
    }

    /** Send the new Helper straight to the value that matches why they joined. */
    private function routeBySituation(string $situation)
    {
        switch ($situation) {
            case 'landlord_off':
                return redirect()->route('tenant.invite-landlord.index')
                    ->with('success', __('Welcome to Centresidence! Invite your landlord below — when they come on board, everything connects for you.'));
            case 'moving':
                return redirect()->route('house.hunt')
                    ->with('success', __('Welcome to Centresidence! Here are homes you can explore.'));
            default:
                return redirect()->route('tenant.dashboard')
                    ->with('success', __('Welcome to Centresidence! Your account is ready.'));
        }
    }
}
