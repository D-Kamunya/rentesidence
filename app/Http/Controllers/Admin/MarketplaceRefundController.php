<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use App\Services\CommissionService;

/**
 * Admin green-light for marketplace refunds. Marketplace money is held by the platform, so the
 * B2C payout back to the buyer is gated behind an admin approval here (security). The actual
 * money movement + ledger reversal happen in CommissionService (async, M-Pesa-confirmed).
 */
class MarketplaceRefundController extends Controller
{
    public function index()
    {
        $refunds = ProductOrder::with(['user', 'orderItems.product'])
            ->whereIn('refund_status', [REFUND_STATUS_REQUESTED, REFUND_STATUS_PROCESSING, REFUND_STATUS_FAILED])
            ->orderByRaw("FIELD(refund_status, '" . REFUND_STATUS_REQUESTED . "', '" . REFUND_STATUS_FAILED . "', '" . REFUND_STATUS_PROCESSING . "')")
            ->latest()
            ->paginate(20);

        return view('admin.marketplace.refunds', [
            'pageTitle' => __('Marketplace Refunds'),
            'refunds'   => $refunds,
        ]);
    }

    /** Green-light a requested (or retry a failed) refund → fire the B2C payout to the buyer. */
    public function approve($id, CommissionService $commissions)
    {
        $order = ProductOrder::findOrFail($id);

        $result = $commissions->approveAndSendRefund($order);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
