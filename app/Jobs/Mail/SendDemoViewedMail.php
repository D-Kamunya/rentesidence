<?php
namespace App\Jobs\Mail;

use App\Models\Affiliate;
use App\Models\Lead;

/**
 * The prospect self-viewed the guided demo — tell the affiliate right away so they can strike while
 * interest is at its peak. Affiliate-facing only (the prospect self-served; the affiliate owns the
 * relationship). This is the immediate "they're hot now" pull; the suggestion engine separately
 * catches the case where the affiliate then goes silent.
 */
class SendDemoViewedMail extends BaseMailJob
{
    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead      = Lead::with('company')->findOrFail($this->leadId);
        $company   = $lead->company;
        $affiliate = Affiliate::where('user_id', $lead->affiliate_id)->with('user')->first();
        $appName   = getOption('app_name');

        if (! $affiliate?->user) {
            return;
        }

        $firstName   = e($affiliate->user->first_name);
        $companyName = e($company->company_name);

        $this->sendCs(
            [$affiliate->user->email],
            __('Your prospect watched the demo') . ' – ' . $company->company_name . ' | ' . $appName,
            [
                'eyebrow' => __('Demo viewed'), 'eyebrowColor' => '#185FA5',
                'title'   => __('Your prospect just watched the demo'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                        . ' ' . __(':company just went through the guided demo for :app — a strong sign of interest.', ['company' => "<strong>{$companyName}</strong>", 'app' => "<strong>{$appName}</strong>"])],
                    ['type' => 'note', 'text' => '<strong>' . __('Strike while it\'s hot') . '</strong> — '
                        . __('Reach out today while it\'s fresh: answer their questions and move them toward getting started on Free or a trial.')],
                    ['type' => 'panel', 'variant' => 'blue', 'title' => __('Lead details'), 'rows' => [
                        ['k' => __('Company'), 'v' => $company->company_name],
                        ['k' => __('Contact'), 'v' => $lead->contact_person_name],
                        ['k' => __('Email'),   'v' => $company->email],
                        ['k' => __('Phone'),   'v' => $company->phone],
                    ]],
                    ['type' => 'button', 'url' => route('affiliate.leads.show', $this->leadId), 'label' => __('View lead & follow up')],
                ],
            ]
        );
    }
}
