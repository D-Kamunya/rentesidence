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
use App\Mail\Concerns\SendsCsMail;

class SendOrderStatusNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsCsMail;

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

            // ── Email notification (CS lifecycle layout) ───────────────────
            if ($recipient->email) {
                $name = e($recipient->name);
                $this->sendCs(
                    [$recipient->email],
                    $this->emailData->subject,
                    [
                        'eyebrow' => __('Order update'), 'eyebrowColor' => '#185FA5',
                        'title'   => $this->notificationData->title ?? $this->emailData->subject,
                        'blocks'  => [
                            ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$name}</strong>"])
                                . ' ' . e($this->emailData->message)],
                            ['type' => 'button', 'url' => $this->notificationData->url, 'label' => __('View your orders')],
                        ],
                    ]
                );
            }

            // ── SMS notification (link-free for cost) ────────────────────
            // The CS email + the in-app notification above already carry the deep-link; appending
            // a full URL here would push the message to multiple segments and burn extra credits.
            if (!empty($recipient->contact_number)) {
                SendSmsJob::dispatch(
                    [$recipient->contact_number],
                    $this->emailData->message,
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