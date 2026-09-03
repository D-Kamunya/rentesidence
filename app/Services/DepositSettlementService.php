<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\DepositSettlement;
use App\Models\DepositSettlementItem;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VacationNotice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Move-out deposit settlement (Phase 4, Model A). Builds the owner's settlement statement (held +
 * suggested arrears) and records the settlement: itemized deductions, computed refund, and the
 * ledger discharge. We do NOT move money (owner refunds outside our rails) and we do NOT re-commission
 * (the deposit was never rent income; applying it to arrears is reconciliation, not a fresh payment).
 * Invoice-status reconciliation + income reporting are the Phase-4 "wrap" slice, not here.
 */
class DepositSettlementService
{
    /**
     * The statement context for the settle-deposit modal, or null when there's nothing held to
     * settle. Suggests the tenancy's outstanding invoices as candidate arrears deductions.
     *
     * @return array<string,mixed>|null
     */
    public function statementContext(Tenant $tenant): ?array
    {
        $held = app(DepositService::class)->totalHeldForTenant((int) $tenant->id);
        if ($held <= 0) {
            return null;
        }

        $arrears = Invoice::where('tenant_id', $tenant->id)
            ->where('status', '!=', INVOICE_STATUS_PAID)
            ->orderBy('due_date')
            ->get()
            ->map(fn ($i) => [
                'invoice_id' => $i->id,
                'label'      => ($i->invoice_no ?: ('INV#' . $i->id)) . ($i->month ? ' · ' . $i->month : ''),
                'amount'     => (float) $i->amount,
            ])->values()->all();

        return [
            'tenant_id'          => $tenant->id,
            'held'               => $held,
            'arrears'            => $arrears,
            'currency_symbol'    => getCurrencySymbol(),
            'currency_placement' => getCurrencyPlacement(),
        ];
    }

