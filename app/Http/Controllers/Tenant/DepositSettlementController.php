<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\DepositSettlement;
use App\Services\DepositSettlementService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class DepositSettlementController extends Controller
{
    use ResponseTrait;

    /** Tenant confirms receipt of / disputes a recorded deposit settlement. */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:confirm,dispute',
            'note'   => 'nullable|string|max:1000',
        ]);

        $tenant = optional(auth()->user())->tenant;
        if (!$tenant) {
            return $this->error([], __('No tenancy found.'));
        }

        // Scope: the settlement must belong to THIS tenant.
        $settlement = DepositSettlement::where('id', $id)->where('tenant_id', $tenant->id)->first();
        if (!$settlement) {
            return $this->error([], __('Settlement not found.'));
        }

        $res = app(DepositSettlementService::class)->respond($settlement, $tenant, $request->action, $request->note);

        return $res['ok']
            ? $this->success(['status' => $res['status'] ?? null], $res['message'])
            : $this->error([], $res['message']);
    }
}
