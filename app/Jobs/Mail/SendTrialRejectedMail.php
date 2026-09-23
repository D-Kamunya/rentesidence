<?php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialRejectedMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public string $affiliateEmail,
        public string $affiliateFirstName,
        public string $rejectionReason,
    ) {}

    public function handle(): void
    {
        $lead    = Lead::with('company')->findOrFail($this->leadId);
        $company = $lead->company;
        $appName = getOption('app_name');

        $companyName = e($company->company_name);
        $firstName   = e($this->affiliateFirstName);

        $this->sendCs(
            [$this->affiliateEmail],
            __('Trial request rejected') . ' - ' . $company->company_name . ' | ' . $appName,
            [
                'eyebrow' => __('Trial rejected'), 'eyebrowColor' => '#B42318',
                'title'   => __('Trial request rejected'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                        . ' ' . __('Unfortunately the trial account request for :company has been rejected by the admin.', ['company' => "<strong>{$companyName}</strong>"])],
                    ['type' => 'panel', 'variant' => 'blue', 'title' => __('Lead details'), 'rows' => [
                        ['k' => __('Company'), 'v' => $company->company_name],
                        ['k' => __('Contact'), 'v' => $lead->contact_person_name],
                        ['k' => __('Email'),   'v' => $company->email],
                        ['k' => __('Phone'),   'v' => $company->phone],
                    ]],
                    ['type' => 'panel', 'variant' => 'red', 'title' => __('Rejection reason'), 'rows' => [
                        ['k' => __('Reason'), 'v' => $this->rejectionReason],
                    ]],
                    ['type' => 'note', 'text' => '<strong>' . __("What's next?") . '</strong> — '
                        . __('Please review the rejection reason above, make the necessary corrections, and resubmit the trial request from your dashboard.')],
                    ['type' => 'button', 'url' => route('affiliate.leads.show', $this->leadId), 'label' => __('View lead details')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('If you have any questions about the rejection, please contact the admin for further clarification.') . '</span>'],
                ],
            ]
        );
    }
}
