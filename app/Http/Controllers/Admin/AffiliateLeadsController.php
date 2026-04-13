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
use App\Jobs\Mail\SendTrialExtendedMail;
use App\Jobs\Mail\SendTrialApprovedMail;
use App\Jobs\Mail\SendTrialRejectedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
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

        $leads = $query->whereNotNull('affiliate_id')
               ->latest()
               ->paginate(10)
               ->withQueryString();

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

        $completeness = $this->completenessScore($lead);
        return view('admin.affiliates.leads.show', compact('lead','completeness'));
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


            // ✅ Resolve affiliate via user_id (SAFE for current structure)
            $affiliate = Affiliate::where('user_id', $affiliateId)->with('user')->first();

            if (!$affiliate) {
                DB::rollBack();
                return back()->with('error', "Affiliate not found for user ID {$affiliateId}. Cannot create owner.");
            }

            if (!$affiliate || !$affiliate->user) {
                DB::rollBack();
                return back()->with('error', 'Affiliate or affiliate user is missing.');
            }

            // ---------------------------------------------------------------
            // EXISTING USER FLOW (trial extension)
            // ---------------------------------------------------------------

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
                    'description' => 'Trial extended by admin. Client given additional trial time.'
                ]);

                DB::commit();

                // email — existing user (trial extension)
                SendTrialExtendedMail::dispatch(
                    $lead->id,
                    $company->email,
                    $trialEndsAt,
                    $affiliate->user->email,
                    $affiliate->user->first_name,
                );
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
            $trialDuration = (int) getOption('trail_duration', 1);

            if ($defaultPackage) {
                setUserPackage($user->id, $defaultPackage, $trialDuration, 1);
            }
            
            $trialEndsAt   = now()->addDays($trialDuration)->format('M d, Y');

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
            $resetLink     = url('/password/reset/' . $passwordResetToken . '?email=' . urlencode($user->email));

            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($passwordResetToken),
                    'created_at' => now()
                ]
            );

            // 📧 Send email (Acount created and Trial approved only for new users)
            SendTrialApprovedMail::dispatch(
                $lead->id,
                $user->email,
                $resetLink,
                $trialEndsAt,
                $affiliate->user->email,
                $affiliate->user->first_name,
            );

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

        // Notify affiliate of rejection
        $affiliate = User::find($lead->affiliate_id);
        if ($affiliate) {
            SendTrialRejectedMail::dispatch(
                $lead->id,
                $affiliate->email,
                $affiliate->first_name,
                $request->rejection_reason,
            );
        }
        return back()->with('success', 'Trial account request successfully rejected.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────
 
    /**
     * Compute the completeness score (0–100) for a single lead.
     * Mirrors the blade-side calculation so both stay in sync.
     */
    private function completenessScore(Lead $lead): int
    {
        $fields = [
            $lead->company->company_name    ?? null,
            $lead->company->country         ?? null,
            $lead->company->city            ?? null,
            $lead->company->phone           ?? null,
            $lead->company->email           ?? null,
            $lead->company->website         ?? null,
            $lead->company->property_type   ?? null,
            $lead->company->estimated_units ?? null,
            $lead->contact_person_name      ?? null,
            $lead->contact_person_role      ?? null,
        ];
 
        $filled = count(array_filter($fields, fn($v) => !is_null($v) && $v !== ''));
        return (int) round(($filled / count($fields)) * 100);
    }
}
