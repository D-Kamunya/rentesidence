<?php

namespace App\Http\Controllers\Maintainer;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Owner;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * On-the-ground rent visibility for the caretaker (maintainer). READ-ONLY by default: they SEE who
 * has paid and who is in arrears across the properties they manage — so they can verify a tenant's
 * "I've paid" claim and follow up on unpaid units without asking the owner.
 *
 * The ONLY write path is the owner-gated cash confirmation (owners.caretaker_can_confirm_rent, off by
 * default): when the owner has delegated it, the caretaker can confirm that a tenant paid a specific
 * invoice in cash. That records a cash payment on the owner's cash gateway, attributes it to the
 * caretaker (orders.confirmed_by_user_id) and notifies the owner. Everything is scoped strictly to
 * the maintainer's assigned properties (properties.maintainer_id) — IDOR-safe.
 */
class RentController extends Controller
{
    /** Owner has delegated cash-payment confirmation to their caretaker. */
    private function canConfirm(): bool
    {
        return (bool) Owner::where('user_id', auth()->user()->owner_user_id)->value('caretaker_can_confirm_rent');
    }

    /** Property ids assigned to the signed-in caretaker. */
    private function scopedPropertyIds()
    {
        return optional(auth()->user()->maintainer)->properties->pluck('id') ?? collect();
    }

    public function index(Request $request)
    {
        $propertyIds = $this->scopedPropertyIds();

        $query = Tenant::query()
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->leftJoin(DB::raw('(select tenant_id, SUM(amount) as due from invoices where status = 0 AND deleted_at IS NULL group By tenant_id) as inv'), ['inv.tenant_id' => 'tenants.id'])
            ->leftJoin(DB::raw('(select tenant_id, MAX(updated_at) as last_payment from invoices where status = 1 AND deleted_at IS NULL group By tenant_id) as inv_last'), ['inv_last.tenant_id' => 'tenants.id'])
            ->select([
                'tenants.*', 'inv.due', 'inv_last.last_payment',
                'users.first_name', 'users.last_name', 'users.contact_number',
                'property_units.unit_name', 'properties.name as property_name',
            ])
            ->whereIn('tenants.property_id', $propertyIds)
            ->where('tenants.status', TENANT_STATUS_ACTIVE);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('users.first_name', 'like', "%$term%")
                  ->orWhere('users.last_name', 'like', "%$term%")
                  ->orWhere('properties.name', 'like', "%$term%")
                  ->orWhere('property_units.unit_name', 'like', "%$term%");
            });
        }
        if ($request->filled('status') && in_array($request->status, ['paid', 'unpaid'], true)) {
            $request->status === 'unpaid'
                ? $query->where('inv.due', '>', 0)
                : $query->where(function ($q) { $q->whereNull('inv.due')->orWhere('inv.due', '<=', 0); });
        }

        $tenants = $query->orderBy('properties.name')->paginate(50)->withQueryString();

        // Headline counts for the caretaker (their patch only).
        $baseIds = $propertyIds;
        $unpaidCount = Tenant::where('status', TENANT_STATUS_ACTIVE)->whereIn('property_id', $baseIds)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('invoices')
                  ->whereColumn('invoices.tenant_id', 'tenants.id')
                  ->where('invoices.status', 0)->whereNull('invoices.deleted_at');
            })->count();
        $totalCount = Tenant::where('status', TENANT_STATUS_ACTIVE)->whereIn('property_id', $baseIds)->count();

        return view('maintainer.rent.index', [
            'tenants'     => $tenants,
            'unpaidCount' => $unpaidCount,
            'totalCount'  => $totalCount,
            'canConfirm'  => $this->canConfirm(),
            'pageTitle'   => __('Rent & Payments'),
        ]);
    }

    /** Unpaid invoices for one scoped tenant — feeds the confirm modal. */
    public function invoices(Request $request)
    {
        abort_unless($this->canConfirm(), 403);

        $tenant = Tenant::whereIn('property_id', $this->scopedPropertyIds())->findOrFail($request->tenant_id);

        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->where('status', INVOICE_STATUS_PENDING)
            ->whereNull('deleted_at')
            ->orderBy('due_date')
            ->get(['id', 'invoice_no', 'name', 'month', 'amount']);

        return response()->json([
            'success'  => true,
            'invoices' => $invoices->map(fn ($i) => [
                'id'         => $i->id,
                'invoice_no' => $i->invoice_no,
                'label'      => $i->name ?: $i->month,
                'month'      => $i->month,
                'amount'     => currencyPrice($i->amount),
            ]),
        ]);
    }

    /**
     * Confirm a specific invoice was paid in cash. Mirrors the owner's cash paymentStatusChange but
     * gated + scoped + attributed to the caretaker; the owner is notified so the cash stays visible.
     */
    public function confirm(Request $request)
    {
        abort_unless($this->canConfirm(), 403);

        $ownerUserId = auth()->user()->owner_user_id;

        // Scope: the invoice must be for this owner AND on a property the caretaker manages AND unpaid.
        $invoice = Invoice::where('owner_user_id', $ownerUserId)
            ->whereIn('property_id', $this->scopedPropertyIds())
            ->where('status', INVOICE_STATUS_PENDING)
            ->findOrFail($request->invoice_id);

        $gateway = Gateway::where(['owner_user_id' => $ownerUserId, 'slug' => 'cash', 'status' => ACTIVE])->first();
        if (! $gateway) {
            return response()->json(['success' => false, 'message' => __('Cash payments aren’t set up for this account yet. Ask the owner to enable a cash gateway.')], 422);
        }
        $gatewayCurrency = GatewayCurrency::where(['owner_user_id' => $ownerUserId, 'gateway_id' => $gateway->id, 'currency' => 'KES'])->first();

        DB::beginTransaction();
        try {
            $order = Order::find($invoice->order_id);
            if (is_null($order)) {
                $order = Order::create([
                    'user_id'            => optional(optional($invoice->tenant)->user)->id,
                    'invoice_id'         => $invoice->id,
                    'amount'             => $invoice->amount,
                    'system_currency'    => optional(Currency::where('current_currency', 'on')->first())->currency_code,
                    'gateway_id'         => $gateway->id,
                    'gateway_currency'   => optional($gatewayCurrency)->currency ?? 'KES',
                    'conversion_rate'    => 1,
                    'subtotal'           => $invoice->amount,
                    'total'              => $invoice->amount,
                    'transaction_amount' => $invoice->amount,
                    'payment_status'     => INVOICE_STATUS_PENDING,
                ]);
            }

            $order->payment_status = INVOICE_STATUS_PAID;
            $order->transaction_id = str_replace('-', '', uuid_create(UUID_TYPE_RANDOM));
            $order->confirmed_by_user_id = auth()->id(); // attribution: who confirmed the cash
            $order->save();

            $invoice->order_id = $order->id;
            $invoice->status   = INVOICE_STATUS_PAID;
            $invoice->save();

            // Notify the owner — the cash confirmation is never silent.
            addNotification(
                __('Rent payment confirmed by caretaker'),
                __(':name confirmed a cash rent payment of :amt for invoice :inv (:month).', [
                    'name'  => auth()->user()->name,
                    'amt'   => currencyPrice($invoice->amount),
                    'inv'   => $invoice->invoice_no,
                    'month' => $invoice->month,
                ]),
                route('owner.invoice.index'),
                null,
                $ownerUserId,
                auth()->id()
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => __('Payment confirmed. The owner has been notified.')]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Caretaker rent confirm failed', ['invoice_id' => $invoice->id ?? null, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('Could not confirm the payment. Please try again.')], 500);
        }
    }
}
