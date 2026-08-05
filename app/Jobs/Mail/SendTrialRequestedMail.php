<?php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialRequestedMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public bool   $isExtension,
        public string $extensionReason = '',
        public string $affiliateName = '',
        public string $affiliateEmail = '',
    ) {}

    public function handle(): void
    {
        $lead    = Lead::with('company')->findOrFail($this->leadId);
        $company = $lead->company;
        $appEmail = getOption('app_email');

        if (!$appEmail) return;

        // Escape every dynamic value — this raw-HTML body goes to the ADMIN inbox and
        // every field here (company data, affiliate name/email, extension reason) is
        // affiliate-entered free text. MailService does no escaping.
        $companyName    = e($company->company_name);
        $contact        = e($lead->contact_person_name);
        $companyEmail   = e($company->email);
        $companyPhone   = e($company->phone);
        $affiliateName  = e($this->affiliateName);
        $affiliateEmail = e($this->affiliateEmail);
        $extensionReason = e($this->extensionReason);

        if ($this->isExtension) {
            $subject = 'Trial Extension Requested - ' . $company->company_name . ' | ' . getOption('app_name');
            $body = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#854F0B;'>🔁 Trial Extension Requested</h2>
                    <p>An affiliate has requested a trial extension for a lead.</p>
                    <div style='background:#FEF9EE;border:1px solid #FAC775;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#854F0B;'>📋 Lead Details:</p>
                        <p style='margin:4px 0;'><strong>Company:</strong> {$companyName}</p>
                        <p style='margin:4px 0;'><strong>Contact:</strong> {$contact}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$companyEmail}</p>
                        <p style='margin:4px 0;'><strong>Phone:</strong> {$companyPhone}</p>
                    </div>
                    <div style='background:#FEF9EE;border:1px solid #FAC775;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#854F0B;'>👤 Affiliate Details:</p>
                        <p style='margin:4px 0;'><strong>Name:</strong> {$affiliateName}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$affiliateEmail}</p>
                    </div>
                    <div style='background:#FEF9EE;border:1px solid #FAC775;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#854F0B;'>📝 Extension Reason:</p>
                        <p style='margin:0;'>{$extensionReason}</p>
                    </div>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='" . route('admin.leads.show', $this->leadId) . "'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                           Review Request
                        </a>
                    </div>
                </div>
            ";
        } else {
            $subject = 'Trial Approval Requested - ' . $company->company_name . ' | ' . getOption('app_name');
            $body = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#185FA5;'>🎯 Trial Approval Requested</h2>
                    <p>An affiliate has requested trial approval for a lead.</p>
                    <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Lead Details:</p>
                        <p style='margin:4px 0;'><strong>Company:</strong> {$companyName}</p>
                        <p style='margin:4px 0;'><strong>Contact:</strong> {$contact}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$companyEmail}</p>
                        <p style='margin:4px 0;'><strong>Phone:</strong> {$companyPhone}</p>
                    </div>
                    <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>👤 Affiliate Details:</p>
                        <p style='margin:4px 0;'><strong>Name:</strong> {$affiliateName}</p>
                        <p style='margin:4px 0;'><strong>Email:</strong> {$affiliateEmail}</p>
                    </div>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='" . route('admin.leads.show', $this->leadId) . "'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                           Review Request
                        </a>
                    </div>
                </div>
            ";
        }

        $this->send([$appEmail], $subject, $body);
    }
}