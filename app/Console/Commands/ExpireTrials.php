<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Services\MailService;
use Carbon\Carbon;
use DB;

class ExpireTrials extends Command
{
    protected $signature = 'trials:expire';
    protected $description = 'Check for expired trials and revert leads to pending_conversion status';

    public function handle()
    {
        // Find all leads currently in 'trial' status
        $trialLeads = Lead::where('status', 'trial')
            ->with(['company', 'affiliate', 'owner'])
            ->get();
            
        $expiredCount = 0;

        foreach ($trialLeads as $lead) {
            // Get the owner associated with this lead
            $owner = $lead->owner;
            
            if (!$owner || !$owner->id) {
                continue;
            }

            // Check if trial package has expired
            $trialPackage = DB::table('owner_packages')
                ->join('packages', 'owner_packages.package_id', '=', 'packages.id')
                ->where('owner_packages.owner_id', $owner->id)
                ->where('packages.is_trail', ACTIVE)
                ->orderByDesc('owner_packages.end_date')
                ->select('owner_packages.*')
                ->first();

            if (!$trialPackage) {
                continue;
            }

            $trialEndsAt = Carbon::parse($trialPackage->end_date);

            // If trial has expired
            if ($trialEndsAt->isPast()) {
                
                DB::beginTransaction();
                
                try {
                    // Revert lead to pending_conversion
                    $lead->update([
                        'status' => 'pending_conversion',
                        'last_activity_at' => now(),
                    ]);

                    // Log activity
                    LeadActivity::create([
                        'lead_id' => $lead->id,
                        'user_id' => null, // System action
                        'type' => 'trial_expired',
                        'description' => 'Trial period has ended. Lead reverted to pending conversion for possible renewal.'
                    ]);

                    DB::commit();

                    // Send notification email to affiliate
                    $this->notifyAffiliateTrialExpired($lead);

                    $expiredCount++;
                    $this->info("Trial expired for lead #{$lead->id} - {$lead->company->company_name}");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Failed to expire trial for lead #{$lead->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("Processed {$expiredCount} expired trials.");
        return 0;
    }

    private function notifyAffiliateTrialExpired($lead)
    {
        if (getOption('send_email_status', 0) != ACTIVE) {
            return;
        }

        $affiliate = $lead->affiliate;
        $company = $lead->company;

        $mailService = new MailService;
        
        $subject = 'Trial Expired - ' . $company->company_name . ' | ' . getOption('app_name');
        
        $message = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <h2 style='color:#854F0B;'>⏰ Trial Period Has Ended</h2>
                
                <p>Hello <strong>{$affiliate->first_name}</strong>,</p>
                
                <p>The trial period for <strong>{$company->company_name}</strong> has ended.</p>
                
                <div style='background:#FEF9EE;border:1px solid #FAC775;border-radius:8px;padding:16px;margin:20px 0;'>
                    <p style='margin:0 0 8px;font-weight:600;color:#854F0B;'>📋 Lead Details:</p>
                    <p style='margin:4px 0;'><strong>Company:</strong> {$company->company_name}</p>
                    <p style='margin:4px 0;'><strong>Contact:</strong> {$lead->contact_person_name}</p>
                    <p style='margin:4px 0;'><strong>Email:</strong> {$company->email}</p>
                    <p style='margin:4px 0;'><strong>Phone:</strong> {$company->phone}</p>
                </div>
                
                <h3 style='color:#185FA5;margin-top:24px;'>What's Next?</h3>
                
                <p>Now is the perfect time to:</p>
                <ol style='line-height:1.8;'>
                    <li><strong>Reach out to {$company->company_name}</strong> to gather feedback on their trial experience</li>
                    <li><strong>Address any concerns</strong> they may have about the platform</li>
                    <li><strong>Highlight the value</strong> they've gained during the trial</li>
                    <li><strong>Request trial extension</strong> if they need more time to evaluate</li>
                </ol>
                
                <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                    <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>💡 Pro Tip:</p>
                    <p style='margin:0;color:#0F6E56;'>If the client needs more time, you can re-request trial approval from your dashboard. Just make sure to note why additional trial time is needed!</p>
                </div>
                
                <div style='text-align:center;margin:30px 0;'>
                    <a href='" . route('affiliate.leads.show', $lead->id) . "' style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>View Lead Details</a>
                </div>
                
                <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                    Remember: Converting this lead to a paying customer means monthly recurring commissions for you! Keep the momentum going.
                </p>
            </div>
        ";

        $mailService->sendCustomizeMail([$affiliate->email], $subject, $message);
    }
}