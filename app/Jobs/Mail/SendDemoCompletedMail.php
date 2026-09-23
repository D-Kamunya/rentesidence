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
        $appName   = getOption('app_name'); // trusted admin config

        $companyName = e($company->company_name);

        // 1. Client
        if ($company?->email) {
            $this->sendCs(
                [$company->email],
                __('Thanks for attending your demo') . ' – ' . $appName,
                [
                    'eyebrow' => __('Thank you'), 'eyebrowColor' => '#0F6E56',
                    'title'   => __('Thanks for your time today'),
                    'blocks'  => [
                        ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$companyName}</strong>"])
                            . ' ' . __('Thank you for attending the demo for :app. We hope it gave you a clear picture of how we can support your property management needs.', ['app' => "<strong>{$appName}</strong>"])],
                        ['type' => 'panel', 'variant' => 'green', 'title' => __('What happens next'), 'rows' => [
                            ['k' => __('Follow-up'), 'v' => __('Your account manager will be in touch shortly')],
                        ]],
                        ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                            . __('If you have any immediate questions, feel free to reach out directly to your account manager.') . '</span>'],
                    ],
                ]
            );
        }

        // 2. Affiliate
        if ($affiliate?->user) {
            $firstName = e($affiliate->user->first_name);
            $this->sendCs(
                [$affiliate->user->email],
                __('Demo completed') . ' – ' . $company->company_name . ' | ' . $appName,
                [
                    'eyebrow' => __('Demo completed'), 'eyebrowColor' => '#185FA5',
                    'title'   => __('Demo marked as complete'),
                    'blocks'  => [
                        ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                            . ' ' . __('You have marked the demo for :company as completed. A follow-up email has been sent to the client.', ['company' => "<strong>{$companyName}</strong>"])],
                        ['type' => 'note', 'text' => '<strong>' . __('Suggested next step') . '</strong> — '
                            . __('Strike while the iron is hot: follow up with the client today to answer any questions and move toward a trial request.')],
                        ['type' => 'panel', 'variant' => 'blue', 'title' => __('Lead details'), 'rows' => [
                            ['k' => __('Company'), 'v' => $company->company_name],
                            ['k' => __('Contact'), 'v' => $lead->contact_person_name],
                            ['k' => __('Email'),   'v' => $company->email],
                            ['k' => __('Phone'),   'v' => $company->phone],
                        ]],
                        ['type' => 'button', 'url' => route('affiliate.leads.show', $this->leadId), 'label' => __('View lead & update status')],
                    ],
                ]
            );
        }
    }
}
