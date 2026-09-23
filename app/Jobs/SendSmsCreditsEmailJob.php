<?php

namespace App\Jobs;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Mail\Concerns\SendsCsMail;

class SendSmsCreditsEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsCsMail;

    /**
     * @param User        $recipient        The owner's user record.
     * @param object      $emailData        Must have ->subject and ->message.
     * @param object      $notificationData Must have ->title, ->body, and ->url.
     */
    public function __construct(
        public User   $recipient,
        public object $emailData,
        public object $notificationData,
    ) {}

    public function handle(): void
    {
        try {
            // ── In-app notification ──────────────────────────────────
            addNotification(
                $this->notificationData->title,
                $this->notificationData->body,
                $this->notificationData->url,
                null,
                $this->recipient->id,
                null,
            );

            // ── Email notification (CS lifecycle layout) ──────────────
            if ($this->recipient->email) {
                $name = e($this->recipient->name);
                $this->sendCs(
                    [$this->recipient->email],
                    $this->emailData->subject,
                    [
                        'eyebrow' => __('SMS credits'), 'eyebrowColor' => '#854F0B',
                        'title'   => $this->notificationData->title ?? $this->emailData->subject,
                        'blocks'  => [
                            ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$name}</strong>"])
                                . ' ' . e($this->emailData->message)],
                            ['type' => 'button', 'url' => $this->notificationData->url, 'label' => __('Manage SMS credits')],
                        ],
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error('SendSmsCreditsEmailJob failed: ' . $e->getMessage(), [
                'recipient_id' => $this->recipient->id,
            ]);
        }
    }
}