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
        $lead     = Lead::with('company')->findOrFail($this->leadId);
        $company  = $lead->company;
        $appName  = getOption('app_name');
        $appEmail = getOption('app_email');

        if (!$appEmail) return;

        $leadPanel = ['type' => 'panel', 'variant' => 'blue', 'title' => __('Lead details'), 'rows' => [
            ['k' => __('Company'), 'v' => $company->company_name],
            ['k' => __('Contact'), 'v' => $lead->contact_person_name],
            ['k' => __('Email'),   'v' => $company->email],
            ['k' => __('Phone'),   'v' => $company->phone],
        ]];
        $affiliatePanel = ['type' => 'panel', 'variant' => 'blue', 'title' => __('Affiliate details'), 'rows' => [
            ['k' => __('Name'),  'v' => $this->affiliateName],
            ['k' => __('Email'), 'v' => $this->affiliateEmail],
        ]];
        $reviewButton = ['type' => 'button', 'url' => route('admin.leads.show', $this->leadId), 'label' => __('Review request')];

        if ($this->isExtension) {
            $blocks = [
                ['type' => 'text', 'html' => __('An affiliate has requested a trial extension for a lead.')],
                $leadPanel,
                $affiliatePanel,
            ];
            if ($this->extensionReason !== '') {
                $blocks[] = ['type' => 'panel', 'variant' => 'amber', 'title' => __('Extension reason'), 'rows' => [
                    ['k' => __('Reason'), 'v' => $this->extensionReason],
                ]];
            }
            $blocks[] = $reviewButton;

            $this->sendCs(
                [$appEmail],
                __('Trial extension requested') . ' - ' . $company->company_name . ' | ' . $appName,
                [
                    'eyebrow' => __('Trial extension requested'), 'eyebrowColor' => '#854F0B',
                    'title'   => __('Trial extension requested'),
                    'blocks'  => $blocks,
                ]
            );
        } else {
            $this->sendCs(
                [$appEmail],
                __('Trial approval requested') . ' - ' . $company->company_name . ' | ' . $appName,
                [
                    'eyebrow' => __('Trial approval requested'), 'eyebrowColor' => '#185FA5',
                    'title'   => __('Trial approval requested'),
                    'blocks'  => [
                        ['type' => 'text', 'html' => __('An affiliate has requested trial approval for a lead.')],
                        $leadPanel,
                        $affiliatePanel,
                        $reviewButton,
                    ],
                ]
            );
        }
    }
}
