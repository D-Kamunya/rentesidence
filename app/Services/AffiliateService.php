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


    /**
     * The SINGLE source of truth for affiliate creation — used by the admin add form AND the
     * one-click "approve application" path. Self-contained (owns its own transaction), so any
     * caller can use it safely: creates the user + affiliate with a system-generated temporary
     * password, then (after commit) delivers the credentials by email + SMS. The affiliate must
     * set their own password on first login (must_change_password → ForcePasswordChange).
     * Returns the created User.
     */
    public function registerAffiliate($data): User
    {
        $plainPassword = Str::random(10);

        DB::beginTransaction();
        try {
            $user = new User();
            $user->first_name = $data['first_name'];
            $user->last_name =  $data['last_name'];
            $user->contact_number =  $data['contact_number'];
            $user->email =  $data['email'];
            $user->password = Hash::make($plainPassword);
            $user->status = USER_STATUS_UNVERIFIED;
            $user->role = USER_ROLE_AFFILIATE;
            $user->must_change_password = 1;
            $user->verify_token = str_replace('-', '', Str::uuid()->toString());
            $user->save();

            $affiliate = new Affiliate();
            $affiliate->user_id = $user->id;
            $affiliate->referral_code = strtoupper(Str::random(12));
            $affiliate->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // After the commit: welcome/verification mail + credential delivery (email + SMS).
        $this->handlePostRegistration($user);
        sendLoginDetails($user, $plainPassword);

        return $user;
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
                // Archive (soft-delete) — for clearing out stale/test records. The controller
                // blocks it on any unpaid balance or in-flight withdrawal, so funds are never
                // orphaned; history is preserved by the soft delete. Shown in either state.
                $archiveBtn = '
                    <form action="' . route('admin.affiliates.delete', $affiliate->affiliate_id) . '" method="POST" style="display:inline;"
                        data-cs-confirm="' . __('Archive this affiliate? Use this to clear out old or test records. It is blocked if they have any unpaid balance or a withdrawal in progress. Their commission history is kept for audit.') . '"
                        data-cs-confirm-title="' . __('Archive affiliate?') . '"
                        data-cs-confirm-ok="' . __('Yes, archive') . '"
                        data-cs-confirm-tone="danger">
                        ' . csrf_field() . '
                        <button type="submit" class="btn" style="display:inline-flex; align-items:center; gap:5px; background:#F4F5F7; border:0.5px solid #D9DEE6; color:#4B5563; border-radius:99px; font-size:11px; font-weight:500; padding:4px 11px; white-space:nowrap; cursor:pointer; line-height:1.4;">
                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M3 5h10M6 5V3.5h4V5M5 5l.4 8h5.2l.4-8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Archive
                        </button>
                    </form>';

                // Edit contact details (name/email/phone — never the password). Shown in both
                // states so an admin can fix a demo/typo email or changed number for any affiliate.
                $editBtn = '
                    <a href="' . route('admin.affiliates.edit', $affiliate->affiliate_id) . '" class="btn"
                        title="' . __('Edit contact details') . '"
                        style="display:inline-flex; align-items:center; gap:5px; background:#EEF4FB; border:0.5px solid #BCD5EE; color:#185FA5; border-radius:99px; font-size:11px; font-weight:500; padding:4px 11px; white-space:nowrap; cursor:pointer; line-height:1.4; text-decoration:none;">
                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M11.5 2.5l2 2L6 12l-2.5.5L4 10l7.5-7.5z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Edit
                    </a>';

                // Earnings — admin sees this affiliate's commission/balance for ANY affiliate (not
                // only those who have withdrawn).
                $earningsBtn = '
                    <a href="' . route('admin.affiliates.earnings', $affiliate->affiliate_id) . '" class="btn"
                        title="' . __('View earnings') . '"
                        style="display:inline-flex; align-items:center; gap:5px; background:#E6F6EE; border:0.5px solid #9FE1CB; color:#0F6E56; border-radius:99px; font-size:11px; font-weight:500; padding:4px 11px; white-space:nowrap; cursor:pointer; line-height:1.4; text-decoration:none;">
                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M2 13h12M4 13V8M8 13V4M12 13V6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                        Earnings
                    </a>';

                if ($affiliate->affiliate_status == AFFILIATE_STATUS_ACTIVE) {
                    return '
                        <div style="display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span class="status-btn status-btn-green font-13 radius-4">Active</span>' . $editBtn . $earningsBtn . '
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
                            </form>' . $archiveBtn . '
                        </div>';
                }

                return '
                    <div style="display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span class="status-btn status-btn-orange font-13 radius-4">Suspended</span>' . $editBtn . $earningsBtn . '
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
                        </form>' . $archiveBtn . '
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
