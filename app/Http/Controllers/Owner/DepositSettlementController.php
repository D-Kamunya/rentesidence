<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\DepositSettlement;
use App\Models\Tenant;
use App\Services\DepositSettlementService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class DepositSettlementController extends Controller
{
    use ResponseTrait;

    /** Statement context for the settle-deposit modal (held amount + suggested arrears). */
    public function context(Request $request, $id)
    {
        $tenant = Tenant::with(['user', 'unit'])->where('owner_user_id', auth()->id())->find($id);
        if (!$tenant) {
            return $this->error([], __('Tenant not found.'));
        }
        return $this->success([
            'context'  => app(DepositSettlementService::class)->statementContext($tenant),
        ], '');
    }

    /** Record the deposit settlement. */
    public function store(Request $request, $id)
    {
        $request->validate([
            'deductions'              => 'nullable|array',
            'deductions.*.description'=> 'nullable|string|max:255',
            'deductions.*.amount'     => 'nullable|numeric|min:0',
            'deductions.*.type'       => 'nullable|string',
            'deductions.*.invoice_id' => 'nullable|integer',
            'refund_method'           => 'nullable|string|max:40',
            'refund_reference'        => 'nullable|string|max:100',
            'refund_date'             => 'nullable|date',
            'notes'                   => 'nullable|string|max:1000',
        ]);

        $tenant = Tenant::where('owner_user_id', auth()->id())->find($id);
        if (!$tenant) {
            return $this->error([], __('Tenant not found.'));
        }

        $res = app(DepositSettlementService::class)->record(
            $tenant,
            $request->input('deductions', []),
            $request->refund_method,
            $request->refund_reference,
            $request->refund_date,
            $request->notes
        );

        return $res['ok']
            ? $this->success(['settlement_id' => $res['settlement_id'] ?? null, 'refund' => $res['refund'] ?? 0], $res['message'])
            : $this->error([], $res['message']);
    }

    /** Owner responds to a tenant's reported settlement (not a self-resolve — re-prompts the tenant). */
    public function respond(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:1000']);

        $settlement = DepositSettlement::where('id', $id)->where('owner_user_id', auth()->id())->first();
        if (!$settlement) {
            return $this->error([], __('Settlement not found.'));
        }

        $res = app(DepositSettlementService::class)->ownerRespond($settlement, (int) auth()->id(), $request->note);

        return $res['ok'] ? $this->success([], $res['message']) : $this->error([], $res['message']);
    }
}
