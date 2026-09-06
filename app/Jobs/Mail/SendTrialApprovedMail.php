<?php
// app/Jobs/Mail/SendTrialApprovedMail.php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialApprovedMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public string $clientEmail,
        public string $tempPassword,
        public string $trialEndsAt,
        public string $affiliateEmail,
        public string $affiliateFirstName,
    ) {}

    public function handle(): void
    {
        $lead    = Lead::with('company')->findOrFail($this->leadId);
        $company = $lead->company;
        $appName = getOption('app_name');

        // Escape affiliate/company free-text fields — raw-HTML email body, affiliate-entered.
        $companyName  = e($company->company_name);
        $contact      = e($lead->contact_person_name);
        $companyEmail = e($company->email);
        $companyPhone = e($company->phone);
        $firstName    = e($this->affiliateFirstName);
        $clientEmail  = e($this->clientEmail);
        $tempPw       = e($this->tempPassword);
        $loginUrl     = route('login');

        // 1. Client — welcome + password setup
        $this->send(
            [$this->clientEmail],
            'Welcome to ' . $appName . ' - Your Trial Is Ready',
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#185FA5;'>🎉 Welcome to {$appName}!</h2>
                    <p>Hello <strong>{$companyName}</strong>,</p>
                    <p>Your trial account has been approved and is ready to use.</p>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Trial Details:</p>
                        <p style='margin:4px 0;'><strong>Start Date:</strong> " . now()->format('M d, Y') . "</p>
                        <p style='margin:4px 0;'><strong>End Date:</strong> {$this->trialEndsAt}</p>
                    </div>
                    <p>Sign in with the temporary password below — for your security you'll be asked to set your own password right after you sign in:</p>
                    <div style='background:#F4F6F8;border:1px solid #D8DEE6;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:4px 0;'><strong>Login email:</strong> {$clientEmail}</p>
                        <p style='margin:4px 0;'><strong>Temporary password:</strong> <code style='background:#fff;border:1px solid #D8DEE6;border-radius:5px;padding:3px 8px;font-family:monospace;'>{$tempPw}</code></p>
                    </div>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='{$loginUrl}'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:500;'>
                           Sign In & Get Started
                        </a>
                    </div>
                    <p style='color:#6b7280;font-size:13px;'>Keep this password private. You'll choose your own the moment you sign in.</p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:30px 0;'>
                    <p>Your account manager is available to assist you throughout your trial period.</p>
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
                    <p>Hello <strong>{$firstName}</strong>,</p>
                    <p>The trial account for <strong>{$companyName}</strong> has been approved and created.</p>
                    <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Trial Details:</p>
                        <p style='margin:4px 0;'><strong>Start Date:</strong> " . now()->format('M d, Y') . "</p>
                        <p style='margin:4px 0;'><strong>End Date:</strong> {$this->trialEndsAt}</p>
                    </div>
                    <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Client Details:</p>
                        <p style='margin:4px 0;'><strong>Company:</strong> {$companyName}</p>
                        <p style='margin:4px 0;'><strong>Contact:</strong> {$contact}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$companyEmail}</p>
                        <p style='margin:4px 0;'><strong>Phone:</strong> {$companyPhone}</p>
                    </div>
                    <p style='color:#6b7280;font-size:13px;'>
                        The client has been sent a welcome email with their login details; they'll set their own
                        password on first sign-in. Now is a great time to follow up and guide them through onboarding!
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