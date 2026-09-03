<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceType;
use App\Models\Tenant;
use App\Models\TenantDeposit;
use Illuminate\Support\Facades\Log;

/**
 * The held-deposit register — the single gateway for recording, totalling, and releasing security
 * deposits held under Model A (owner holds; we keep the record).
 *
 * MONEY INVARIANTS (must never be broken):
 *  - A held deposit is the TENANT's money. It is NEVER commissioned and NEVER counted as the owner's
 *    revenue or as financing collateral. (Commission already excludes it — Invoice::rentPortion()
 *    sums only 'rent' lines — this register keeps the accounting of what's held.)
 *  - "Held" ≠ the owner's spendable balance. Totals here are a LIABILITY, reported separately from
 *    rent collected.
 *  - Record only what is genuinely COLLECTED (a paid deposit), never a merely-invoiced or a cosmetic
 *    configured amount. Recording is idempotent per invoice line so a double-fired payment callback
 *    can't double-count.
 */
class DepositService
{
    /** Invoice-type name that marks a deposit line (mirrors how 'rent' marks rent in rentPortion). */
    public const DEPOSIT_TYPE_NAME = 'Deposit';

    /**
     * The owner's "Deposit" invoice type, self-healed (there is NO default 'Deposit' type — only
     * 'Rent' is seeded). Case-insensitive match so we never create duplicates. Returns null only on
     * a hard failure.
     */
    public function ensureDepositType(int $ownerUserId): ?InvoiceType
    {
        $type = InvoiceType::where('owner_user_id', $ownerUserId)
            ->whereRaw('LOWER(name) = ?', [strtolower(self::DEPOSIT_TYPE_NAME)])
            ->first();
        if ($type) {
            return $type;
        }

        try {
            $type = new InvoiceType();
            $type->owner_user_id = $ownerUserId;
            $type->name          = self::DEPOSIT_TYPE_NAME;
            $type->tax           = 0;
            $type->status        = ACTIVE;
            if (\Illuminate\Support\Facades\Schema::hasColumn('invoice_types', 'is_default')) {
                $type->is_default = false;
            }
            $type->save();
            return $type;
        } catch (\Throwable $e) {
            Log::error('ensureDepositType failed for owner ' . $ownerUserId . ' — ' . $e->getMessage());
            return null;
        }
    }

    /**
     * The deposit amount configured for a tenancy, resolved to an absolute figure. Fixed → the
     * stored amount; percentage → that % of the rent base. Returns 0.0 when no deposit is configured.
     */
    public function configuredDepositAmount($tenant, float $rentBase): float
    {
        $amount = (float) ($tenant->security_deposit ?? 0);
        if ($amount <= 0) {
            return 0.0;
        }
        $isPercent = (int) ($tenant->security_deposit_type ?? TYPE_FIXED) === TYPE_PERCENTAGE;
        return $isPercent ? round($rentBase * $amount / 100, 2) : round($amount, 2);
    }

    /**
     * Has this tenancy already got a deposit in play? True if one is HELD, or sits on an
     * unpaid/pending invoice line — the guard that stops the move-in flow collecting a deposit twice
     * (independent of billing period, since a deposit is one-time per tenancy).
     */
    public function tenantHasDeposit(int $tenantId): bool
    {
        if (TenantDeposit::where('tenant_id', $tenantId)->where('status', TenantDeposit::STATUS_HELD)->exists()) {
            return true;
        }

        return InvoiceItem::whereHas('invoiceType', fn ($q) => $q->whereRaw('LOWER(name) = ?', [strtolower(self::DEPOSIT_TYPE_NAME)]))
            ->whereHas('invoice', fn ($q) => $q->where('tenant_id', $tenantId)->where('status', '!=', INVOICE_STATUS_PAID))
            ->exists();
    }

    /**
     * Turn the PAID deposit line(s) on an invoice into HELD ledger entries — held == collected, so
     * this runs at payment-confirmation time, never at invoice creation. Idempotent per line
     * (recordHeld keys off invoice_item_id). Best-effort: never disturbs the payment flow.
     */
    public function recordFromPaidInvoice(Invoice $invoice): void
    {
        try {
            if (!$invoice->tenant_id) {
                return;
            }
            $tenant = $invoice->tenant instanceof Tenant ? $invoice->tenant : Tenant::find($invoice->tenant_id);
            if (!$tenant) {
                return;
            }

            $depositItems = InvoiceItem::with('invoiceType')
                ->where('invoice_id', $invoice->id)
                ->whereHas('invoiceType', fn ($q) => $q->whereRaw('LOWER(name) = ?', [strtolower(self::DEPOSIT_TYPE_NAME)]))
                ->get();

            foreach ($depositItems as $item) {
                $this->recordHeld($tenant, (float) $item->amount, $invoice, $item);
            }
        } catch (\Throwable $e) {
            Log::error('recordFromPaidInvoice failed for invoice ' . $invoice->id . ' — ' . $e->getMessage());
        }
    }

