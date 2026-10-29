<?php
namespace App\Jobs\Mail;

use App\Models\Affiliate;

/**
 * Tells an affiliate that an admin has reset their blocked academy module — the "the gate is open
 * again, continue" signal. The in-app bell fires too (AcademyAdminController::resetAffiliateModule),
 * but a blocked affiliate who walked away needs the email to know they can actually resume; without
 * it they stay stuck and drift off. CS-styled like all system mail.
 */
class SendAcademyRetakeResetMail extends BaseMailJob
{
    public function __construct(public int $affiliateId, public string $moduleTitle) {}

    public function handle(): void
    {
        $affiliate = Affiliate::with('user')->find($this->affiliateId);
        if (! $affiliate || ! $affiliate->user || empty($affiliate->user->email)) {
            return;
        }

        $appName = getOption('app_name') ?: 'Centresidence';
        $first   = e($affiliate->user->first_name ?: __('there'));
        $module  = e($this->moduleTitle);

        $this->sendCs(
            [$affiliate->user->email],
            __('You can retake your academy module') . ' | ' . $appName,
            [
                'eyebrow' => __('Good news'), 'eyebrowColor' => '#1D9E75',
                'title'   => __('Your retake is open — pick up where you left off'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hi :name, an admin has reset your attempts on <strong>":module"</strong>. You can take it again now — the rest of your academy progress is untouched.', ['name' => "<strong>{$first}</strong>", 'module' => $module])],
                    ['type' => 'text', 'html' => __('Give it another go when you\'re ready. There\'s no rush, and finishing gets you certified so your partner tools unlock.')],
                    ['type' => 'button', 'url' => route('affiliate.academy.index'), 'label' => __('Retake the module')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>" . __('Stuck or unsure about something? Contact support from your dashboard and we\'ll walk you through it.') . '</span>'],
                ],
            ]
        );
    }
}