    /**
     * Record a settlement. $deductions = list of ['type','description','amount','invoice_id'?].
     * refund = held − total deductions (deductions may not exceed what's held). Discharges the
     * held-deposit ledger.
     *
     * @return array{ok:bool,message:string,settlement_id?:int,refund?:float}
     */
    public function record(Tenant $tenant, array $deductions, ?string $method = null, ?string $reference = null, $refundDate = null, ?string $notes = null, ?int $vacationNoticeId = null): array
    {
        $depSvc = app(DepositService::class);
        $held   = $depSvc->totalHeldForTenant((int) $tenant->id);
        if ($held <= 0) {
            return ['ok' => false, 'message' => __('There is no held deposit to settle for this tenant.')];
        }

        // Normalise + validate deduction lines.
        $clean = [];
        $total = 0.0;
        foreach ($deductions as $d) {
            $amount = round((float) ($d['amount'] ?? 0), 2);
            if ($amount <= 0) {
                continue; // skip empty lines
            }
            $desc = trim((string) ($d['description'] ?? '')) ?: __('Deduction');
            $type = in_array(($d['type'] ?? ''), [
                DepositSettlementItem::TYPE_ARREARS, DepositSettlementItem::TYPE_DAMAGE,
                DepositSettlementItem::TYPE_CHARGE, DepositSettlementItem::TYPE_OTHER,
            ], true) ? $d['type'] : DepositSettlementItem::TYPE_OTHER;

            // Only accept an invoice_id that really belongs to this tenant (no cross-tenant linkage).
            $invoiceId = null;
            if (!empty($d['invoice_id'])) {
                $invoiceId = Invoice::where('id', $d['invoice_id'])->where('tenant_id', $tenant->id)->value('id');
            }

            $clean[] = ['type' => $type, 'description' => $desc, 'amount' => $amount, 'invoice_id' => $invoiceId];
            $total += $amount;
        }
        $total = round($total, 2);

        if ($total > $held + 0.001) {
            return ['ok' => false, 'message' => __('Deductions exceed the held deposit. Charge the excess separately — a deposit can only cover up to what is held.')];
        }

        $refund = round($held - $total, 2);

        // Anchor to (and close) the tenant's live notice to vacate, if any — settling the deposit
        // is the last step of the move-out, so the notice is now COMPLETED.
        $activeNotice = app(VacationNoticeService::class)->activeNotice((int) $tenant->id);
        $vacationNoticeId = $vacationNoticeId ?: optional($activeNotice)->id;

        try {
            $settlement = DB::transaction(function () use ($tenant, $held, $total, $refund, $method, $reference, $refundDate, $notes, $vacationNoticeId, $clean, $depSvc) {
                $s = DepositSettlement::create([
                    'tenant_id'          => $tenant->id,
                    'owner_user_id'      => $tenant->owner_user_id,
                    'property_id'        => $tenant->property_id,
                    'property_unit_id'   => $tenant->unit_id,
                    'vacation_notice_id' => $vacationNoticeId,
                    'deposit_held'       => $held,
                    'total_deductions'   => $total,
                    'refund_amount'      => $refund,
                    'refund_method'      => $method,
                    'refund_reference'   => $reference,
                    'refund_date'        => $refundDate ? Carbon::parse($refundDate)->toDateString() : null,
                    'status'             => DepositSettlement::STATUS_RECORDED,
                    'notes'              => $notes,
                    'settled_at'         => now(),
                ]);

                foreach ($clean as $line) {
                    DepositSettlementItem::create([
                        'deposit_settlement_id' => $s->id,
                        'type'                  => $line['type'],
                        'description'           => $line['description'],
                        'amount'                => $line['amount'],
                        'invoice_id'            => $line['invoice_id'],
                    ]);

                    // Arrears covered by the deposit → clear that invoice. Set PAID DIRECTLY (never
                    // via the payment pipeline) so it is NOT commissioned and NO wallet credit is
                    // created — the money is already the owner's; this only reclassifies the held
                    // deposit into the rent it settled. The item->invoice_id link is the audit record.
                    if (!empty($line['invoice_id'])) {
                        Invoice::where('id', $line['invoice_id'])
                            ->where('tenant_id', $tenant->id)
                            ->where('status', '!=', INVOICE_STATUS_PAID)
                            ->update(['status' => INVOICE_STATUS_PAID]);
                    }
                }

                // Discharge the held-deposit ledger (held → settled). Money is NOT moved by us and
                // NOTHING is re-commissioned.
                $depSvc->settleHeldForTenant((int) $tenant->id, $refund);

                return $s;
            });
        } catch (\Throwable $e) {
            Log::error('DepositSettlementService::record failed for tenant ' . $tenant->id . ' — ' . $e->getMessage());
            return ['ok' => false, 'message' => __('Could not record the settlement. Please try again.')];
        }

        // Close the notice to vacate — the move-out is done.
        if ($activeNotice && $activeNotice->status !== VacationNotice::STATUS_COMPLETED) {
            try {
                $activeNotice->status = VacationNotice::STATUS_COMPLETED;
                $activeNotice->save();
            } catch (\Throwable $e) {
                Log::error('completing notice ' . $activeNotice->id . ' on settlement failed — ' . $e->getMessage());
            }
        }

        // Prompt the tenant to confirm receipt / dispute — bell + email + SMS (SMS on owner's pool).
        $this->notifyTenantOfSettlement($settlement, $tenant);

        return [
            'ok'            => true,
            'message'       => __('Deposit settlement recorded.'),
            'settlement_id' => $settlement->id,
            'refund'        => $refund,
        ];
    }

