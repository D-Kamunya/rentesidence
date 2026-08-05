<?php

namespace App\Http\Controllers\Owner;

use App\Centresidence\Services\InfraBillPaymentService;
use App\Http\Controllers\Controller;

/**
 * Owner action to pay their outstanding module-infrastructure bill via M-Pesa STK.
 * NOT gated by the readonly middleware (this is the way OUT of readonly).
 */
class InfraBillController extends Controller
{
    public function pay(InfraBillPaymentService $bills)
    {
        $result = $bills->initiate((int) auth()->id());

        if (! ($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? __('We could not start the payment. Please try again.'));
        }

        // 'log' driver settles instantly; 'mpesa' waits for the STK prompt.
        $message = ($result['settled'] ?? false)
            ? ($result['message'] ?? __('Infrastructure bill settled.'))
            : __('Check your phone for the M-Pesa prompt to pay your infrastructure bill.');

        return back()->with('success', $message);
    }
}
