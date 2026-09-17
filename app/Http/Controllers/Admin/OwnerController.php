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
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->contact_number = $request->contact_number;
            $user->email = $request->email;
            // New onboarding lifecycle (matches tenant + referral creation): a system-generated
            // temporary password delivered by email + SMS, and a forced reset on first login —
            // the admin no longer types a password. Account is active immediately.
            $plainPassword = Str::random(10);
            $user->password = Hash::make($plainPassword);
            $user->must_change_password = 1;
            $user->status = USER_STATUS_ACTIVE;
            $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
            $user->role = USER_ROLE_OWNER;
            $user->verify_token = str_replace('-', '', Str::uuid()->toString());
            $user->save();

            $owner = new Owner();
            $owner->user_id = $user->id;
            $owner->affiliate_id = $request->affiliate_id;
            $owner->save();

            $duration = (int) getOption('trail_duration', 1);

            $defaultPackage = Package::where(['is_trail' => ACTIVE])->first();
            if ($defaultPackage) {
                setUserPackage($user->id, $defaultPackage, $duration, 1);
            }

            setOwnerGateway($user->id);
            setOwnerInvoiceType($user->id);
            setOwnerDefaultMaintenanceIssue($user->id);
            setOwnerDefaultTicketTopics($user->id);
            setOwnerDefaultDocumentConfig($user->id);

            DB::commit();

            // Deliver the login credentials (email + SMS, forced reset on first login).
            \App\Jobs\SendLoginDetailsJob::dispatch($user, $plainPassword);

            // DEV ONLY: surface the temp password in a persistent panel so the flow can be tested
            // without live email/SMS (the toast flash disappears too fast to copy). Never in prod.
            if (config('app.debug')) {
                session()->flash('dev_credentials', [
                    'name'     => trim($user->first_name . ' ' . $user->last_name),
                    'email'    => $user->email,
                    'phone'    => $user->contact_number,
                    'password' => $plainPassword,
                ]);
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
