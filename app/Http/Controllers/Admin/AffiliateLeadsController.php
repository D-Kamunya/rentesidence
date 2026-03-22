<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AffiliateService;
use App\Services\SmsMail\MailService;
use App\Services\OwnerService;
use Illuminate\Http\Request;
use App\Models\Affiliate;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\EmailTemplate;
use App\Models\Owner;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\OwnerRegisterRequest;
use Carbon\Carbon;
use Exception;


class AffiliateLeadsController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['company', 'affiliate']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('company', function($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%");
                })
                ->orWhereHas('affiliate', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Temperature Filter
        if ($request->filled('temperature')) {
            $query->where('temperature', $request->temperature);
        }

        $leads = $query->latest()->paginate(10)->withQueryString();

        // Summary Stats
        $pendingCount = Lead::where('status', 'pending_conversion')->count();
        $trialCount = Lead::where('status', 'trial')->count();
        $convertedCount = Lead::where('status', 'converted')->count();
        $totalLeads = Lead::count();
        $conversionRate = $totalLeads > 0 ? round(($convertedCount / $totalLeads) * 100, 1) : 0;

        return view('admin.affiliates.leads.index', compact(
            'leads',
            'pendingCount',
            'trialCount',
            'convertedCount',
            'conversionRate'
        ));
    }

    public function show(Lead $lead)
    {
        $lead->load([
            'company',
            'affiliate',
            'activities' => function ($q) {
                $q->latest();
            }
        ]);

        return view('admin.affiliates.leads.show', compact('lead'));
    }

    public function approveTrial(Request $request, $leadId)
    {
        DB::beginTransaction();

        try {
            $lead = Lead::with('company')->findOrFail($leadId);

            // Ensure correct status
            if ($lead->status !== 'pending_conversion') {
                return back()->with('error', 'This lead is not pending conversion.');
            }

            $company = $lead->company;
            $affiliateId = $lead->affiliate_id;

            // Split company name safely
            $nameParts = explode(' ', $company->company_name, 2);
            $firstName = $nameParts[0] ?? 'Client';
            $lastName = $nameParts[1] ?? 'Account';


            // 🔍 Check if user already exists (for trial renewals/extensions)
            $existingUser = User::where('email', $company->email)->first();
            $isExtension = false;


                // ✅ Resolve affiliate via user_id (SAFE for your current structure)
                $affiliate = Affiliate::where('user_id', $affiliateId)->first();

                if (!$affiliate) {
                    DB::rollBack();
                    return back()->with('error', "Affiliate not found for user ID {$affiliateId}. Cannot create owner.");
                }

                if (!$affiliate || !$affiliate->user) {
                    DB::rollBack();
                    return back()->with('error', 'Affiliate or affiliate user is missing.');
                }

            if ($existingUser) {
                // User exists - this might be a trial extension
                $owner = Owner::where('user_id', $existingUser->id)->first();


                if (!$owner) {
                    // User exists but no owner record - create one
                    $owner = new Owner();
                    $owner->user_id = $existingUser->id;
                    $owner->affiliate_id = $affiliate->id;
                    $owner->save();
                }

                // 🔍 Check if they have/had a trial package
                $trialPackage = DB::table('owner_packages')
                    ->join('packages', 'owner_packages.package_id', '=', 'packages.id')
                    ->where('owner_packages.user_id', $owner->user_id)
                    ->where('packages.is_trail', ACTIVE)
                    ->orderByDesc('owner_packages.end_date')
                    ->select('owner_packages.*')
                    ->first();

                if ($trialPackage) {
                    $trialEndsAt = Carbon::parse($trialPackage->end_date);

                    // 🟡 Active trial → block (can't renew active trial)
                    if ($trialEndsAt->isFuture()) {
                        return back()->with('error', 'This user already has an active trial that expires on ' . $trialEndsAt->format('M d, Y') . '. Cannot approve another trial.');
                    }

                    // 🔁 Expired trial → require confirmation
                    if (!$request->has('confirm_renewal')) {
                        $expiredDate = $trialEndsAt->format('M d, Y');
                        return back()
                            ->with('warning_message', 'This user\'s trial expired on ' . $expiredDate . '. Do you want to extend their trial?')
                            ->with('warning_data', [
                                'show_confirm' => true,
                                'lead_id' => $lead->id
                            ]);
                    }

                    // Confirmed renewal
                    $isExtension = true;
                }

                // ✅ Renew/Extend trial for existing user
                $defaultPackage = Package::where(['is_trail' => ACTIVE])->first();

                if ($defaultPackage) {
                    setUserPackage($existingUser->id, $defaultPackage, (int) getOption('trail_duration', 1), 1);
                }

                // Update lead status
                $lead->update([
                    'status' => 'trial',
                    'owner_id' => $owner->id,
                    'last_activity_at' => now(),
                ]);

                // Update company status
                $company->update([
                    'sales_status' => 'client',
                ]);

                // Log activity
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => auth()->id(),
                    'type' => 'trial_started',
                    'description' => 'Trial extended by admin. Customer given additional trial time.'
                ]);

                DB::commit();

                return back()->with('success', 'Trial extended successfully for ' . $company->company_name . '!');
            }

            // 🆕 CREATE NEW USER FLOW (no existing user found)

            $user = new User();
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->contact_number = $company->phone;
            $user->email = $company->email;
            $user->password = Hash::make(Str::random(32));
            $user->status = USER_STATUS_ACTIVE;
            $user->email_verified_at = Carbon::now()->format("Y-m-d H:i:s");
            $user->role = USER_ROLE_OWNER;
            $user->verify_token = str_replace('-', '', Str::uuid()->toString());
            $user->save();

            // Create owner record
            $owner = new Owner();
            $owner->user_id = $user->id;
            $owner->affiliate_id = $affiliate->id;
            $owner->save();

            // 🎯 Assign trial package
            $defaultPackage = Package::where(['is_trail' => ACTIVE])->first();

            if ($defaultPackage) {
                setUserPackage($user->id, $defaultPackage, (int) getOption('trail_duration', 1), 1);
            }

            // Setup defaults (only for new users)
            setOwnerGateway($user->id);
            setOwnerInvoiceType($user->id);
            setOwnerDefaultMaintenanceIssue($user->id);

            // Update lead status
            $lead->update([
                'status' => 'trial',
                'owner_id' => $owner->id,
                'last_activity_at' => now(),
            ]);

            // Update company status
            $company->update([
                'sales_status' => 'client',
            ]);

            // Log activity
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'trial_started',
                'description' => 'Trial approved by admin. Owner account created successfully.'
            ]);

            DB::commit();

            // 🔐 Password reset setup (only for new users)
            $passwordResetToken = Str::random(64);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($passwordResetToken),
                    'created_at' => now()
                ]
            );

            // 📧 Send email (only for new users)
            if (getOption('send_email_status', 0) == ACTIVE) {

                $resetLink = url('/password/reset/' . $passwordResetToken . '?email=' . urlencode($user->email));

                $mailService = new MailService;

                $subject = 'Welcome to ' . getOption('app_name') . ' - Set Your Password';

                $message = "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <h2 style='color:#185FA5;'>Welcome to " . getOption('app_name') . "!</h2>
                        <p>Hello <strong>{$company->company_name}</strong>,</p>
                        <p>Great news! Your trial account has been approved and is ready to use.</p>
                        <p>To get started, please set your password by clicking the button below:</p>
                        <div style='text-align:center;margin:30px 0;'>
                            <a href='{$resetLink}' style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:500;'>Set My Password</a>
                        </div>
                        <p><strong>Your login email:</strong> {$user->email}</p>
                        <p style='color:#6b7280;font-size:13px;'>This link will expire in 60 minutes. If you didn't request this, please ignore this email.</p>
                        <hr style='border:none;border-top:1px solid #e5e7eb;margin:30px 0;'>
                        <p style='color:#9ca3af;font-size:12px;'>If the button doesn't work, copy and paste this link into your browser:<br>{$resetLink}</p>
                    </div>
                ";

                $mailService->sendCustomizeMail([$user->email], $subject, $message);
            }

            return back()->with('success', 'Lead converted successfully! Owner account created for ' . $company->company_name . ' and setup email sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectTrial(Request $request, Lead $lead)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $lead->update([
            'status'           => 'demo_completed', // back to last valid stage
            'rejection_reason' => $request->rejection_reason,
            'last_activity_at' => now(),
        ]);

        LeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => auth()->id(),
            'type'        => 'conversion_rejected',
            'description' => 'Rejected - ' . $request->rejection_reason,
        ]);

        return back()->with('success', 'Trial account request successfully rejected.');
    }
}
