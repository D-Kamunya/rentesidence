<?php

namespace App\Jobs;

use App\Mail\Concerns\SendsCsMail;
use App\Models\Owner;
use App\Models\ProductOrder;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Seller-side alert when a marketplace order is PAID and awaiting fulfilment. Fires once from
 * CommissionService::holdOnPayment() (the single null→HELD escrow transition, across every payment
 * path). Tells the OWNER (seller) to organize dispatch, and — when the owner delegates dispatch to
 * the on-site caretaker (owners.caretaker_dispatch_enabled) — the MAINTAINER for the buyer's
 * property too, so an offline seller/caretaker still acts. The buyer already gets their own
 * payment-confirmation email/notification elsewhere; this is purely the seller side.
 *
 * Channels: owner in-app + owner email (CS layout) + owner SMS; maintainer in-app + maintainer SMS.
 * Both SMS are carved from the OWNER's SMS credit pool (the maintainer acts for the owner) — the
 * per-number balance pre-flight lives in AdvantaSmsService::sendSms, so a credit-less owner simply
 * gets in-app + email and the SMS is skipped (never a silent total drop). SMS stay link-free and
 * ≤1 segment for cost discipline; the email carries the detail + deep link.
 *
 * tries=1 and every channel is wrapped so a partial failure never retries and re-charges an SMS.
 */
class SendSellerDispatchAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsCsMail;

    public int $tries = 1;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = ProductOrder::with(['orderItems.product', 'user'])->find($this->orderId);
        if (! $order) {
            return;
        }
        // Guard against a payment rollback race: only alert on a genuinely paid order.
        if ((int) $order->payment_status !== PRODUCT_ORDER_STATUS_PAID) {
            return;
        }

        $firstProduct = $order->orderItems->first()?->product;
        $ownerRecord  = $firstProduct ? Owner::find($firstProduct->owner_user_id) : null;
        if (! $ownerRecord || ! $ownerRecord->user_id) {
            return;
        }
        $ownerUserId = (int) $ownerRecord->user_id;
        $ownerUser   = User::find($ownerUserId);

        $appName   = getOption('app_name') ?: 'Centresidence';
        $orderRef  = $order->order_id;
        $ordersUrl = route('owner.order.index');
        $buyerName = $order->user->name ?? '';

        // ── Owner: in-app (always) ────────────────────────────────────────
        try {
            addNotification(
                __('New paid order — organize dispatch'),
                __('Order #:id has been paid and is ready to dispatch.', ['id' => $orderRef]),
                $ordersUrl,
                null,
                $ownerUserId,
            );
        } catch (\Throwable $e) {
            Log::error('Seller dispatch alert: owner in-app failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        // ── Owner: email on the CS layout (always — free) ─────────────────
        try {
            if ($ownerUser?->email) {
                $ownerFirst = e($ownerUser->first_name ?: __('there'));
                $this->sendCs(
                    [$ownerUser->email],
                    __('New paid order #:id — ready to dispatch', ['id' => $orderRef]),
                    [
                        'eyebrow' => __('Marketplace'), 'eyebrowColor' => '#0F6E56',
                        'title'   => __('A new order is paid and ready to dispatch'),
                        'blocks'  => [
                            ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$ownerFirst}</strong>"])
                                . ' ' . __('A marketplace order has been paid and is waiting to be dispatched.')],
                            ['type' => 'panel', 'variant' => 'green', 'title' => __('Order details'), 'rows' => array_values(array_filter([
                                ['k' => __('Order'),  'v' => $orderRef, 'mono' => true],
                                ['k' => __('Amount'), 'v' => currencyPrice($order->transaction_amount ?? $order->amount), 'amount' => true],
                                ['k' => __('Buyer'),  'v' => $buyerName],
                            ], fn ($r) => $r['v'] !== '' && $r['v'] !== null))],
                            ['type' => 'text', 'html' => $ownerRecord->caretaker_dispatch_enabled
                                ? __('Your on-site caretaker has also been notified to handle dispatch.')
                                : __('Please arrange dispatch to the buyer.')],
                            ['type' => 'button', 'url' => $ordersUrl, 'label' => __('View orders')],
                        ],
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error('Seller dispatch alert: owner email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        // ── Owner: SMS (carved from owner credits; link-free, 1 segment) ──
        try {
            if (! empty($ownerUser?->contact_number)) {
                $msg = __('New paid order #:id on :app. Log in to organize dispatch.', ['id' => $orderRef, 'app' => $appName]);
                SendSmsJob::dispatch([$ownerUser->contact_number], $msg, $ownerUserId);
            }
        } catch (\Throwable $e) {
            Log::error('Seller dispatch alert: owner SMS failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        // ── Maintainer (only when the owner delegates dispatch) ───────────
        if (! $ownerRecord->caretaker_dispatch_enabled) {
            return;
        }

        try {
            $buyerTenant      = Tenant::where('user_id', $order->user_id)->first();
            $maintainerUserId = $buyerTenant ? (int) optional(Property::find($buyerTenant->property_id))->maintainer_id : 0;
            $maintainerUser   = $maintainerUserId ? User::find($maintainerUserId) : null;

            if ($maintainerUser) {
                // Maintainer in-app → their dispatch queue.
                try {
                    addNotification(
                        __('Order to dispatch'),
                        __('Order #:id is paid and ready to dispatch.', ['id' => $orderRef]),
                        route('maintainer.dispatch.index'),
                        null,
                        $maintainerUserId,
                    );
                } catch (\Throwable $e) {
                    Log::error('Seller dispatch alert: maintainer in-app failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                }

                // Maintainer SMS — carved from the OWNER's credits (maintainer acts for the owner).
                if (! empty($maintainerUser->contact_number)) {
                    $msg = __('Order #:id is paid and ready to dispatch. Check your dispatch queue.', ['id' => $orderRef]);
                    SendSmsJob::dispatch([$maintainerUser->contact_number], $msg, $ownerUserId);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Seller dispatch alert: maintainer notify failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
