<?php
namespace App\Jobs\Mail;

use App\Models\Affiliate;
use App\Models\Lead;

class SendDemoScheduledMail extends BaseMailJob
{
    public function __construct(
        public int     $leadId,
        public string  $demoDate,
        public ?string $meetingLink = null,
    ) {}

    public function handle(): void
    {
        $lead      = Lead::with('company')->findOrFail($this->leadId);
        $company   = $lead->company;
        $affiliate = Affiliate::where('user_id', $lead->affiliate_id)->with('user')->first();
        $appName   = getOption('app_name'); // trusted admin config
        $demoDate  = $this->demoDate;       // server-formatted (Carbon), safe

        // Only treat a real http(s) URL as a usable link (defence-in-depth on top of the
        // controller's `url` validation — never render javascript:/other schemes in an href).
        $hasLink = $this->meetingLink && preg_match('#^https?://#i', $this->meetingLink);
        $link    = $hasLink ? $this->meetingLink : null;

        // Escape only values interpolated into free-text (text/note) blocks; panel rows are
        // auto-escaped by the panel part, so raw model values are passed straight in there.
        $companyName = e($company->company_name);

        // 1. Client
        if ($company?->email) {
            $blocks = [
                ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$companyName}</strong>"])
                    . ' ' . __('Great news — your demo for :app has been scheduled.', ['app' => "<strong>{$appName}</strong>"])],
                ['type' => 'panel', 'variant' => 'green', 'title' => __('Demo details'), 'rows' => [
                    ['k' => __('Date & time'), 'v' => $demoDate],
                    ['k' => __('Format'),      'v' => __('Live walkthrough with your account manager')],
                ]],
            ];
            if ($hasLink) {
                $blocks[] = ['type' => 'button', 'url' => $link, 'label' => __('Join the demo'), 'color' => '#0F6E56'];
                $blocks[] = ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                    . __('Or copy this link:') . ' ' . e($link) . '</span>'];
            } else {
                $blocks[] = ['type' => 'note', 'text' => __('Your account manager will share the meeting details with you directly.')];
            }
            $blocks[] = ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                . __('We look forward to showing you what :app can do for your business.', ['app' => $appName]) . '</span>'];

            $this->sendCs(
                [$company->email],
                __('Your demo has been scheduled') . ' – ' . $appName,
                [
                    'eyebrow' => __('Demo confirmed'), 'eyebrowColor' => '#0F6E56',
                    'title'   => __('Your demo is confirmed'),
                    'blocks'  => $blocks,
                ]
            );
        }

        // 2. Affiliate
        if ($affiliate?->user) {
            $firstName = e($affiliate->user->first_name);
            $linkBlock = $hasLink
                ? ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                    . __('Meeting link shared with the client:') . ' ' . e($link) . '</span>']
                : ['type' => 'note', 'text' => __('Remember to share your meeting link with the client before the demo.')];

            $this->sendCs(
                [$affiliate->user->email],
                __('Demo scheduled') . ' – ' . $company->company_name . ' | ' . $appName,
                [
                    'eyebrow' => __('Demo scheduled'), 'eyebrowColor' => '#185FA5',
                    'title'   => __('Demo scheduled successfully'),
                    'blocks'  => [
                        ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                            . ' ' . __('You have scheduled a demo for :company.', ['company' => "<strong>{$companyName}</strong>"])],
                        ['type' => 'panel', 'variant' => 'green', 'title' => __('Demo details'), 'rows' => [
                            ['k' => __('Date & time'), 'v' => $demoDate],
                        ]],
                        ['type' => 'panel', 'variant' => 'blue', 'title' => __('Lead details'), 'rows' => [
                            ['k' => __('Company'), 'v' => $company->company_name],
                            ['k' => __('Contact'), 'v' => $lead->contact_person_name],
                            ['k' => __('Email'),   'v' => $company->email],
                            ['k' => __('Phone'),   'v' => $company->phone],
                        ]],
                        $linkBlock,
                        ['type' => 'button', 'url' => route('affiliate.leads.show', $this->leadId), 'label' => __('View lead')],
                    ],
                ]
            );
        }
    }
}
