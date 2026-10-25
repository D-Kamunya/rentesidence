<?php
namespace App\Jobs\Mail;

use App\Models\OutreachRecipient;
use App\Support\AffiliateInvite;

/**
 * Sends one campaign email (or its follow-up) to one recipient, on the CS layout like every other
 * system mail — so there's no bespoke HTML to maintain. Recruitment campaigns resolve the LIVE
 * invite link at send time (never a copy-pasted URL) and append a CTA button; placeholders
 * {{first_name}} / {{invite_link}} are filled per recipient. The send itself is marked on the
 * recipient row by the dispatcher; this job only renders + delivers.
 */
class SendOutreachMail extends BaseMailJob
{
    public function __construct(public int $recipientId, public bool $isFollowUp = false) {}

    public function handle(): void
    {
        $r = OutreachRecipient::with('campaign')->find($this->recipientId);
        if (! $r || ! $r->campaign || empty($r->email)) {
            return;
        }

        $campaign = $r->campaign;
        $appName  = getOption('app_name') ?: 'Centresidence';
        $name     = $r->first_name ?: __('there');
        $link     = $campaign->isRecruitment()
            ? (AffiliateInvite::link() ?: route('affiliate.apply'))
            : route('frontend');

        if ($this->isFollowUp) {
            // Short, bounded nudge — the system chases so the admin doesn't have to.
            $subject = __('Reminder: your :app partner invitation', ['app' => $appName]);
            $intro   = __('Hi :name, just following up — your :app partner invitation is still open, but the window is closing soon. You can set up your account in about a minute:', ['name' => e($name), 'app' => $appName]);
            $blocks  = [
                ['type' => 'text', 'html' => $intro],
                ['type' => 'button', 'url' => $link, 'label' => __('Set up my partner account')],
            ];
            $eyebrow = __('Reminder');
            $title   = __('Your invitation is still open');
        } else {
            $subject = $this->resolve($campaign->subject, $name, $link);
            $blocks  = [
                ['type' => 'text', 'html' => $this->renderBody($this->resolve($campaign->body, $name, $link))],
            ];
            if ($campaign->isRecruitment()) {
                $blocks[] = ['type' => 'button', 'url' => $link, 'label' => __('Set up my partner account')];
            }
            $eyebrow = $campaign->isRecruitment() ? __('Partner invitation') : $appName;
            $title   = $campaign->isRecruitment()
                ? __('You\'ve been selected to partner with :app', ['app' => $appName])
                : $this->resolve($campaign->subject, $name, $link);
        }

        $this->sendCs(
            [$r->email],
            $subject . ' | ' . $appName,
            [
                'eyebrow' => $eyebrow, 'eyebrowColor' => '#185FA5',
                'title'   => $title,
                'blocks'  => $blocks,
            ]
        );
    }

    private function resolve(string $s, string $name, string $link): string
    {
        return str_replace(['{{first_name}}', '{{invite_link}}'], [$name, $link], $s);
    }

    /**
     * Render an admin-authored plain-text body safely: escape everything first (admin is trusted, but
     * we never want raw HTML breaking the email), then allow lightweight **bold** markers and preserve
     * line breaks. Keeps the compose box plain-text-simple while letting key phrases stand out.
     */
    private function renderBody(string $text): string
    {
        $safe = e($text);
        $safe = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $safe);

        return nl2br($safe);
    }
}
