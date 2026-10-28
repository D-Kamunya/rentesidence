<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * The public face of scan-to-pay: a tenant scans the QR on their unit (a STABLE per-tenant token),
 * and this resolves their CURRENT unpaid rent invoice and hands straight off to the existing,
 * battle-tested instant-pay flow (the public pay page → M-Pesa STK → server-side reconciliation →
 * receipt). No new money path — this only routes to the current invoice; the actual payment is the
 * same instant-pay the rent SMS/email links already use.
 */
class ScanToPayController extends Controller
{
    public function resolve(string $token)
    {
        $tenant = Tenant::with('user')->where('pay_token', $token)->first();

        if (! $tenant || (int) $tenant->status === TENANT_STATUS_CLOSE) {
            return response()->view('scan-pay.notice', [
                'pageTitle' => __('Payment code inactive'),
                'heading'   => __('This payment code isn’t active'),
                'message'   => __('The tenancy may have ended. Please check with your landlord.'),
            ], 410);
        }

        // Oldest unpaid rent invoice first — clear the furthest-behind due.
        $invoice = Invoice::where('tenant_id', $tenant->id)
            ->where('status', INVOICE_STATUS_PENDING)
            ->orderBy('due_date')
            ->first();

        if (! $invoice) {
            return view('scan-pay.notice', [
                'pageTitle' => __('All paid up'),
                'heading'   => __('You’re all paid up 🎉'),
                'message'   => __('There’s nothing due right now. Scan again when your next rent invoice is issued.'),
            ]);
        }

        // The instant-pay flow requires a LIVE per-invoice token (it expires each cycle). Refresh it
        // only when missing/expired, so a previously-sent SMS/email pay link for this invoice still
        // works where it's still valid.
        $expired = empty($invoice->payment_token)
            || empty($invoice->payment_token_expires_at)
            || Carbon::parse($invoice->payment_token_expires_at)->isPast();

        if ($expired) {
            $invoice->payment_token = (string) Str::uuid();
            $invoice->payment_token_expires_at = invoicePayTokenExpiry($invoice->due_date);
            $invoice->save();
        }

        return redirect()->route('instant.invoice.pay', ['token' => $invoice->payment_token]);
    }
}
