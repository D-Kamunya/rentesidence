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

                $mailService->sendUserEmailVerificationMail($emails, $subject, $message, $user, $affiliateUserId);
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
            ->select('users.*', 'affiliates.referral_code')
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
                if ($affiliate->status == ACTIVE) {
                    return '<div class="status-btn status-btn-green font-13 radius-4">Active</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Deactivate</div>';
                }
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

}
