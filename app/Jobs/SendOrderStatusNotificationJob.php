<?php

namespace App\Jobs;

use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ProductOrder $order,
        public object $emailData,
        public object $notificationData,
        public ?int $overrideUserId = null
    ) {}

    public function handle(): void
    {
        try {
            $userId    = $this->overrideUserId ?? $this->order->user_id;
            $recipient = User::find($userId);
            if (!$recipient) return;

            // ── In-app notification ──────────────────────────────────────
            DB::table('notifications')->insert([
                'title'      => $this->notificationData->title,
                'body'       => $this->notificationData->body,
                'url'        => $this->notificationData->url,
                'is_seen'    => 0,
                'user_id'    => $recipient->id,
                'sender_id'  => $this->order->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ── Email notification ───────────────────────────────────────
            if ($recipient->email) {
                Mail::send([], [], function ($message) use ($recipient) {
                    $message->to($recipient->email)
                            ->subject($this->emailData->subject)
                            ->html(
                                '<p>Hello ' . e($recipient->name) . ',</p>' .
                                '<p>' . e($this->emailData->message) . '</p>' .
                                '<p><a href="' . $this->notificationData->url . '">View your orders</a></p>'
                            );
                });
            }

            // ── SMS notification ─────────────────────────────────────────
            if (!empty($recipient->contact_number)) {
                $smsMessage = $this->emailData->message .
                    ' ' . __('View your orders: ') . $this->notificationData->url;

                SendSmsJob::dispatch(
                    [$recipient->contact_number],
                    $smsMessage,
                    $this->order->user_id
                );
            }

        } catch (\Exception $e) {
            Log::error('SendOrderStatusNotificationJob failed: ' . $e->getMessage(), [
                'order_id' => $this->order->id,
            ]);
        }
    }

    public static function dispatchToUser(
        User $user,
        ProductOrder $order,
        object $emailData,
        object $notificationData
    ): void {
        dispatch(new self($order, $emailData, $notificationData, $user->id));
    }
}