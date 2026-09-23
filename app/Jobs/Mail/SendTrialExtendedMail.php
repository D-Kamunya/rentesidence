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
        $from    = now()->format('M d, Y');

        $companyName = e($company->company_name);
        $firstName   = e($this->affiliateFirstName);

        // 1. Affiliate
        $this->sendCs(
            [$this->affiliateEmail],
            __('Trial extended') . ' - ' . $company->company_name . ' | ' . $appName,
            [
                'eyebrow' => __('Trial extended'), 'eyebrowColor' => '#0F6E56',
                'title'   => __('Trial extended successfully'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                        . ' ' . __('You have successfully extended the trial for :company.', ['company' => "<strong>{$companyName}</strong>"])],
                    ['type' => 'panel', 'variant' => 'green', 'title' => __('Updated trial details'), 'rows' => [
                        ['k' => __('Extended from'), 'v' => $from],
                        ['k' => __('New end date'),  'v' => $this->trialEndsAt],
                    ]],
                    ['type' => 'panel', 'variant' => 'blue', 'title' => __('Lead details'), 'rows' => [
                        ['k' => __('Company'), 'v' => $company->company_name],
                        ['k' => __('Contact'), 'v' => $lead->contact_person_name],
                        ['k' => __('Email'),   'v' => $company->email],
                        ['k' => __('Phone'),   'v' => $company->phone],
                    ]],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('Keep the momentum going — now is a great time to follow up and push for conversion.') . '</span>'],
                    ['type' => 'button', 'url' => route('affiliate.leads.show', $this->leadId), 'label' => __('View lead details')],
                ],
            ]
        );

        // 2. Client
        $this->sendCs(
            [$this->clientEmail],
            __('Your trial has been extended') . ' - ' . $appName,
            [
                'eyebrow' => __('Trial extended'), 'eyebrowColor' => '#0F6E56',
                'title'   => __('Your trial has been extended'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$companyName}</strong>"])
                        . ' ' . __('Great news! Your trial on :app has been extended.', ['app' => "<strong>{$appName}</strong>"])],
                    ['type' => 'panel', 'variant' => 'green', 'title' => __('Updated trial details'), 'rows' => [
                        ['k' => __('Extended from'), 'v' => $from],
                        ['k' => __('New end date'),  'v' => $this->trialEndsAt],
                    ]],
                    ['type' => 'text', 'html' => __('Your account manager is available to assist you throughout your extended trial period.')],
                    ['type' => 'button', 'url' => url('/'), 'label' => __('Continue using :app', ['app' => $appName])],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __("If you have any questions, please don't hesitate to reach out to your account manager.") . '</span>'],
                ],
            ]
        );
    }
}
