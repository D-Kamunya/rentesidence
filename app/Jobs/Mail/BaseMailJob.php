<?php
namespace App\Jobs\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LifecycleMail;
use App\Services\SmsMail\MailService;

abstract class BaseMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30; // seconds between retries

    /**
     * Legacy raw-HTML send. RETIRED for lifecycle mails — use sendCs() so every message
     * renders through the CS parts. Kept only for any non-lifecycle caller still passing
     * owner-custom HTML through the customize wrapper.
     *
     * @deprecated Prefer sendCs() with structured blocks.
     */
    protected function send(array $recipients, string $subject, string $body): void
    {
        if (getOption('send_email_status', 0) != ACTIVE) {
            return;
        }
        MailService::sendCustomizeMail($recipients, $subject, $body);
    }

    /**
     * CS lifecycle send: renders the reusable mail.lifecycle body from a structured data
     * array (eyebrow/title/blocks/…) and sends it DIRECTLY as a LifecycleMail — not through
     * sendCustomizeMail, which would wrap an already-complete CS shell inside another shell.
     * Mirrors the send gates of send()/sendCustomizeMail exactly.
     */
    protected function sendCs(array $recipients, string $subject, array $data): void
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
                Mail::to($email)->send(new LifecycleMail($subject, $data));
                Log::channel('sms-mail')->info('email : ' . $email . ', subject : ' . $subject . ', date : ' . date('d-m-Y'));
            } catch (\Throwable $e) {
                Log::channel('sms-mail')->info($e->getMessage());
            }
        }
    }
}