    /**
     * The tenant confirms receipt of / disputes a recorded settlement. Tenant-scoped by the caller.
     * Guarded to a still-RECORDED settlement (no responding twice). Notifies the owner.
     *
     * @return array{ok:bool,message:string,status?:string}
     */
    public function respond(DepositSettlement $settlement, Tenant $tenant, string $action, ?string $note = null): array
    {
        if ((int) $settlement->tenant_id !== (int) $tenant->id) {
            return ['ok' => false, 'message' => __('Settlement not found.')];
        }
        if (!in_array($action, ['confirm', 'dispute'], true)) {
            return ['ok' => false, 'message' => __('Invalid action.')];
        }
        if ($settlement->status === DepositSettlement::STATUS_CONFIRMED) {
            return ['ok' => false, 'message' => __('You have already confirmed this settlement.')];
        }
        // Reporting an issue is only from a fresh (recorded) settlement — no re-reporting. But
        // CONFIRM is allowed from recorded OR reported (disputed), so a tenant can close it the
        // moment they actually receive the refund — that IS the resolution.
        if ($action === 'dispute' && $settlement->status !== DepositSettlement::STATUS_RECORDED) {
            return ['ok' => false, 'message' => __('You have already reported an issue with this settlement.')];
        }

        $settlement->status               = $action === 'confirm' ? DepositSettlement::STATUS_CONFIRMED : DepositSettlement::STATUS_DISPUTED;
        $settlement->tenant_response_note = $action === 'dispute' ? $note : $settlement->tenant_response_note;
        $settlement->tenant_responded_at  = now();
        $settlement->save();

        $this->notifyOwnerOfResponse($settlement, $tenant, $action);

        return [
            'ok'      => true,
            'message' => $action === 'confirm'
                ? __('Thank you — you have confirmed receipt of your deposit settlement.')
                : __('Your report has been sent to your landlord.'),
            'status'  => $settlement->status,
        ];
    }

    /**
     * Owner RESPONDS to a reported settlement — a note (e.g. "re-sent via M-Pesa, code X"). This is
     * NOT a self-resolve: status stays 'disputed' and resolution still needs the TENANT to confirm
     * receipt. It only records the response + stamps owner_responded_at (which clears the owner's
     * action nudge) and re-prompts the tenant.
     *
     * @return array{ok:bool,message:string}
     */
    public function ownerRespond(DepositSettlement $settlement, int $ownerUserId, ?string $note): array
    {
        if ((int) $settlement->owner_user_id !== $ownerUserId) {
            return ['ok' => false, 'message' => __('Settlement not found.')];
        }
        if ($settlement->status !== DepositSettlement::STATUS_DISPUTED) {
            return ['ok' => false, 'message' => __('There is nothing to respond to on this settlement.')];
        }

        $settlement->owner_response_note = $note;
        $settlement->owner_responded_at  = now();
        $settlement->save();

        $this->notifyTenantOwnerResponded($settlement);

        return ['ok' => true, 'message' => __('Your response has been sent to your tenant.')];
    }

    /** Re-prompt the tenant after the owner responds to their report — bell + email + SMS (isolated). */
    private function notifyTenantOwnerResponded(DepositSettlement $settlement): void
    {
        $tenantUser = optional($settlement->tenant)->user;
        if (!$tenantUser) {
            return;
        }
        $app = getOption('app_name') ?: 'Centresidence';

        try {
            addNotification(
                __('Landlord responded to your report'),
                __('Your landlord has responded about your deposit refund. Please confirm receipt once you have it, or follow up with them.'),
                route('tenant.invoice.index'),
                null,
                $tenantUser->id,
                $settlement->owner_user_id
            );
        } catch (\Throwable $e) {
            Log::error('owner-responded bell failed for settlement ' . $settlement->id . ' — ' . $e->getMessage());
        }

        if (getOption('send_email_status', 0) == ACTIVE && $tenantUser->email) {
            try {
                (new MailService())->sendMail(
                    [$tenantUser->email],
                    $app . ' — ' . __('Update on your deposit refund'),
                    __('Your landlord has responded about your deposit refund.')
                        . ($settlement->owner_response_note ? ' "' . $settlement->owner_response_note . '"' : '') . ' '
                        . __('Please sign in to confirm receipt once you have it.'),
                    $settlement->owner_user_id
                );
            } catch (\Throwable $e) {
                Log::error('owner-responded email failed for settlement ' . $settlement->id . ' — ' . $e->getMessage());
            }
        }

        if ($tenantUser->contact_number) {
            try {
                $msg = __(':app: your landlord responded about your deposit refund. Sign in to confirm receipt once you have it.', ['app' => $app]);
                SendSmsJob::dispatch([$tenantUser->contact_number], $msg, $settlement->owner_user_id);
            } catch (\Throwable $e) {
                Log::error('owner-responded SMS failed for settlement ' . $settlement->id . ' — ' . $e->getMessage());
            }
        }
    }

