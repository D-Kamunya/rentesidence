<?php
namespace App\Jobs\Mail;

/**
 * Applicant-facing "thank you, not this time" note when an affiliate application is declined.
 * Deliberately warm and generic — it never surfaces the internal admin note (triage shorthand),
 * and leaves the door open (markets expand; they can apply again). Dispatched from
 * Admin\AffiliateApplicationController@reject. Gated by the usual CS mail switches (send only
 * when email is configured + active), like every other lifecycle mail.
 */
class SendAffiliateApplicationDeclinedMail extends BaseMailJob
{
    public function __construct(
        public string $email,
        public string $firstName,
    ) {}

    public function handle(): void
    {
        $appName   = getOption('app_name');
        $firstName = e($this->firstName);

        $this->sendCs(
            [$this->email],
            __('Your Centresidence partner application') . ' | ' . $appName,
            [
                'eyebrow' => __('Application update'), 'eyebrowColor' => '#6b7280',
                'title'   => __('Thank you for your interest'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$firstName}</strong>"])
                        . ' ' . __('Thank you for applying to join the :app Partner Program and for the time you took to tell us about yourself.', ['app' => "<strong>{$appName}</strong>"])],
                    ['type' => 'text', 'html' => __('After reviewing the applications we\'ve received, we won\'t be able to move forward with your application at this time.')],
                    ['type' => 'note', 'text' => '<strong>' . __('This isn\'t a closed door.') . '</strong> — '
                        . __('We keep applications on file and our programme keeps growing as we expand into new areas — you\'re very welcome to apply again in future.')],
                    ['type' => 'button', 'url' => route('frontend'), 'label' => __('Explore Centresidence')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('We genuinely appreciate your interest and wish you all the best.') . '</span>'],
                ],
            ]
        );
    }
}
