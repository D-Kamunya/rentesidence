<?php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialExpiredMail extends BaseMailJob
{
    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead      = Lead::with('company')->findOrFail($this->leadId);
        $company   = $lead->company;
        $affiliate = $lead->affiliate;

        if (!$affiliate || !$company) return;

        // Escape affiliate/company free-text fields — raw-HTML email body, affiliate-entered.
        $companyName  = e($company->company_name);
        $contact      = e($lead->contact_person_name);
        $companyEmail = e($company->email);
        $companyPhone = e($company->phone);
        $firstName    = e($affiliate->first_name);

        $this->send(
            [$affiliate->email],
            'Trial Expired - ' . $company->company_name . ' | ' . getOption('app_name'),
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#854F0B;'>⏰ Trial Period Has Ended</h2>
                    <p>Hello <strong>{$firstName}</strong>,</p>
                    <p>The trial period for <strong>{$companyName}</strong> has ended.</p>
                    <div style='background:#FEF9EE;border:1px solid #FAC775;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#854F0B;'>📋 Lead Details:</p>
                        <p style='margin:4px 0;'><strong>Company:</strong> {$companyName}</p>
                        <p style='margin:4px 0;'><strong>Contact:</strong> {$contact}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$companyEmail}</p>
                        <p style='margin:4px 0;'><strong>Phone:</strong> {$companyPhone}</p>
                    </div>
                    <h3 style='color:#185FA5;margin-top:24px;'>What's Next?</h3>
                    <ol style='line-height:1.8;'>
                        <li><strong>Reach out to {$companyName}</strong> to gather feedback on their trial experience</li>
                        <li><strong>Address any concerns</strong> they may have about the platform</li>
                        <li><strong>Highlight the value</strong> they gained during the trial</li>
                        <li><strong>Request a trial extension</strong> if they need more time to evaluate</li>
                    </ol>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>💡 Pro Tip:</p>
                        <p style='margin:0;color:#0F6E56;'>If the client needs more time, you can re-request trial approval from your dashboard. Just make sure to note why additional trial time is needed!</p>
                    </div>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='" . route('affiliate.leads.show', $this->leadId) . "'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                           View Lead Details
                        </a>
                    </div>
                    <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                        Remember: Converting this lead to a paying customer means monthly recurring commissions for you!
                    </p>
                </div>
            "
        );
    }
}