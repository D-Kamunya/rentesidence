<?php

namespace App\Centresidence\Listeners;

use App\Centresidence\Events\ApplicationSubmitted;
use App\Centresidence\Models\FinancePartner;
use App\Models\User;

/**
 * In-app notification to the finance partner when an owner submits an application,
 * so they know to review it (partners had no in-app notifications before).
 */
class NotifyPartnerOnApplicationSubmitted
{
    public function handle(ApplicationSubmitted $event): void
    {
        // A notification failure must never block the owner's submission.
        try {
            $this->notify($event);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Partner submit notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function notify(ApplicationSubmitted $event): void
    {
        $app = $event->application;
        $partnerUserId = optional(FinancePartner::find($app->finance_partner_id))->user_id;
        if (! $partnerUserId || ! function_exists('addNotification')) {
            return;
        }

        addNotification(
            __('New financing application'),
            __(':owner applied to finance :module.', [
                'owner'  => optional(User::find($app->owner_id))->name ?? __('An owner'),
                'module' => optional($app->module)->name ?? __('a module'),
            ]),
            route('finance-partner.applications.show', $app->id),
            null,
            $partnerUserId,   // recipient (partner)
            $app->owner_id    // sender (owner) — required by the notification join
        );
    }
}