    /** Prompt the tenant (bell + email + SMS on the owner's credit pool). Channels isolated. */
    private function notifyTenantOfSettlement(DepositSettlement $settlement, Tenant $tenant): void
    {
        $tenantUser = optional($tenant)->user;
        if (!$tenantUser) {
            return;
        }
        $app    = getOption('app_name') ?: 'Centresidence';
        $refund = currencyPrice($settlement->refund_amount);

        try {
            addNotification(
                __('Deposit settlement recorded'),
                __('Your landlord recorded your deposit settlement — refund of :amt. Please confirm receipt or raise a concern.', ['amt' => $refund]),
                route('tenant.invoice.index'),
                null,
                $tenantUser->id,
                $settlement->owner_user_id
            );
        } catch (\Throwable $e) {
            Log::error('settlement-notify bell failed for settlement ' . $settlement->id . ' — ' . $e->getMessage());
        }

        if (getOption('send_email_status', 0) == ACTIVE && $tenantUser->email) {
            try {
                (new MailService())->sendMail(
                    [$tenantUser->email],
                    $app . ' — ' . __('Deposit settlement recorded'),
                    __('Your landlord has recorded your deposit settlement with a refund of') . ' ' . $refund . '. '
                        . __('Please sign in to confirm receipt or raise a concern.'),
                    $settlement->owner_user_id
                );
            } catch (\Throwable $e) {
                Log::error('settlement-notify email failed for settlement ' . $settlement->id . ' — ' . $e->getMessage());
            }
        }

        if ($tenantUser->contact_number) {
            try {
                $msg = __(':app: your landlord recorded your deposit settlement — refund :amt. Sign in to confirm receipt or raise a concern.', [
                    'app' => $app, 'amt' => $refund,
                ]);
                SendSmsJob::dispatch([$tenantUser->contact_number], $msg, $settlement->owner_user_id);
            } catch (\Throwable $e) {
                Log::error('settlement-notify SMS failed for settlement ' . $settlement->id . ' — ' . $e->getMessage());
            }
        }
    }

    /** Bell (+ best-effort email) to the owner with the tenant's confirm/dispute response. */
    private function notifyOwnerOfResponse(DepositSettlement $settlement, Tenant $tenant, string $action): void
    {
        try {
            $tenantName = trim(optional($tenant->user)->first_name . ' ' . optional($tenant->user)->last_name) ?: __('Your tenant');
            $title = $action === 'confirm' ? __('Deposit settlement confirmed') : __('Deposit settlement disputed');
            $body  = $action === 'confirm'
                ? $tenantName . ' ' . __('confirmed receipt of their deposit settlement.')
                : $tenantName . ' ' . __('has raised a concern about their deposit settlement.') . ($settlement->tenant_response_note ? ' ' . __('Reason:') . ' ' . $settlement->tenant_response_note : '');
            $url = route('owner.tenant.details', [$settlement->tenant_id, 'tab' => 'payment']);

            addNotification($title, $body, $url, null, $settlement->owner_user_id, $tenant->user_id ?? null);

            if (getOption('send_email_status', 0) == ACTIVE) {
                $ownerEmail = optional(User::find($settlement->owner_user_id))->email;
                if ($ownerEmail) {
                    (new MailService())->sendMail([$ownerEmail], getOption('app_name') . ' — ' . $title, $body, $settlement->owner_user_id);
                }
            }
        } catch (\Throwable $e) {
            Log::error('settlement response owner-notify failed for settlement ' . $settlement->id . ' — ' . $e->getMessage());
        }
    }
}
