<?php

namespace App\Services;

use App\Models\User;
use App\Models\Affiliate;
use App\Services\SmsMail\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AffiliateService
{


    public function registerAffiliate($data)
    {
        // 1️⃣ Generate random password
        $plainPassword = Str::random(10); // e.g., 10 characters long
        $user = new User();
        $user->first_name = $data['first_name'];
        $user->last_name =  $data['last_name'];
        $user->contact_number =  $data['contact_number'];
        $user->email =  $data['email'];
        $user->password = Hash::make($plainPassword);
        $user->status = USER_STATUS_UNVERIFIED;
        $user->role = USER_ROLE_AFFILIATE;
        // System-generated password → force the affiliate to set their own on first
        // login (same onboarding rule as owner-created tenants). Cleared in
        // ProfileController::changePasswordUpdate; enforced by ForcePasswordChange.
        $user->must_change_password = 1;
        $user->verify_token = str_replace('-', '', Str::uuid()->toString());
        $user->save();

        $affiliate = new Affiliate();
        $referralCode = strtoupper(Str::random(12));
        $affiliate->user_id = $user->id;
        $affiliate->referral_code = $referralCode;
        $affiliate->save();

        DB::commit();

        $this->handlePostRegistration($user);
        sendLoginDetails($user, $plainPassword);
    }

    protected function handlePostRegistration(User $user)
    {
        if (getOption('send_email_status', 0) == ACTIVE) {
            $emails = [$user->email];
            $mailService = new MailService;

            // Welcome email
            $mailService->sendWelcomeMail($emails, getOption('app_name') . ' ' . __('welcomes you'), __('You have successfully been registered'), $user->id);

            // Email verification
            if (getOption('email_verification_status', 0) == ACTIVE) {
                $subject = __('Account Verification') . ' ' . getOption('app_name');
                $message = __('Thank you for create new account. Please verify your account');

                // BUGFIX: Replaced undefined variable $affiliateUserId with $user->id.
                // The previous variable caused a Fatal Error (Undefined Variable) in environments 
                // with strict error reporting or PHP 8+, preventing the registration flow 
                // from completing when email verification is enabled.
                $mailService->sendUserEmailVerificationMail($emails, $subject, $message, $user, $user->id);
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
    }

    public function getAllData($request)
    {
        $affiliates = Affiliate::query()
            ->join('users', 'affiliates.user_id', '=', 'users.id')
            ->select('users.*', 'affiliates.referral_code', 'affiliates.status as affiliate_status', 'affiliates.id as affiliate_id')
            ->orderBy('affiliates.id', 'desc');

        return datatables($affiliates)
            ->addIndexColumn()
            ->addColumn('name', function ($affiliate) {
                return $affiliate->first_name . ' ' . $affiliate->last_name;
            })
            ->addColumn('email', function ($affiliate) {
                return $affiliate->email;
            })
            ->addColumn('contact_number', function ($affiliate) {
                return $affiliate->contact_number;
            })
            ->addColumn('referral_code', function ($affiliate) {
                return $affiliate->referral_code;
            })
            ->addColumn('status', function ($affiliate) {
                if ($affiliate->affiliate_status == AFFILIATE_STATUS_ACTIVE) {
                    return '
                        <div style="display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span class="status-btn status-btn-green font-13 radius-4">Active</span>
                            <form action="' . route('admin.affiliates.suspend', $affiliate->affiliate_id) . '" method="POST" style="display:inline;"
                                data-cs-confirm="' . __('Suspend this affiliate? They immediately lose access to their account until you reinstate them. Earned commissions, referrals and history are kept.') . '"
                                data-cs-confirm-title="' . __('Suspend affiliate?') . '"
                                data-cs-confirm-ok="' . __('Yes, suspend') . '"
                                data-cs-confirm-tone="danger">
                                ' . csrf_field() . '
                                <button type="submit" class="btn deactivate"
                                    style="display: inline-flex; align-items: center; gap: 5px;
                                        background: #FDF4F1; border: 0.5px solid #F5C4B3;
                                        color: #712B13; border-radius: 99px;
                                        font-size: 11px; font-weight: 500;
                                        padding: 4px 11px; white-space: nowrap;
                                        cursor: pointer; line-height: 1.4;">
                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Suspend
                                </button>
                            </form>
                        </div>';
                }

                return '
                    <div style="display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span class="status-btn status-btn-orange font-13 radius-4">Suspended</span>
                        <form action="' . route('admin.affiliates.reinstate', $affiliate->affiliate_id) . '" method="POST" style="display:inline;"
                            data-cs-confirm="' . __('Reinstate this affiliate and restore their access to the platform?') . '"
                            data-cs-confirm-title="' . __('Reinstate affiliate?') . '"
                            data-cs-confirm-ok="' . __('Yes, reinstate') . '">
                            ' . csrf_field() . '
                            <button type="submit" class="btn activate"
                                style="display: inline-flex; align-items: center; gap: 5px;
                                    background: #F0F9F4; border: 0.5px solid #9FE1CB;
                                    color: #085041; border-radius: 99px;
                                    font-size: 11px; font-weight: 500;
                                    padding: 4px 11px; white-space: nowrap;
                                    cursor: pointer; line-height: 1.4;">
                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                    <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Reinstate
                            </button>
                        </form>
                    </div>';
            })
            ->rawColumns(['name', 'status', 'trail', 'action'])
            ->make(true);
    }

    public function getAll()
    {
        $affiliates = Affiliate::query()
            ->join('users', 'affiliates.user_id', '=', 'users.id')
            ->select('users.*')
            ->orderBy('affiliates.id', 'desc')
            ->get();
        return $affiliates->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function getAllActive()
    {
        return Affiliate::query()
        ->leftJoin('users', 'affiliates.user_id', '=', 'users.id')
        ->where('affiliates.status', AFFILIATE_STATUS_ACTIVE)
        ->orderBy('users.first_name', 'asc')
        ->select('affiliates.*', 'users.first_name', 'users.last_name', 'users.email')
        ->get();
    }

}
