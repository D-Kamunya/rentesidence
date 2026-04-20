<?php
namespace App\Jobs\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\SmsMail\MailService;

abstract class BaseMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30; // seconds between retries

    protected function send(array $recipients, string $subject, string $body): void
    {
        if (getOption('send_email_status', 0) != ACTIVE) {
            return;
        }
        MailService::sendCustomizeMail($recipients, $subject, $body);
    }
}