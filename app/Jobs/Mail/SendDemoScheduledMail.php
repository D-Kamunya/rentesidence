<?php
namespace App\Jobs\Mail;

use App\Models\Affiliate;
use App\Models\Lead;

class SendDemoScheduledMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public string $demoDate,
    ) {}

    public function handle(): void
    {
        $lead      = Lead::with('company')->findOrFail($this->leadId);
        $company   = $lead->company;
        $affiliate = Affiliate::where('user_id', $lead->affiliate_id)->with('user')->first();
        $appName   = getOption('app_name');
        $demoDate  = $this->demoDate;

        // 1. Client
        if ($company?->email) {
            $this->send(
                [$company->email],
                'Your Demo Has Been Scheduled – ' . $appName,
                "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <h2 style='color:#185FA5;'>📅 Your Demo Is Confirmed</h2>
                        <p>Hello <strong>{$company->company_name}</strong>,</p>
                        <p>Great news — your demo for <strong>{$appName}</strong> has been scheduled.</p>
                        <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Demo Details:</p>
                            <p style='margin:4px 0;'><strong>Date & Time:</strong> {$demoDate}</p>
                            <p style='margin:4px 0;'><strong>Format:</strong> Live walkthrough with your account manager</p>
                        </div>
                        <p>Your account manager will be in touch shortly with the meeting link.</p>
                        <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                            We look forward to showing you what {$appName} can do for your business.
                        </p>
                    </div>
                "
            );
        }

        // 2. Affiliate
        if ($affiliate?->user) {
            $this->send(
                [$affiliate->user->email],
                'Demo Scheduled – ' . $company->company_name . ' | ' . $appName,
                "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <h2 style='color:#185FA5;'>📅 Demo Scheduled Successfully</h2>
                        <p>Hello <strong>{$affiliate->user->first_name}</strong>,</p>
                        <p>You have scheduled a demo for <strong>{$company->company_name}</strong>.</p>
                        <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0 0 8px;font-weight:600;color:#0F6E56;'>📋 Demo Details:</p>
                            <p style='margin:4px 0;'><strong>Date & Time:</strong> {$demoDate}</p>
                        </div>
                        <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Lead Details:</p>
                            <p style='margin:4px 0;'><strong>Company:</strong> {$company->company_name}</p>
                            <p style='margin:4px 0;'><strong>Contact:</strong> {$lead->contact_person_name}</p>
                            <p style='margin:4px 0;'><strong>Email:</strong> {$company->email}</p>
                            <p style='margin:4px 0;'><strong>Phone:</strong> {$company->phone}</p>
                        </div>
                        <p style='color:#6b7280;font-size:13px;'>
                            Remember to share your meeting link with the client before the demo.
                        </p>
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