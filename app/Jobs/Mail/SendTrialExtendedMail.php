<?php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialExtendedMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public string $clientEmail,
        public string $trialEndsAt,
        public string $affiliateEmail,
        public string $affiliateFirstName,
    ) {}

    public function handle(): void
    {
        $lead    = Lead::with('company')->findOrFail($this->leadId);
        $company = $lead->company;
        $appName = getOption('app_name');

        // 1. Affiliate
        $this->send(
            [$this->affiliateEmail],
            'Trial Extended - ' . $company->company_name . ' | ' . $appName,
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#185FA5;'>✅ Trial Extended Successfully</h2>
                    <p>Hello <strong>{$this->affiliateFirstName}</strong>,</p>
                    <p>You have successfully extended the trial for <strong>{$company->company_name}</strong>.</p>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Updated Trial Details:</p>
                        <p style='margin:4px 0;'><strong>Extended From:</strong> " . now()->format('M d, Y') . "</p>
                        <p style='margin:4px 0;'><strong>New End Date:</strong> {$this->trialEndsAt}</p>
                    </div>
                    <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Lead Details:</p>
                        <p style='margin:4px 0;'><strong>Company:</strong> {$company->company_name}</p>
                        <p style='margin:4px 0;'><strong>Contact:</strong> {$lead->contact_person_name}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$company->email}</p>
                        <p style='margin:4px 0;'><strong>Phone:</strong> {$company->phone}</p>
                    </div>
                    <p style='color:#6b7280;font-size:13px;'>
                        Keep the momentum going — now is a great time to follow up and push for conversion!
                    </p>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='" . route('affiliate.leads.show', $this->leadId) . "'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                           View Lead Details
                        </a>
                    </div>
                </div>
            "
        );

        // 2. Client
        $this->send(
            [$this->clientEmail],
            'Your Trial Has Been Extended - ' . $appName,
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#185FA5;'>✅ Your Trial Has Been Extended</h2>
                    <p>Hello <strong>{$company->company_name}</strong>,</p>
                    <p>Great news! Your trial on <strong>{$appName}</strong> has been extended.</p>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Updated Trial Details:</p>
                        <p style='margin:4px 0;'><strong>Extended From:</strong> " . now()->format('M d, Y') . "</p>
                        <p style='margin:4px 0;'><strong>New End Date:</strong> {$this->trialEndsAt}</p>
                    </div>
                    <p>Your account manager is available to assist you throughout your extended trial period.</p>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='" . url('/') . "'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                           Continue Using {$appName}
                        </a>
                    </div>
                    <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                        If you have any questions, please don't hesitate to reach out to your account manager.
                    </p>
                </div>
            "
        );
    }
}