<?php
namespace App\Jobs\Mail;

use App\Models\Affiliate;
use App\Models\Lead;

class SendDemoCompletedMail extends BaseMailJob
{
    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead      = Lead::with('company')->findOrFail($this->leadId);
        $company   = $lead->company;
        $affiliate = Affiliate::where('user_id', $lead->affiliate_id)->with('user')->first();
        $appName   = getOption('app_name');

        // 1. Client
        if ($company?->email) {
            $this->send(
                [$company->email],
                'Thanks for Attending Your Demo – ' . $appName,
                "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <h2 style='color:#185FA5;'>✅ Thanks for Your Time Today</h2>
                        <p>Hello <strong>{$company->company_name}</strong>,</p>
                        <p>Thank you for attending the demo for <strong>{$appName}</strong>.
                        We hope it gave you a clear picture of how we can support your property management needs.</p>
                        <div style='background:#E1F5EE;border:1px solid #9FE1CB;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0;color:#0F6E56;font-weight:600;'>🚀 What Happens Next?</p>
                            <p style='margin:8px 0 0;'>Your account manager will be in touch shortly to answer any questions
                            and walk you through getting started.</p>
                        </div>
                        <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                            If you have any immediate questions, feel free to reach out directly to your account manager.
                        </p>
                    </div>
                "
            );
        }

        // 2. Affiliate
        if ($affiliate?->user) {
            $this->send(
                [$affiliate->user->email],
                'Demo Completed – ' . $company->company_name . ' | ' . $appName,
                "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <h2 style='color:#185FA5;'>✅ Demo Marked as Complete</h2>
                        <p>Hello <strong>{$affiliate->user->first_name}</strong>,</p>
                        <p>You have marked the demo for <strong>{$company->company_name}</strong> as completed.
                        A follow-up email has been sent to the client.</p>
                        <div style='background:#FFF7ED;border:1px solid #FED7AA;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0;color:#92400E;font-weight:600;'>⚡ Suggested Next Step</p>
                            <p style='margin:8px 0 0;color:#92400E;'>
                                Strike while the iron is hot — follow up with the client today to answer any
                                questions and move toward a trial request.
                            </p>
                        </div>
                        <div style='background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:16px;margin:20px 0;'>
                            <p style='margin:0 0 8px;font-weight:600;color:#1D4ED8;'>📋 Lead Details:</p>
                            <p style='margin:4px 0;'><strong>Company:</strong> {$company->company_name}</p>
                            <p style='margin:4px 0;'><strong>Contact:</strong> {$lead->contact_person_name}</p>
                            <p style='margin:4px 0;'><strong>Email:</strong> {$company->email}</p>
                            <p style='margin:4px 0;'><strong>Phone:</strong> {$company->phone}</p>
                        </div>
                        <div style='text-align:center;margin:30px 0;'>
                            <a href='" . route('affiliate.leads.show', $this->leadId) . "'
                               style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                               View Lead & Update Status
                            </a>
                        </div>
                    </div>
                "
            );
        }
    }
}