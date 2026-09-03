<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\Tenant;
use App\Models\VacationNotice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Tenant notice-to-vacate (Phase 3). Owns the notice-period policy, the earliest-valid move-out
 * computation, and filing a notice (with the "enforce as a default, not a block" rule + owner
 * notification). The required period is snapshot onto each notice so terms are fixed at filing.
 */
class VacationNoticeService
{
    /**
     * Required notice period (days) for an owner. Global policy default today (config-seeded,
     * admin-editable); a per-owner override is a clean future extension — read it here so every
     * caller stays correct when it lands.
     */
    public function noticePeriodDays(int $ownerUserId): int
    {
        $days = (int) getOption('vacation_notice_days', 30);
        return $days > 0 ? $days : 30;
    }

    /** The earliest move-out date that honours the required notice period, counted from today. */
    public function earliestMoveOut(int $ownerUserId, ?Carbon $from = null): Carbon
    {
        $from = $from ? $from->copy()->startOfDay() : Carbon::today();
        return $from->addDays($this->noticePeriodDays($ownerUserId));
    }

    /** Does this tenancy already have a LIVE notice (pending/acknowledged)? The one-notice guard. */
    public function hasActiveNotice(int $tenantId): bool
    {
        return VacationNotice::where('tenant_id', $tenantId)
            ->whereIn('status', VacationNotice::ACTIVE_STATUSES)
            ->exists();
    }

    /** The tenancy's current live notice, if any. */
    public function activeNotice(int $tenantId): ?VacationNotice
    {
        return VacationNotice::where('tenant_id', $tenantId)
            ->whereIn('status', VacationNotice::ACTIVE_STATUSES)
            ->latest('id')
            ->first();
    }

    /**
     * File a tenant's notice to vacate. Early move-out is ALLOWED but flagged (meets_notice=false).
     * Guards one live notice per tenancy. Notifies the owner. Returns a UI-friendly result.
     *
     * @return array{ok:bool,message:string,meets_notice?:bool,notice_id?:int}
     */
    public function fileNotice(Tenant $tenant, $intendedDate, ?string $message = null): array
    {
        if ($this->hasActiveNotice($tenant->id)) {
            return ['ok' => false, 'message' => __('You already have an active notice to vacate.')];
        }

        try {
            $intended = $intendedDate instanceof Carbon
                ? $intendedDate->copy()->startOfDay()
                : Carbon::parse($intendedDate)->startOfDay();
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => __('Choose a valid move-out date.')];
        }

        if ($intended->lt(Carbon::today())) {
            return ['ok' => false, 'message' => __('Choose a move-out date in the future.')];
        }

        $ownerId  = (int) $tenant->owner_user_id;
        $days     = $this->noticePeriodDays($ownerId);
        $earliest = $this->earliestMoveOut($ownerId);
        $meets    = $intended->gte($earliest);

        try {
            $notice = VacationNotice::create([
                'tenant_id'              => $tenant->id,
                'owner_user_id'          => $ownerId,
                'property_id'            => $tenant->property_id,
                'property_unit_id'       => $tenant->unit_id,
                'notice_date'            => Carbon::today(),
                'intended_move_out_date' => $intended,
                'notice_period_days'     => $days,
                'meets_notice'           => $meets,
                'message'                => $message,
                'status'                 => VacationNotice::STATUS_PENDING,
            ]);
        } catch (\Throwable $e) {
            Log::error('fileNotice failed for tenant ' . $tenant->id . ' — ' . $e->getMessage());
            return ['ok' => false, 'message' => __('Could not file your notice. Please try again.')];
        }

        $this->notifyOwner($tenant, $notice);

