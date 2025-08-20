<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OwnerService;
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
    public function __construct()
    {
        $this->ownerService = new OwnerService;
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
            $user->password = Hash::make($request->password);
            $user->status = USER_STATUS_UNVERIFIED;
            $user->role = USER_ROLE_OWNER;
            $user->verify_token = str_replace('-', '', Str::uuid()->toString());
            $user->save();

            $owner = new Owner();
            $owner->user_id = $user->id;
            $owner->save();

            $duration = (int) getOption('trail_duration', 1);

            $defaultPackage = Package::where(['is_trail' => ACTIVE])->first();
            if ($defaultPackage) {
                setUserPackage($user->id, $defaultPackage, $duration, 1);
            }

            setOwnerGateway($user->id);
            
            setOwnerInvoiceType($user->id);

            setOwnerDefaultMaintenanceIssue($user->id);

            DB::commit();
            if (getOption('send_email_status', 0) == ACTIVE) {
                $emails = [$user->email];
                $subject = getOption('app_name') . ' ' . __('welcome you');
                $message = __('You have successfully been registered');
                $ownerUserId = $user->id;

                $mailService = new MailService;
                $mailService->sendWelcomeMail($emails, $subject, $message, $ownerUserId);

                if (getOption('email_verification_status', 0) == ACTIVE) {
                    $subject = __('Account Verification') . ' ' . getOption('app_name');
                    $message = __('Thank you for create new account. Please verify your account');
                    $template = EmailTemplate::where('owner_user_id', $ownerUserId)->where('category', EMAIL_TEMPLATE_EMAIL_VERIFY)->where('status', ACTIVE)->first();
                    if ($template) {
                        $customizedFieldsArray = [
                            '{{user_name}}' => $user->name,
                            '{{verify_link}}' => route('user.email.verified', $user->verify_token),
                            '{{otp}}' => $user->otp,
                            '{{app_name}}' => getOption('app_name'),
                        ];
                        $content = getEmailTemplate($template->body, $customizedFieldsArray);
                        $mailService->sendCustomizeMail($emails, $template->subject, $content);
                    } else {
                        $mailService->sendUserEmailVerificationMail($emails, $subject, $message, $user, $ownerUserId);
                    }
                    return redirect()->route('user.email.verify', $user->verify_token);
                } else {
                    $user->status = USER_STATUS_ACTIVE;
                    $user->email_verified_at = Carbon::now()->format("Y-m-d H:i:s");
                    $user->save();
                }
            } else {
                $user->status = USER_STATUS_ACTIVE;
                $user->email_verified_at = Carbon::now()->format("Y-m-d H:i:s");
                $user->save();
            }
            $message = __("OWNER REGISTERED SUCCESSFULLY");
            return back()->with('success', $message);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
