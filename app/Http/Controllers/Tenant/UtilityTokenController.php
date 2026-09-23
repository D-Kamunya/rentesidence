<?php

namespace App\Http\Controllers\Tenant;

use App\Centresidence\Services\TokenPurchaseCollectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Tenant-facing utility token purchase (completion-map C1). Lists the metered
 * modules on the tenant's unit and lets them top up prepaid units via M-Pesa.
 * All authorization lives in TokenPurchaseCollectionService — the controller
 * only resolves the current tenant and hands off.
 */
class UtilityTokenController extends Controller
{
    public function __construct(private TokenPurchaseCollectionService $tokens)
    {
    }

    public function index()
    {
        $data['pageTitle'] = __('Utilities');
        $data['modules']   = $this->tokens->modulesFor((int) auth()->id());

        return view('tenant.utilities.index')->with($data);
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'property_module_id' => ['required', 'integer'],
            'amount'             => ['required', 'numeric', 'min:1'],
        ]);

        $module = $this->tokens->authorizedModule((int) auth()->id(), (int) $request->property_module_id);

        if (! $module) {
            return back()->with('error', __('That utility is not available on your unit.'));
        }

        $result = $this->tokens->initiate($module, (int) auth()->id(), (float) $request->amount);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        if (! empty($result['settled'])) {
            return back()->with('success', __('Units credited to your meter.'));
        }

        return back()->with('success', __('Check your phone to authorise the M-Pesa payment. Your tokens are credited once payment is confirmed.'));
    }
}