    /**
     * Record a deposit as HELD for a tenancy. Idempotent by the collecting invoice line: if that
     * line already recorded a deposit, the existing record is returned untouched (no double-count).
     */
    public function recordHeld(Tenant $tenant, float $amount, ?Invoice $invoice = null, ?InvoiceItem $item = null, $heldAt = null): ?TenantDeposit
    {
        if ($amount <= 0) {
            return null;
        }

        if ($item) {
            $existing = TenantDeposit::where('invoice_item_id', $item->id)->first();
            if ($existing) {
                return $existing;
            }
        }

        try {
            return TenantDeposit::create([
                'owner_user_id'    => $tenant->owner_user_id,
                'tenant_id'        => $tenant->id,
                'property_id'      => $tenant->property_id,
                'property_unit_id' => $tenant->unit_id,
                'invoice_id'       => $invoice?->id,
                'invoice_item_id'  => $item?->id,
                'amount'           => round($amount, 2),
                'status'           => TenantDeposit::STATUS_HELD,
                'held_at'          => $heldAt ?: now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('DepositService::recordHeld failed for tenant ' . $tenant->id . ' — ' . $e->getMessage());
            return null;
        }
    }

    /** Total deposits an owner is currently HOLDING (the liability). Excludes released ones. */
    public function totalHeldForOwner(int $ownerId): float
    {
        return (float) TenantDeposit::where('owner_user_id', $ownerId)
            ->where('status', TenantDeposit::STATUS_HELD)
            ->sum('amount');
    }

    /** Total currently held for a single tenancy. */
    public function totalHeldForTenant(int $tenantId): float
    {
        return (float) TenantDeposit::where('tenant_id', $tenantId)
            ->where('status', TenantDeposit::STATUS_HELD)
            ->sum('amount');
    }

    /** How many distinct tenancies the owner is holding a deposit for (for the page header). */
    public function heldTenantCountForOwner(int $ownerId): int
    {
        return (int) TenantDeposit::where('owner_user_id', $ownerId)
            ->where('status', TenantDeposit::STATUS_HELD)
            ->distinct('tenant_id')
            ->count('tenant_id');
    }

    /** Owner-scoped register query for the Deposits Held page (newest first, eager-loaded). */
    public function ownerDepositsQuery(int $ownerId, array $filters = [])
    {
        $q = TenantDeposit::with(['tenant.user', 'unit', 'property'])
            ->where('owner_user_id', $ownerId)
            ->orderByDesc('held_at')
            ->orderByDesc('id');

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q;
    }

    // ── Release transitions (Phase 4 settlement will drive these) ──────────────────────────────
    // Kept here from the start so the ledger's lifecycle is complete and consistent. They only flip
    // a HELD record to a terminal released state; the money movement/reconciliation is Phase 4.

    /** Mark a held deposit as REFUNDED to the tenant. */
    public function markRefunded(TenantDeposit $deposit, ?float $amount = null, ?string $method = null, ?string $notes = null): TenantDeposit
    {
        return $this->release($deposit, TenantDeposit::STATUS_REFUNDED, $amount, $method, $notes);
    }

    /** Mark a held deposit as APPLIED against arrears/damages at move-out. */
    public function markApplied(TenantDeposit $deposit, ?float $amount = null, ?string $notes = null): TenantDeposit
    {
        return $this->release($deposit, TenantDeposit::STATUS_APPLIED, $amount, 'applied-to-arrears', $notes);
    }

    /**
     * Resolve ALL of a tenancy's held deposits via a move-out settlement: mark them 'settled'
     * (they stop counting as held) and distribute the refunded portion across them as released_amount.
     * The itemized truth (deductions) lives on the DepositSettlement; this just discharges the
     * liability. Returns how many deposit records were settled.
     */
    public function settleHeldForTenant(int $tenantId, float $refundAmount): int
    {
        $deposits = TenantDeposit::where('tenant_id', $tenantId)
            ->where('status', TenantDeposit::STATUS_HELD)
            ->get();

        $remaining = round(max(0, $refundAmount), 2);
        $count = 0;
        foreach ($deposits as $d) {
            $give = min((float) $d->amount, $remaining);
            $d->status          = TenantDeposit::STATUS_SETTLED;
            $d->released_amount = round($give, 2);
            $d->released_at     = now();
            $d->save();
            $remaining = round($remaining - $give, 2);
            $count++;
        }
        return $count;
    }

    private function release(TenantDeposit $deposit, string $status, ?float $amount, ?string $method, ?string $notes): TenantDeposit
    {
        $deposit->status          = $status;
        $deposit->released_amount = round($amount ?? (float) $deposit->amount, 2);
        $deposit->release_method  = $method;
        $deposit->notes           = $notes;
        $deposit->released_at     = now();
        $deposit->save();

        return $deposit;
    }
}
