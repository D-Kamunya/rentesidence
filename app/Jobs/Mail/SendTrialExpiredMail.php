<?php
namespace App\Jobs\Mail;

use App\Models\Lead;
use App\Models\User;

class SendTrialExpiredMail extends BaseMailJob
{
    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead      = Lead::with(['company', 'owner'])->findOrFail($this->leadId);
        $company   = $lead->company;
        $affiliate = $lead->affiliate;

        // Notify the account holder (the converted lead's owner user) too — their trial
        // ended and the account has been moved to the Free plan.
        $this->notifyUser($lead, $company);

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

    /**
     * User-facing trial-ended email — the account holder is told their trial has ended
     * and their account moved to the Free plan, with a path to upgrade. Distinct from the
     * affiliate email above (which is about following up the lead).
     */
    private function notifyUser(Lead $lead, $company): void
    {
        $owner = $lead->owner;
        $user  = $owner ? User::find($owner->user_id) : null;

        if (!$user || empty($user->email)) return;

        $appName    = getOption('app_name');
        $firstName  = e($user->first_name ?: (optional($company)->company_name ?? 'there'));
        $upgradeUrl = route('owner.subscription.index');

        $this->send(
            [$user->email],
            'Your trial has ended — ' . $appName,
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#854F0B;'>Your free trial has ended</h2>
                    <p>Hello <strong>{$firstName}</strong>,</p>
                    <p>Your trial of <strong>" . e($appName) . "</strong> has come to an end. Your account has been
                       moved to the <strong>Free plan</strong>, so you can keep signing in and using the essentials —
                       nothing has been deleted.</p>
                    <div style='background:#E6F1FB;border:1px solid #B5D4F4;border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0;color:#0C447C;'>Want your full feature set back? Upgrade any time and pick the
                           plan that fits your properties.</p>
                    </div>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='{$upgradeUrl}'
                           style='background:#185FA5;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;'>
                           View plans &amp; upgrade
                        </a>
                    </div>
                    <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                        Thank you for trying {$appName}. We'd love to have you on board.
                    </p>
                </div>
            "
        );
    }
}