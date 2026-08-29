<?php

namespace App\Centresidence\Listeners;

use App\Centresidence\Events\FacilityDisbursed;
use App\Centresidence\Models\FinancePartner;
use App\Jobs\SendSmsJob;
use App\Models\User;

/**
 * Owner in-app + SMS notification when their facility is disbursed (goes live and
 * starts repaying from rent). The SMS is a PLATFORM (admin) notification —
 * dispatched with a null ownerUserId so it is NOT gated by the owner's SMS credits.
 */
class NotifyOwnerOnFacilityDisbursed
{
    public function handle(FacilityDisbursed $event): void
    {
        // A notification/SMS failure must never roll back the disbursement.
        try {
            $this->notify($event);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Owner disburse notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function notify(FacilityDisbursed $event): void
    {
        $f     = $event->facility;
        $owner = User::find($f->owner_id);
        if (! $owner) {
            return;
        }

        $ref      = $f->facility_number ?? ('#' . $f->id);
        $senderId = optional(FinancePartner::find($f->finance_partner_id))->user_id ?? $f->owner_id;

        if (function_exists('addNotification')) {
            addNotification(
                __('Financing disbursed'),
                __('Facility :ref is live — repayment begins from your next rent.', ['ref' => $ref]),
                route('owner.financing.mine'),
                null,
                $owner->id,
                $senderId
            );
        }

        if (! empty($owner->contact_number)) {
            SendSmsJob::dispatch(
                [$owner->contact_number],
                __('Centresidence: facility :ref has been disbursed and is now live. Repayment begins from your next rent.', ['ref' => $ref]),
                null // platform SMS — ungated
            );
        }
    }
}
