<?php
// app/Jobs/Mail/SendTrialApprovedMail.php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialApprovedMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public string $clientEmail,
        public string $resetLink,
        public string $trialEndsAt,
        public string $affiliateEmail,
        public string $affiliateFirstName,
    ) {}

    public function handle(): void
    {
        $lead    = Lead::with('company')->findOrFail($this->leadId);
        $company = $lead->company;
        $appName = getOption('app_name');

        // 1. Client — welcome + password setup
        $this->send(
            [$this->clientEmail],
            'Welcome to ' . $appName . ' - Your Trial Is Ready',
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#185FA5;'>🎉 Welcome to {$appName}!</h2>
                    <p>Hello <strong>{$company->company_name}</strong>,</p>
                    <p>Your trial account has been approved and is ready to use.</p>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Trial Details:</p>
                        <p style='margin:4px 0;'><strong>Start Date:</strong> " . now()->format('M d, Y') . "</p>
                        <p style='margin:4px 0;'><strong>End Date:</strong> {$this->trialEndsAt}</p>
                    </div>
                    <p>To get started, please set your password by clicking the button below:</p>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='{$this->resetLink}'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:500;'>
                           Set My Password & Get Started
                        </a>
                    </div>
                    <p><strong>Your login email:</strong> {$this->clientEmail}</p>
                    <p style='color:#6b7280;font-size:13px;'>This link will expire in 60 minutes.</p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:30px 0;'>
                    <p>Your account manager is available to assist you throughout your trial period.</p>
                    <p style='color:#9ca3af;font-size:12px;'>If the button doesn't work, copy and paste this link:<br>{$this->resetLink}</p>
                </div>
            "
        );

        // 2. Affiliate — account created notification
        $this->send(
            [$this->affiliateEmail],
            'Trial Approved - ' . $company->company_name . ' | ' . $appName,
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#185FA5;'>✅ Trial Account Created Successfully</h2>
                    <p>Hello <strong>{$this->affiliateFirstName}</strong>,</p>
                    <p>The trial account for <strong>{$company->company_name}</strong> has been approved and created.</p>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Trial Details:</p>
                        <p style='margin:4px 0;'><strong>Start Date:</strong> " . now()->format('M d, Y') . "</p>
                        <p style='margin:4px 0;'><strong>End Date:</strong> {$this->trialEndsAt}</p>
                    </div>
                    <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Client Details:</p>
                        <p style='margin:4px 0;'><strong>Company:</strong> {$company->company_name}</p>
                        <p style='margin:4px 0;'><strong>Contact:</strong> {$lead->contact_person_name}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$company->email}</p>
                        <p style='margin:4px 0;'><strong>Phone:</strong> {$company->phone}</p>
                    </div>
                    <p style='color:#6b7280;font-size:13px;'>
                        The client has been sent a welcome email with instructions to set up their password.
                        Now is a great time to follow up and guide them through the onboarding process!
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
    }
}