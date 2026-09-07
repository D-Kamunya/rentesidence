<?php

namespace App\Mail\Concerns;

use App\Mail\LifecycleMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single chokepoint for sending on-brand CS email. Any job/service that needs to send a
 * transactional email renders the reusable mail.lifecycle body (structured blocks through the
 * CS parts) via a LifecycleMail — never hand-rolled HTML. Mirrors the send gates the rest of the
 * app uses (send_email_status + mail.status + smtp username) and validates each recipient.
 *
 * $data => ['eyebrow','eyebrowColor','title','blocks'[],'preheader'?,'footnote'?]  (see mail.lifecycle)
 * $attachments => [ ['path'=>..., 'as'=>..., 'mime'=>...], ... ]  (optional)
 */
trait SendsCsMail
{
    protected function sendCs(array $recipients, string $subject, array $data, array $attachments = []): void
    {
        if (getOption('send_email_status', 0) != ACTIVE) {
            return;
        }
        if (config('mail.status') != 1 || ! config('mail.mailers.smtp.username')) {
            return;
        }

        foreach ($recipients as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            try {
                $mail = new LifecycleMail($subject, $data);
                foreach ($attachments as $a) {
                    if (! empty($a['path']) && is_file($a['path'])) {
                        $mail->attach($a['path'], array_filter([
                            'as'   => $a['as']   ?? null,
                            'mime' => $a['mime'] ?? null,
                        ]));
                    }
                }
                Mail::to($email)->send($mail);
                Log::channel('sms-mail')->info('email : ' . $email . ', subject : ' . $subject . ', date : ' . date('d-m-Y'));
            } catch (\Throwable $e) {
                Log::channel('sms-mail')->info($e->getMessage());
            }
        }
    }
}