        return [
            'ok'           => true,
            'message'      => $meets
                ? __('Your notice to vacate has been sent to your landlord.')
                : __('Your notice was sent. Note: it is shorter than the required notice period — your landlord may need to approve it or charge rent through the notice period.'),
            'meets_notice' => $meets,
            'notice_id'    => $notice->id,
        ];
    }

    /**
     * Tell the tenant their notice was ACKNOWLEDGED — in-app bell + email + SMS. Given how weighty a
     * confirmed move-out is, all three channels fire. The SMS is GATED ON THE OWNER's SMS credit
     * pool (SendSmsJob deducts from the owner + no-ops gracefully when they're out — it never blocks
     * the acknowledgement). Best-effort throughout: a comms failure never disturbs the status change.
     */
    public function notifyTenantAcknowledged(VacationNotice $notice): void
    {
        $tenantUser = optional($notice->tenant)->user;
        if (!$tenantUser) {
            return;
        }
        $moveOut = Carbon::parse($notice->intended_move_out_date)->format('d M Y');
        $app     = getOption('app_name') ?: 'Centresidence';

        // Each channel is guarded INDEPENDENTLY — one failing (e.g. an email send throwing) must
        // never suppress the others.
        try {
            addNotification(
                __('Notice to vacate acknowledged'),
                __('Your landlord has acknowledged your notice to vacate. Move-out date:') . ' ' . $moveOut . '.',
                route('tenant.invoice.index'),
                null,
                $tenantUser->id,
                $notice->owner_user_id
            );
        } catch (\Throwable $e) {
            Log::error('vacation-ack bell failed for notice ' . $notice->id . ' — ' . $e->getMessage());
        }

        if (getOption('send_email_status', 0) == ACTIVE && $tenantUser->email) {
            try {
                (new MailService())->sendMail(
                    [$tenantUser->email],
                    $app . ' — ' . __('Notice to vacate acknowledged'),
                    __('Your landlord has acknowledged your notice to vacate.') . ' '
                        . __('Your move-out date is') . ' ' . $moveOut . '. '
                        . __('Please prepare for the move-out inspection and final settlement.'),
                    $notice->owner_user_id
                );
            } catch (\Throwable $e) {
                Log::error('vacation-ack email failed for notice ' . $notice->id . ' — ' . $e->getMessage());
            }
        }

        // SMS on the OWNER's credit pool (SendSmsJob deducts + no-ops gracefully when out).
        if ($tenantUser->contact_number) {
            try {
                $msg = __(':app: your landlord has acknowledged your notice to vacate. Move-out date: :date.', [
                    'app'  => $app,
                    'date' => $moveOut,
                ]);
                SendSmsJob::dispatch([$tenantUser->contact_number], $msg, $notice->owner_user_id);
            } catch (\Throwable $e) {
                Log::error('vacation-ack SMS failed for notice ' . $notice->id . ' — ' . $e->getMessage());
            }
        }
    }

    /** In-app bell (+ best-effort email) to the landlord that a notice was filed. */
    private function notifyOwner(Tenant $tenant, VacationNotice $notice): void
    {
        try {
            $tenantName = trim(optional($tenant->user)->first_name . ' ' . optional($tenant->user)->last_name) ?: __('A tenant');
            $unitLabel  = optional($tenant->unit)->unit_name ?: ('#' . $tenant->unit_id);
            $moveOut    = $notice->intended_move_out_date->format('d M Y');
            $flag       = $notice->meets_notice ? '' : ' ' . __('(shorter than the required notice period)');

            $title = __('Notice to vacate');
            $body  = $tenantName . ' (' . $unitLabel . ') ' . __('intends to move out on') . ' ' . $moveOut . '.' . $flag;
            $url   = route('owner.tenant.details', [$tenant->id, 'tab' => 'payment']);

            addNotification($title, $body, $url, null, $notice->owner_user_id, $tenant->user_id ?? null);

            if (getOption('send_email_status', 0) == ACTIVE) {
                $ownerEmail = optional(\App\Models\User::find($notice->owner_user_id))->email;
                if ($ownerEmail) {
                    (new MailService())->sendMail(
                        [$ownerEmail],
                        getOption('app_name') . ' — ' . $title,
                        $body . ' ' . __('Please review it in your dashboard.'),
                        $notice->owner_user_id
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error('notifyOwner (vacation notice) failed for notice ' . $notice->id . ' — ' . $e->getMessage());
        }
    }
}
