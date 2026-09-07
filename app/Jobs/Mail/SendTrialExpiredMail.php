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

        $appName     = getOption('app_name');
        $companyName = e($company->company_name);
        $firstName   = e($affiliate->first_name);

        $whatsNext = '<ol style="margin:0;padding-left:18px;line-height:1.8;">'
            . '<li>' . __('Reach out to :company to gather feedback on their trial experience', ['company' => "<strong>{$companyName}</strong>"]) . '</li>'
            . '<li>' . __('Address any concerns they may have about the platform') . '</li>'
            . '<li>' . __('Highlight the value they gained during the trial') . '</li>'
            . '<li>' . __('Request a trial extension if they need more time to evaluate') . '</li>'
            . '</ol>';

        $this->sendCs(
            [$affiliate->email],
            __('Trial expired') . ' - ' . $company->company_name . ' | ' . $appName,
            [
                'eyebrow' => __('Trial ended'), 'eyebrowColor' => '#854F0B',
                'title'   => __('Trial period has ended'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                        . ' ' . __('The trial period for :company has ended.', ['company' => "<strong>{$companyName}</strong>"])],
                    ['type' => 'panel', 'variant' => 'amber', 'title' => __('Lead details'), 'rows' => [
                        ['k' => __('Company'), 'v' => $company->company_name],
                        ['k' => __('Contact'), 'v' => $lead->contact_person_name],
                        ['k' => __('Email'),   'v' => $company->email],
                        ['k' => __('Phone'),   'v' => $company->phone],
                    ]],
                    ['type' => 'text', 'html' => '<strong>' . __("What's next?") . '</strong>' . $whatsNext],
                    ['type' => 'note', 'text' => '<strong>' . __('Pro tip') . '</strong> — '
                        . __('If the client needs more time, you can re-request trial approval from your dashboard. Just make sure to note why additional trial time is needed.')],
                    ['type' => 'button', 'url' => route('affiliate.leads.show', $this->leadId), 'label' => __('View lead details')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('Remember: converting this lead to a paying customer means monthly recurring commissions for you.') . '</span>'],
                ],
            ]
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

        $appName   = getOption('app_name');
        $firstName = e($user->first_name ?: (optional($company)->company_name ?? __('there')));

        $this->sendCs(
            [$user->email],
            __('Your trial has ended') . ' — ' . $appName,
            [
                'eyebrow' => __('Trial ended'), 'eyebrowColor' => '#854F0B',
                'title'   => __('Your free trial has ended'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                        . ' ' . __('Your trial of :app has come to an end. Your account has been moved to the Free plan, so you can keep signing in and using the essentials — nothing has been deleted.', ['app' => "<strong>{$appName}</strong>"])],
                    ['type' => 'panel', 'variant' => 'blue', 'title' => __('Want your full feature set back?'), 'rows' => [
                        ['k' => __('Upgrade'), 'v' => __('Pick the plan that fits your properties')],
                    ]],
                    ['type' => 'button', 'url' => route('owner.subscription.index'), 'label' => __('View plans & upgrade')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('Thank you for trying :app. We would love to have you on board.', ['app' => $appName]) . '</span>'],
                ],
            ]
        );
    }
}
