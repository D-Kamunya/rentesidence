<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OwnerService;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use App\Http\Requests\OwnerRegisterRequest;
use App\Models\EmailTemplate;
use App\Models\Owner;
use App\Models\Package;
use App\Models\User;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class OwnerController extends Controller
{
    public $ownerService;
    public $affiliateService;
    public function __construct()
    {
        $this->ownerService = new OwnerService;
        $this->affiliateService = new AffiliateService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->ownerService->getAllData($request);
        } else {
            $data['pageTitle'] = __('Owners');
            // Active affiliates for the "assign affiliate" picker on each owner row.
            $data['affiliates'] = \App\Models\Affiliate::with('user')
                ->where('status', AFFILIATE_STATUS_ACTIVE)
                ->get()
                ->map(fn ($a) => (object) [
                    'id'   => $a->id,
                    'name' => trim(($a->user->first_name ?? '') . ' ' . ($a->user->last_name ?? '')),
                    'email' => $a->user->email ?? '',
                ])
                ->filter(fn ($a) => $a->name !== '' || $a->email !== '')
                ->sortBy('name')
                ->values();
            return view('admin.owner.index', $data);
        }
    }

    /**
     * Owner detail: identity + uptake signals (properties / units / tenants / rent collected) +
     * a setup-progress checklist, so an admin can gauge adoption and spot owners who are stuck.
     * {owner} is the owner's USER id (what the list carries).
     */
    public function show($owner)
    {
        $user = User::where('id', $owner)->where('role', USER_ROLE_OWNER)->firstOrFail();
        $ownerRow = Owner::where('user_id', $user->id)->first();

        $propertyIds = \App\Models\Property::where('owner_user_id', $user->id)->pluck('id');
        $properties  = $propertyIds->count();
        $units       = \App\Models\PropertyUnit::whereIn('property_id', $propertyIds)->count();
        $tenants     = \App\Models\Tenant::where('owner_user_id', $user->id)->count();
        $hasRent     = \App\Models\Invoice::where('owner_user_id', $user->id)
            ->where('status', INVOICE_STATUS_PAID)->exists();

        // Who earns commission on this owner (if referred by an affiliate).
        $affiliateName = null;
        if ($ownerRow && $ownerRow->affiliate_id) {
            $aff = \App\Models\Affiliate::with('user')->find($ownerRow->affiliate_id);
            $affiliateName = $aff && $aff->user ? trim(($aff->user->first_name ?? '') . ' ' . ($aff->user->last_name ?? '')) : null;
        }

        // Last activity — the freshest signal of engagement.
        $lastActivity = collect([
            \App\Models\Property::where('owner_user_id', $user->id)->max('created_at'),
            \App\Models\Tenant::where('owner_user_id', $user->id)->max('created_at'),
            $user->updated_at,
        ])->filter()->map(fn ($d) => Carbon::parse($d))->max();

        $setup = ['property' => $properties > 0, 'tenant' => $tenants > 0, 'rent' => (bool) $hasRent];
        $stepsDone = collect($setup)->filter()->count();

        return view('admin.owner.show', [
            'pageTitle'     => __('Owner'),
            'user'          => $user,
            'ownerRow'      => $ownerRow,
            'properties'    => $properties,
            'units'         => $units,
            'tenants'       => $tenants,
            'hasRent'       => $hasRent,
            'affiliateName' => $affiliateName,
            'lastActivity'  => $lastActivity,
            'setup'         => $setup,
            'stepsDone'     => $stepsDone,
        ]);
    }

    /**
     * Assign / change / clear the affiliate attributed to an EXISTING owner. Admin-only money
     * surface: it decides who earns commission on this owner going forward. PROSPECTIVE ONLY —
     * the commission engine reads owners.affiliate_id live at each earning event, so changing it
     * affects FUTURE events only; nothing past is backfilled or reversed. Audited to the log.
     */
    public function assignAffiliate(Request $request, $id)
    {
        $request->validate([
            // nullable = clear the attribution; otherwise must be an existing affiliate.
            'affiliate_id' => ['nullable', 'integer', 'exists:affiliates,id'],
        ]);

        $owner = Owner::findOrFail($id);
        $newId = $request->filled('affiliate_id') ? (int) $request->affiliate_id : null;
        $oldId = $owner->affiliate_id ? (int) $owner->affiliate_id : null;

        if ($newId === $oldId) {
            return back()->with('info', __('No change — that affiliate is already attributed to this owner.'));
        }

        $owner->affiliate_id = $newId;
        $owner->save();

        \Illuminate\Support\Facades\Log::info('Owner affiliate attribution changed', [
            'admin_user_id'   => auth()->id(),
            'owner_id'        => $owner->id,
            'from_affiliate'  => $oldId,
            'to_affiliate'    => $newId,
            'at'              => now()->toDateTimeString(),
        ]);

        $msg = $newId === null
            ? __('Affiliate attribution cleared for this owner. They earn no affiliate commission going forward.')
            : __('Affiliate assigned. They earn commission on this owner\'s activity from now on (past periods are not backfilled).');

        return back()->with('success', $msg);
    }

    public function owner_register_form()
    {
        $data['pageTitle'] = __('Add Owner');
        $data['navOwnerAddMMShowClass'] = 'active';
        $data['affiliates'] = $this->affiliateService->getAllActive();
        return view('admin.owner.add', $data);
    }

    public function owner_register_store(OwnerRegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            // Shared owner-onboarding machinery: system temp password, forced reset on first
            // login, active + verified, trial package + plug-and-play defaults. The admin no
            // longer types a password (matches tenant + referral + trial-message creation).
            $onboard = app(\App\Services\OwnerOnboardingService::class)->create([
                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'phone'        => $request->contact_number,
                'email'        => $request->email,
                'affiliate_id' => $request->affiliate_id,
            ]);
            $user = $onboard['user'];
            $plainPassword = $onboard['password'];

            DB::commit();

            // Deliver the login credentials (email + SMS, forced reset on first login).
            \App\Jobs\SendLoginDetailsJob::dispatch($user, $plainPassword);

            // DEV ONLY: surface the temp password in a persistent panel so the flow can be tested
            // without live email/SMS (the toast flash disappears too fast to copy). Never in prod.
            if (config('app.debug')) {
                session()->flash('dev_credentials', app(\App\Services\OwnerOnboardingService::class)->devCredentials($user, $plainPassword));
            }
            return back()->with('success', __('OWNER REGISTERED SUCCESSFULLY'));
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
    public function activate($id)
    {
        $owner = Owner::findOrFail($id);
        $owner->status = 1;
        $owner->save();

        return redirect()->back()->with('success', __('Owner activated successfully.'));
    }

    public function deactivate($id)
    {
        $owner = Owner::findOrFail($id);
        $owner->status = 0;
        $owner->save();

        return redirect()->back()->with('success', __('Owner deactivated successfully.'));
    }

}
