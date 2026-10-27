<?php
// app/Jobs/Mail/SendFreeInviteMail.php
namespace App\Jobs\Mail;

use App\Jobs\SendSmsJob;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

/**
 * Reaches a lead an affiliate has invited to activate a FREE Centresidence account.
 *
 * Two channels (the owner isn't a user yet, so there's no in-app bell):
 *   - Email: on-brand CS lifecycle mail with the activation CTA (/free/activate/{token}).
 *   - SMS: platform-paid (ownerUserId = null) + link-carrying — the only other way in. This is a
 *     customer-acquisition cost, so the platform pays.
 *
 * No account exists yet — it's created only when the owner opens the link and sets their password.
 * Fail-safe per channel: one failing never blocks the other.
 */
class SendFreeInviteMail extends BaseMailJob
{
    public function __construct(
        public int     $leadId,
        public string  $clientEmail,
        public ?string $clientPhone,
        public ?string $affiliateName,
        public int     $expiryDays,
    ) {}

    public function handle(): void
    {
        $lead = Lead::with('company')->find($this->leadId);
        if (! $lead || ! $lead->free_invite_token) {
            return;
        }

        $appName    = getOption('app_name');
        $who        = $this->affiliateName ? e($this->affiliateName) : $appName;
        $company    = e(optional($lead->company)->company_name ?? '');
        $activateUrl = route('free.invite', $lead->free_invite_token);

        // ── Email ───────────────────────────────────────────────────────────────
        try {
            $this->sendCs(
                [$this->clientEmail],
                __('Activate your free :app account', ['app' => $appName]),
                [
                    'eyebrow'   => __('You\'re invited'), 'eyebrowColor' => '#185FA5',
                    'title'     => __('Your free account is ready to activate'),
                    'preheader' => __(':who set up a free Centresidence account for you.', ['who' => $this->affiliateName ?: $appName]),
                    'blocks'    => [
                        ['type' => 'text', 'html' => __('Hello,') . ' '
                            . __(':who set up a free :app account for :company. Activate it by choosing your password — it stays free, with nothing to pay and no card required.', [
                                'who'     => "<strong>{$who}</strong>",
                                'app'     => $appName,
                                'company' => $company !== '' ? "<strong>{$company}</strong>" : __('your properties'),
                            ])],
                        ['type' => 'text', 'html' => '<strong>' . __('What you get, free') . '</strong><ul style="margin:8px 0 0;padding-left:20px;line-height:1.7;">'
                            . '<li>' . __('Collect rent from your tenants\' phones (M-Pesa included), matched and receipted automatically.') . '</li>'
                            . '<li>' . __('Every property and unit in one dashboard — who has paid, who is due, at a glance.') . '</li>'
                            . '<li>' . __('Deposits tracked, agreements signed in-app, a clean record for every tenancy.') . '</li>'
                            . '</ul>'],
                        ['type' => 'button', 'url' => $activateUrl, 'label' => __('Activate my free account')],
                        ['type' => 'note', 'text' => __('This link is just for you and expires in :days days. You choose your own password — no account exists until you do.', ['days' => $this->expiryDays])],
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Log::channel('sms-mail')->info('Free invite email failed: ' . $e->getMessage());
        }

        // ── SMS (platform-paid, link-carrying) ───────────────────────────────────
        if ($this->clientPhone) {
            try {
                $msg = __(':who set up a free :app account for you. Activate it (choose a password): :url', [
                    'who' => $this->affiliateName ?: $appName,
                    'app' => $appName,
                    'url' => $activateUrl,
                ]);
                SendSmsJob::dispatch([$this->clientPhone], $msg, null);
            } catch (\Throwable $e) {
                Log::channel('sms-mail')->info('Free invite SMS failed: ' . $e->getMessage());
            }
        }
    }
}
