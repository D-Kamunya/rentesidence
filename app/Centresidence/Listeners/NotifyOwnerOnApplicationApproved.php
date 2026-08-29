<?php

namespace App\Centresidence\Listeners;

use App\Centresidence\Events\ApplicationApproved;
use App\Centresidence\Models\FinancePartner;
use App\Jobs\SendSmsJob;
use App\Models\User;

/**
 * Owner in-app + SMS notification when their financing application is approved.
 * The SMS is a PLATFORM (admin) notification — dispatched with a null ownerUserId
 * so it is NOT gated by / does not consume the owner's SMS credits.
 */
class NotifyOwnerOnApplicationApproved
{
    public function handle(ApplicationApproved $event): void
    {
        // A notification/SMS failure must never roll back the approval itself.
        try {
            $this->notify($event);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Owner approve notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function notify(ApplicationApproved $event): void
    {
        $app   = $event->application;
        $owner = User::find($app->owner_id);
        if (! $owner) {
            return;
        }

        $ref      = $app->application_number ?? ('#' . $app->id);
        $senderId = optional(FinancePartner::find($app->finance_partner_id))->user_id ?? $app->owner_id;

        if (function_exists('addNotification')) {
            addNotification(
                __('Financing approved'),
                __('Your application :ref was approved for KES :amt.', ['ref' => $ref, 'amt' => number_format((float) $app->approved_amount, 2)]),
                route('owner.financing.mine'),
                null,
                $owner->id,
                $senderId
            );
        }

        if (! empty($owner->contact_number)) {
            SendSmsJob::dispatch(
                [$owner->contact_number],
                __('Centresidence: your financing application :ref has been approved. Your infrastructure will be deployed shortly.', ['ref' => $ref]),
                null // platform SMS — ungated, no owner-credit deduction
            );
        }
    }
}
