<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\TenantDeposit;
use App\Services\DepositService;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /** Owner "Deposits Held" register — what the owner is holding, for whom, since when. */
    public function index(Request $request)
    {
        $ownerId = auth()->id();
        $svc     = app(DepositService::class);

        $due     = $request->status === 'due';
        $pending = $request->status === 'pending';
        $status  = (!$due && !$pending && in_array($request->status, [
            TenantDeposit::STATUS_HELD, TenantDeposit::STATUS_REFUNDED,
            TenantDeposit::STATUS_APPLIED, TenantDeposit::STATUS_SETTLED,
        ], true)) ? $request->status : null;

        // Invoiced-but-unpaid deposits — display only, kept out of the held-liability total.
        $pendingDeposits = $svc->pendingDepositsForOwner($ownerId);

        $data = [
            'pageTitle'                 => __('Deposits Held'),
            'navTenantMMShowClass'      => 'mm-show',
            'subNavDepositMMActiveClass'=> 'mm-active',
            'subNavDepositActiveClass'  => 'active',
            'totalHeld'                 => $svc->totalHeldForOwner($ownerId),
            'heldCount'                 => $svc->heldTenantCountForOwner($ownerId),
            'dueCount'                  => $svc->dueForSettlementCount($ownerId),
            'pendingDeposits'           => $pendingDeposits,
            'pendingTotal'              => (float) $pendingDeposits->sum('amount'),
            'pendingCount'              => $pendingDeposits->count(),
            'statusFilter'              => $due ? 'due' : ($pending ? 'pending' : $status),
            // The held register (unchanged); hidden when the owner is viewing the pending list.
            'deposits'                  => $svc->ownerDepositsQuery($ownerId, $due ? ['due' => true] : ['status' => $status])
                                              ->paginate(15)->appends($request->query()),
        ];

        return view('owner.deposits.index', $data);
    }
}
