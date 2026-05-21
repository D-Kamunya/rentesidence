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
use Illuminate\Support\Facades\Mail;

class SendSmsCreditsEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

            // ── Email notification ───────────────────────────────────
            if ($this->recipient->email) {
                Mail::send([], [], function ($message) {
                    $message->to($this->recipient->email)
                            ->subject($this->emailData->subject)
                            ->html(
                                '<p>' . __('Hello') . ' ' . e($this->recipient->name) . ',</p>' .
                                '<p>' . e($this->emailData->message) . '</p>' .
                                '<p><a href="' . e($this->notificationData->url) . '">' .
                                    __('Manage SMS Credits') .
                                '</a></p>'
                            );
                });
            }

        } catch (\Exception $e) {
            Log::error('SendSmsCreditsEmailJob failed: ' . $e->getMessage(), [
                'recipient_id' => $this->recipient->id,
            ]);
        }
    }
}