<?php
namespace App\Jobs\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\Concerns\SendsCsMail;
use App\Services\SmsMail\MailService;

abstract class BaseMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsCsMail;

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

    // sendCs() is provided by the App\Mail\Concerns\SendsCsMail trait — the single CS chokepoint.
}