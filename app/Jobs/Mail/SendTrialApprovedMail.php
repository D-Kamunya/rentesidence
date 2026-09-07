<?php
// app/Jobs/Mail/SendTrialApprovedMail.php
namespace App\Jobs\Mail;

use App\Models\Lead;

class SendTrialApprovedMail extends BaseMailJob
{
    public function __construct(
        public int    $leadId,
        public string $clientEmail,
        public string $tempPassword,
        public string $trialEndsAt,
        public string $affiliateEmail,
        public string $affiliateFirstName,
    ) {}

    public function handle(): void
    {
        $lead     = Lead::with('company')->findOrFail($this->leadId);
        $company  = $lead->company;
        $appName  = getOption('app_name');
        $startDate = now()->format('M d, Y');

        $companyName = e($company->company_name);
        $firstName   = e($this->affiliateFirstName);

        // 1. Client — welcome + first-login credentials
        $this->sendCs(
            [$this->clientEmail],
            __('Welcome to :app - your trial is ready', ['app' => $appName]),
            [
                'eyebrow' => __('Welcome'), 'eyebrowColor' => '#0F6E56',
                'title'   => __('Welcome to :app!', ['app' => $appName]),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$companyName}</strong>"])
                        . ' ' . __('Your trial account has been approved and is ready to use.')],
                    ['type' => 'panel', 'variant' => 'green', 'title' => __('Trial details'), 'rows' => [
                        ['k' => __('Start date'), 'v' => $startDate],
                        ['k' => __('End date'),   'v' => $this->trialEndsAt],
                    ]],
                    ['type' => 'text', 'html' => __('Sign in with the temporary password below — for your security you will be asked to set your own password right after you sign in:')],
                    ['type' => 'panel', 'variant' => 'blue', 'title' => __('Your login'), 'rows' => [
                        ['k' => __('Login email'),        'v' => $this->clientEmail],
                        ['k' => __('Temporary password'), 'v' => $this->tempPassword, 'mono' => true],
                    ]],
                    ['type' => 'button', 'url' => route('login'), 'label' => __('Sign in & get started')],
                    ['type' => 'note', 'text' => __('Keep this password private. You will choose your own the moment you sign in.')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('Your account manager is available to assist you throughout your trial period.') . '</span>'],
                ],
            ]
        );

        // 2. Affiliate — account created notification
        $this->sendCs(
            [$this->affiliateEmail],
            __('Trial approved') . ' - ' . $company->company_name . ' | ' . $appName,
            [
                'eyebrow' => __('Trial approved'), 'eyebrowColor' => '#0F6E56',
                'title'   => __('Trial account created successfully'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                        . ' ' . __('The trial account for :company has been approved and created.', ['company' => "<strong>{$companyName}</strong>"])],
                    ['type' => 'panel', 'variant' => 'green', 'title' => __('Trial details'), 'rows' => [
                        ['k' => __('Start date'), 'v' => $startDate],
                        ['k' => __('End date'),   'v' => $this->trialEndsAt],
                    ]],
                    ['type' => 'panel', 'variant' => 'blue', 'title' => __('Client details'), 'rows' => [
                        ['k' => __('Company'), 'v' => $company->company_name],
                        ['k' => __('Contact'), 'v' => $lead->contact_person_name],
                        ['k' => __('Email'),   'v' => $company->email],
                        ['k' => __('Phone'),   'v' => $company->phone],
                    ]],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('The client has been sent a welcome email with their login details; they will set their own password on first sign-in. Now is a great time to follow up and guide them through onboarding.') . '</span>'],
                    ['type' => 'button', 'url' => route('affiliate.leads.show', $this->leadId), 'label' => __('View lead details')],
                ],
            ]
        );
    }
}
