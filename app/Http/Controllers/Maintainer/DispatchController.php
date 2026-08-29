<?php

namespace App\Http\Controllers\Maintainer;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\ProductOrder;
use App\Models\Tenant;
use App\Services\DispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The caretaker's dispatch queue — paid marketplace orders bought by tenants on the properties they
 * manage, awaiting dispatch/delivery. Available only when the owner has left caretaker dispatch ON
 * (owners.caretaker_dispatch_enabled). Strictly scoped to the maintainer's properties (IDOR-safe).
 */
class DispatchController extends Controller
{
    public function index()
    {
        $enabled = $this->caretakerDispatchEnabled();
        $orders  = collect();

        if ($enabled) {
            $orders = ProductOrder::with(['user', 'orderItems.product'])
                ->whereIn('user_id', $this->scopedTenantUserIds())
                ->where('payment_status', PRODUCT_ORDER_STATUS_PAID)
                ->where('fulfilment_status', '<', FULFILMENT_DELIVERED)
                ->where('order_status', ORDER_STATUS_PENDING) // exclude owner-completed / cancelled orders
                ->latest()
                ->paginate(30);
        }

        return view('maintainer.dispatch.index', [
            'orders'    => $orders,
            'enabled'   => $enabled,
            'pageTitle' => __('Dispatch'),
        ]);
    }

    public function dispatchOrder(Request $request, $id)
    {
        $res = app(DispatchService::class)->markDispatched($this->findScopedOrder($id), auth()->id());
        return back()->with($res['ok'] ? 'success' : 'error', $res['message']);
    }

    public function deliver(Request $request, $id)
    {
        $res = app(DispatchService::class)->markDelivered($this->findScopedOrder($id), auth()->id());
        return back()->with($res['ok'] ? 'success' : 'error', $res['message']);
    }

    /** Tenant user_ids on the maintainer's assigned properties. */
    private function scopedTenantUserIds(): Collection
    {
        $propertyIds = optional(auth()->user()->maintainer)->properties->pluck('id') ?? collect();
        return Tenant::whereIn('property_id', $propertyIds)->pluck('user_id')->unique();
    }

    private function caretakerDispatchEnabled(): bool
    {
        return (bool) Owner::where('user_id', auth()->user()->owner_user_id)->value('caretaker_dispatch_enabled');
    }

    private function findScopedOrder($id): ProductOrder
    {
        abort_unless($this->caretakerDispatchEnabled(), 403);
        return ProductOrder::whereIn('user_id', $this->scopedTenantUserIds())->findOrFail($id);
    }
}
