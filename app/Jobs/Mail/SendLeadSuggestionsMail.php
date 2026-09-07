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
        $firstName = e($this->affiliateFirstName); // escaped — lands in a free-text block
        $count     = $this->suggestionCount;
        $plural    = $count > 1 ? 's' : '';

        $blocks = [
            ['type' => 'text', 'html' => __('Hi :name,', ['name' => "<strong>{$firstName}</strong>"])
                . ' ' . __('You have :count new suggested action(s) waiting for your leads.', ['count' => "<strong>{$count}</strong>"])],
        ];

        if ($this->highPriorityCount > 0) {
            $blocks[] = ['type' => 'note', 'text' => '<strong>'
                . trans_choice(':count urgent action needs attention|:count urgent actions need attention', $this->highPriorityCount, ['count' => $this->highPriorityCount])
                . '</strong>'];
        }

        $blocks[] = ['type' => 'text', 'html' => __('These suggestions are based on your leads\' status, temperature, and recent activity. Acting now helps you stay top of mind, move leads through the pipeline faster, and earn more commissions.')];
        $blocks[] = ['type' => 'button', 'url' => route('affiliate.leads'), 'label' => __('View suggested actions')];
        $blocks[] = ['type' => 'note', 'text' => '<strong>' . __('Pro tip') . '</strong> — '
            . __('Leads go cold fast — responding to high-priority suggestions within 24 hours keeps you ahead.')];

        $this->sendCs(
            [$this->affiliateEmail],
            $count . ' ' . __('new action' . ($plural ? 's' : '') . ' for your leads'),
            [
                'eyebrow' => __('Lead suggestions'), 'eyebrowColor' => '#185FA5',
                'title'   => __(':count new suggested action(s) for your leads', ['count' => $count]),
                'blocks'  => $blocks,
            ]
        );
    }
}
