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

        $status = in_array($request->status, [
            TenantDeposit::STATUS_HELD, TenantDeposit::STATUS_REFUNDED,
            TenantDeposit::STATUS_APPLIED, TenantDeposit::STATUS_SETTLED,
        ], true) ? $request->status : null;

        $data = [
            'pageTitle'                 => __('Deposits Held'),
            'navTenantMMShowClass'      => 'mm-show',
            'subNavDepositMMActiveClass'=> 'mm-active',
            'subNavDepositActiveClass'  => 'active',
            'totalHeld'                 => $svc->totalHeldForOwner($ownerId),
            'heldCount'                 => $svc->heldTenantCountForOwner($ownerId),
            'statusFilter'              => $status,
            'deposits'                  => $svc->ownerDepositsQuery($ownerId, ['status' => $status])
                                              ->paginate(15)->appends($request->query()),
        ];

        return view('owner.deposits.index', $data);
    }
}
