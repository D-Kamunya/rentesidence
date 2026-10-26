<?php
namespace App\Jobs\Mail;

use App\Models\Affiliate;

/**
 * Activation nudge for a certified affiliate who hasn't added a single lead yet — the "trained and
 * sitting" churn gap. Points them to the guaranteed warm first lead (their own landlord) and the
 * add-lead page. CS-styled like all system mail; bounded + stopped-on-first-lead by the command that
 * dispatches it (AffiliateActivationNudge).
 */
class SendAffiliateActivationNudgeMail extends BaseMailJob
{
    public function __construct(public int $affiliateId) {}

    public function handle(): void
    {
        $affiliate = Affiliate::with('user')->find($this->affiliateId);
        if (! $affiliate || ! $affiliate->user || empty($affiliate->user->email)) {
            return;
        }

        $appName = getOption('app_name') ?: 'Centresidence';
        $first   = e($affiliate->user->first_name ?: __('there'));

        $this->sendCs(
            [$affiliate->user->email],
            __('Your first lead is one you already know') . ' | ' . $appName,
            [
                'eyebrow' => __('Let\'s get you started'), 'eyebrowColor' => '#185FA5',
                'title'   => __('You\'re certified — here\'s your fastest first win'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hi :name, you\'ve completed the academy — you\'re ready to earn. The quickest route to your first commission is the landlord you already know: <strong>your own</strong>.', ['name' => "<strong>{$first}</strong>"])],
                    ['type' => 'text', 'html' => __('Add your own landlord as your first lead. It\'s warm, it\'s real, and from there your dashboard guides every follow-up for you.')],
                    ['type' => 'button', 'url' => route('affiliate.leads.create'), 'label' => __('Add my first lead')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('Not your own landlord? Any landlord you know works — or share your referral link and let interested owners come to you.') . '</span>'],
                ],
            ]
        );
    }
}
