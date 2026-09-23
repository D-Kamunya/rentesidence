<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Saas\FrontendController;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function index(Request $request)
    {
        if (isAddonInstalled('PROTYSAAS') > 1) {
            $frontendController = new FrontendController;
            return $frontendController->index($request);
        }
        return redirect()->route('login');
    }

    public function generateInvoice(Request $request)
    {
        // This is a PUBLIC URL-cron hook (for hosts that can only schedule a URL, not a
        // shell cron). Gate it behind a secret key so it can't be triggered by anyone —
        // the billing run is idempotent, but a public trigger for it is still a red flag.
        // The primary path is the shell scheduler (`schedule:run` runs generate:invoice
        // daily); if you rely on this URL instead, set RECURRING_INVOICE_KEY and call it
        // as `/recurring-generate-invoice?key=<value>`. Fail closed: no key configured, or
        // a mismatch, returns 404 (endpoint effectively disabled).
        $configuredKey = config('app.recurring_invoice_key');
        if (empty($configuredKey) || ! hash_equals((string) $configuredKey, (string) $request->get('key'))) {
            abort(404);
        }

        try {
            Artisan::call('generate:invoice');
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }
}
