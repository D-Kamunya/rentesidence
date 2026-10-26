<?php
namespace App\Jobs\Mail;

use App\Models\User;

/**
 * Emails a user that the team has sent them an in-system message (admin-initiated support thread),
 * so they're prompted to log in and reply even when they're off-system. CS-styled like all system
 * mail. The message body is admin-authored (trusted) but still escaped before rendering.
 */
class SendAdminMessageMail extends BaseMailJob
{
    public function __construct(
        public int $userId,
        public int $ticketId,
        public string $subject,
        public string $body,
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user || empty($user->email)) {
            return;
        }

        $appName = getOption('app_name') ?: 'Centresidence';
        $first   = e($user->first_name ?: __('there'));

        $this->sendCs(
            [$user->email],
            __('You have a new message') . ' — ' . $this->subject . ' | ' . $appName,
            [
                'eyebrow' => __('A message for you'), 'eyebrowColor' => '#185FA5',
                'title'   => $this->subject,
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hi :name, the :app team has sent you a message:', ['name' => "<strong>{$first}</strong>", 'app' => $appName])],
                    ['type' => 'panel', 'variant' => 'blue', 'title' => $this->subject, 'rows' => [
                        ['k' => __('Message'), 'v' => nl2br(e($this->body))],
                    ]],
                    ['type' => 'button', 'url' => route('support.show', $this->ticketId), 'label' => __('View & reply')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>"
                        . __('Log in to read it in your support inbox and reply there.') . '</span>'],
                ],
            ]
        );
    }
}
