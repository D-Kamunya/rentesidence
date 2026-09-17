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
            return view('admin.owner.index', $data);
        }
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
