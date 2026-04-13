<?php
namespace App\Jobs\Mail;

class SendLeadSuggestionsMail extends BaseMailJob
{
    public function __construct(
        public string $affiliateEmail,
        public string $affiliateFirstName,
        public int    $suggestionCount,
        public int    $highPriorityCount,
    ) {}

    public function handle(): void
    {
        $urgentText = $this->highPriorityCount > 0
            ? "<div style='background:#FCEBEB;border:1px solid #F7C1C1;border-radius:8px;padding:12px;margin:16px 0;'>
                <strong style='color:#A32D2D;'>🔥 {$this->highPriorityCount} Urgent Action" . ($this->highPriorityCount > 1 ? 's' : '') . " Needed!</strong>
               </div>"
            : '';

        $plural = $this->suggestionCount > 1 ? 's' : '';

        $this->send(
            [$this->affiliateEmail],
            '🎯 ' . $this->suggestionCount . ' New Action' . $plural . ' for Your Leads',
            "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <h2 style='color:#185FA5;'>Hi {$this->affiliateFirstName}! 👋</h2>

                    <p>You have <strong>{$this->suggestionCount} new suggested action{$plural}</strong> waiting for your leads.</p>

                    {$urgentText}

                    <p>These intelligent suggestions are based on your leads' status, temperature, and recent activity. Taking action now will help you:</p>

                    <ul style='line-height:1.8;'>
                        <li>Stay top of mind with your prospects</li>
                        <li>Move leads through the pipeline faster</li>
                        <li>Maximize your conversion rate</li>
                        <li>Earn more commissions! 💰</li>
                    </ul>

                    <div style='text-align:center;margin:30px 0;'>
                        <a href='" . route('affiliate.leads') . "'
                           style='background:#185FA5;color:#fff;padding:14px 32px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:500;'>
                           View Suggested Actions
                        </a>
                    </div>

                    <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                        💡 <strong>Pro Tip:</strong> Responding to high-priority suggestions within 24 hours can increase your conversion rate by up to 40%!
                    </p>
                </div>
            "
        );
    }
}