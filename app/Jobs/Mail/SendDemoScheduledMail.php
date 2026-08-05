<?php
namespace App\Jobs\Mail;

use App\Models\Affiliate;
use App\Models\Lead;

class SendDemoScheduledMail extends BaseMailJob
{
    public function __construct(
        public int     $leadId,
        public string  $demoDate,
        public ?string $meetingLink = null,
    ) {}

    public function handle(): void
    {
        $lead      = Lead::with('company')->findOrFail($this->leadId);
        $company   = $lead->company;
        $affiliate = Affiliate::where('user_id', $lead->affiliate_id)->with('user')->first();
        $appName   = getOption('app_name'); // trusted admin config
        $demoDate  = $this->demoDate;       // server-formatted (Carbon), safe

        // Escape the affiliate/company free-text fields — they land in a raw-HTML
        // email body (MailService does no escaping) and are affiliate-entered.
        $companyName  = e($company->company_name);
        $contact      = e($lead->contact_person_name);
        $companyEmail = e($company->email);
        $companyPhone = e($company->phone);

        // Only treat a real http(s) URL as a usable link (defence-in-depth on top
        // of the controller's `url` validation — never render javascript:/other
        // schemes in an href). Escaped for safe interpolation into the body.
        $hasLink  = $this->meetingLink && preg_match('#^https?://#i', $this->meetingLink);
        $safeLink = $hasLink ? e($this->meetingLink) : '';

        // Client-facing block: a Join button when a link is set, otherwise a
        // graceful line that doesn't promise an automatic link.
        $clientLinkBlock = $hasLink
            ? "<div style='text-align:center;margin:24px 0;'>
                   <a href='{$safeLink}'
                      style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:500;'>
                      Join the Demo
                   </a>
                   <p style='color:#9ca3af;font-size:12px;margin:10px 0 0;'>Or copy this link: {$safeLink}</p>
               </div>"
            : "<p>Your account manager will share the meeting details with you directly.</p>";

        // Affiliate-facing block: confirm the saved link, or nudge to share one.
        $affiliateLinkBlock = $hasLink
            ? "<p style='color:#6b7280;font-size:13px;'>Meeting link shared with the client: <a href='{$safeLink}'>{$safeLink}</a></p>"
            : "<p style='color:#6b7280;font-size:13px;'>Remember to share your meeting link with the client before the demo.</p>";

        // 1. Client
        if ($company?->email) {
            $this->send(
                [$company->email],
                'Your Demo Has Been Scheduled – ' . $appName,
                "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <h2 style='color:#185FA5;'>📅 Your Demo Is Confirmed</h2>
                        <p>Hello <strong>{$companyName}</strong>,</p>
                        <p>Great news — your demo for <strong>{$appName}</strong> has been scheduled.</p>
                        <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Demo Details:</p>
                            <p style='margin:4px 0;'><strong>Date & Time:</strong> {$demoDate}</p>
                            <p style='margin:4px 0;'><strong>Format:</strong> Live walkthrough with your account manager</p>
                        </div>
                        {$clientLinkBlock}
                        <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                            We look forward to showing you what {$appName} can do for your business.
                        </p>
                    </div>
                "
            );
        }

        // 2. Affiliate
        if ($affiliate?->user) {
            $firstName = e($affiliate->user->first_name);
            $this->send(
                [$affiliate->user->email],
                'Demo Scheduled – ' . $company->company_name . ' | ' . getOption('app_name'),
                "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <h2 style='color:#185FA5;'>📅 Demo Scheduled Successfully</h2>
                        <p>Hello <strong>{$firstName}</strong>,</p>
                        <p>You have scheduled a demo for <strong>{$companyName}</strong>.</p>
                        <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Demo Details:</p>
                            <p style='margin:4px 0;'><strong>Date & Time:</strong> {$demoDate}</p>
                        </div>
                        <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Lead Details:</p>
                            <p style='margin:4px 0;'><strong>Company:</strong> {$companyName}</p>
                            <p style='margin:4px 0;'><strong>Contact:</strong> {$contact}</p>
                            <p style='margin:4px 0;'><strong>Email:</strong> {$companyEmail}</p>
                            <p style='margin:4px 0;'><strong>Phone:</strong> {$companyPhone}</p>
                        </div>
                        {$affiliateLinkBlock}
                        <div style='text-align:center;margin:30px 0;'>
                            <a href='" . route('affiliate.leads.show', $this->leadId) . "'
                               style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                               View Lead
                            </a>
                        </div>
                    </div>
                "
            );
        }
    }
}