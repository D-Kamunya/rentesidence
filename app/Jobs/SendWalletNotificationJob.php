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
        public WithdrawalRequest|AffiliateWithdrawal|null $withdrawal = null
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

            // ── Email notification ───────────────────────────────────
            
            if ($this->recipient->email) {
                Mail::send([], [], function ($message) {
                    $message->to($this->recipient->email)
                            ->subject($this->emailData->subject)
                            ->html(
                                '<p>Hello ' . e($this->recipient->name) . ',</p>' .
                                '<p>' . e($this->emailData->message) . '</p>' .
                                '<p><a href="' . $this->notificationData->url . '">View your wallet</a></p>'
                            );
                });
            }

            // ── SMS notification ─────────────────────────────────────

            $phone = $this->recipient->contact_number 
            ?: getOption('app_contact_number');
        
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