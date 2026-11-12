<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerSelfRegisterRequest;
use App\Models\EmailTemplate;
use App\Models\Owner;
use App\Models\Package;
use App\Models\User;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OwnerAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Public landlord self-signup (owner_register_form / owner_register_store) is open to
        // guests — it's the ad-landing "Start free" path, guarded by the dedicated
        // OwnerSelfRegisterRequest (unique + password + honeypot + optional reCAPTCHA) and a
        // route throttle. Any other action on this controller stays admin-only.
        $this->middleware(function ($request, $next) {
            if (auth()->guest() || auth()->user()->role !== USER_ROLE_ADMIN) {
                return redirect()->route('login');
            }
            return $next($request);
        })->except(['logout', 'owner_register_form', 'owner_register_store']);
    }

    public function owner_register_form()
    {
        return view('auth.owner_register_form');
    }

    public function owner_register_store(OwnerSelfRegisterRequest $request)
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

            // Free-first: an owner who self-registers lands on the permanent FREE plan (50-year
            // duration = open-ended), NOT a trial. Trials are granted deliberately, not on signup.
            $freePackage = defaultFreePackage();
            if ($freePackage) {
                setUserPackage($user->id, $freePackage, 365 * 50, 1);
                grantFreeWelcomeSms($user->id); // one-time SMS taste (Free has no recurring grant)
            }

            setOwnerGateway($user->id);
            
            setOwnerInvoiceType($user->id);

            setOwnerDefaultMaintenanceIssue($user->id);

            setOwnerDefaultTicketTopics($user->id);

            setOwnerDefaultDocumentConfig($user->id);

            DB::commit();

            // Meta Pixel: flag this request's next page (email-verify, or the dashboard on
            // instant activation) to fire a CompleteRegistration conversion event — so Facebook
            // can optimise ads toward real landlord signups, not just page views. Fail-safe.
            session()->flash('fb_complete_registration', true);

            // In-app heads-up for admins (no email) — so you see every landlord who comes through
            // the door and can reach out to help them activate. Fail-safe, real system sender.
            try {
                $adminIds = User::where('role', USER_ROLE_ADMIN)->pluck('id');
                $systemSender = $adminIds->first();
                if ($systemSender) {
                    $who = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
                    $url = \Illuminate\Support\Facades\Route::has('admin.owner.index') ? route('admin.owner.index') : url('/admin');
                    foreach ($adminIds as $adminId) {
                        addNotification(
                            __('New landlord signup'),
                            __(':who just created a free account from the website.', ['who' => $who]),
                            $url, null, (int) $adminId, (int) $systemSender
                        );
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('New owner signup admin notify failed: ' . $e->getMessage());
            }

            // In-app welcome for the owner (points to their first step + Support). Idempotent + fail-safe.
            \App\Services\OwnerWelcomeService::send($user);

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
            $credentials = ['email' => $request->email, 'password' => $request->password];
            if (Auth::attempt($credentials)) {
                return redirect()->back()->with('success', __(CREATED_SUCCESSFULLY));
            } else {
                return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
            }
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
