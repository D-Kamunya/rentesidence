<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\AffiliateWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWalletNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public object $emailData,
        public object $notificationData,
        public WithdrawalRequest|AffiliateWithdrawal|null $withdrawal = null,
        public bool $sendSms = true   // affiliate flows pass false to stay email-only (SMS cost)
    ) {}

    public function handle(): void
    {
        try {
            // ── In-app notification ──────────────────────────────────
            DB::table('notifications')->insert([
                'title'      => $this->notificationData->title,
                'body'       => $this->notificationData->body,
                'url'        => $this->notificationData->url,
                'is_seen'    => 0,
                'user_id'    => $this->recipient->id,
                'sender_id'  => $this->recipient->id, // system-to-self; adjust if you have a system user id
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ── Email notification (branded CS shell, respects mail gate) ──
            if ($this->recipient->email) {
                \App\Services\SmsMail\MailService::sendMail(
                    [$this->recipient->email],
                    $this->emailData->subject,
                    $this->emailData->message,
                    null
                );
            }

            // ── SMS notification (skipped when sendSms=false, e.g. affiliate) ──
            $phone = $this->sendSms
                ? ($this->recipient->contact_number ?: getOption('app_contact_number'))
                : null;

            if (!empty($phone)) {
                $smsMessage = $this->emailData->message .
                    ' ' . __('View your wallet: ') . $this->notificationData->url;
            
                SendSmsJob::dispatch(
                    [$phone],
                    $smsMessage,
                    $this->recipient->id
                );
            }

        } catch (\Exception $e) {
            Log::error('SendWalletNotificationJob failed: ' . $e->getMessage(), [
                'recipient_id'  => $this->recipient->id,
                'withdrawal_id' => $this->withdrawal?->id,
            ]);
        }
    }
}