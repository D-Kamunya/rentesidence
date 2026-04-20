<?php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialRejectedMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public string $affiliateEmail,
        public string $affiliateFirstName,
        public string $rejectionReason,
    ) {}

    public function handle(): void
    {
        $lead    = Lead::with('company')->findOrFail($this->leadId);
        $company = $lead->company;
        $appName = getOption('app_name');

        $this->send(
            [$this->affiliateEmail],
            'Trial Request Rejected - ' . $company->company_name . ' | ' . $appName,
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#854F0B;'>❌ Trial Request Rejected</h2>
                    <p>Hello <strong>{$this->affiliateFirstName}</strong>,</p>
                    <p>Unfortunately the trial account request for <strong>{$company->company_name}</strong> has been rejected by the admin.</p>
                    <div style='background:#FEF9EE;border:1px solid #FAC775;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#854F0B;'>📋 Lead Details:</p>
                        <p style='margin:4px 0;'><strong>Company:</strong> {$company->company_name}</p>
                        <p style='margin:4px 0;'><strong>Contact:</strong> {$lead->contact_person_name}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$company->email}</p>
                        <p style='margin:4px 0;'><strong>Phone:</strong> {$company->phone}</p>
                    </div>
                    <div style='background:#FEF2F2;border:1px solid #FCA5A5;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#DC2626;'>📝 Rejection Reason:</p>
                        <p style='margin:0;color:#DC2626;'>{$this->rejectionReason}</p>
                    </div>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>💡 What's Next?</p>
                        <p style='margin:0;color:#0F6E56;'>Please review the rejection reason above, make the necessary corrections, and resubmit the trial request from your dashboard.</p>
                    </div>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='" . route('affiliate.leads.show', $this->leadId) . "'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                           View Lead Details
                        </a>
                    </div>
                    <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                        If you have any questions about the rejection, please contact the admin for further clarification.
                    </p>
                </div>
            "
        );
    }
}