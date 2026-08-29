<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OwnerCreditTransaction;
use App\Models\TenantScreeningLookup;
use App\Services\Credit\CreditService;
use App\Services\Screening\ScreeningLookupService;
use Illuminate\Http\Request;

/**
 * Owner "Tenant Screening" — enter a phone, run a credit-metered lookup, and see the person's
 * OBJECTIVE aggregated rental score + summary (Step 4 of the Global Tenant ID). Screening rides
 * the unified credit rail as the `screening` bucket, so top-ups reuse CreditTopUpController and
 * the shared STK flow. Bureau + transparency posture: the objective record is not gated behind
 * the tenant's consent, but every lookup is logged and visible to the tenant.
 */
class OwnerScreeningController extends Controller
{
    public function index(Request $request)
    {
        return view('owner.screening.index', $this->pageData($request));
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:9|max:20',
        ]);

        try {
            $result = app(ScreeningLookupService::class)->screen(auth()->id(), $request->phone);
        } catch (\Throwable $e) {
            // AJAX (the in-flow modal on tenant-create) — a thrown gate means out of coverage.
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'topup' => true, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok'   => true,
                'html' => view('owner.screening.partials.report', ['result' => $result])->render(),
            ]);
        }

        return view('owner.screening.index', $this->pageData($request, $result));
    }

    /**
     * Shared page data. $result (when present) is the just-run lookup to render as a report.
     */
    private function pageData(Request $request, ?array $result = null): array
    {
        $svc         = app(ScreeningLookupService::class);
        $eligibility = $svc->eligibility(auth()->id());
        $unlimited   = ScreeningLookupService::ownerHasUnlimited(auth()->id());
        $allowance   = ScreeningLookupService::freeMonthlyAllowance(auth()->id());

        $balance = CreditService::balance('screening', auth()->id());
        $price   = CreditService::pricePerUnit('screening');

        $history = TenantScreeningLookup::where('owner_user_id', auth()->id())
            ->latest()
            ->paginate(10, ['*'], 'hist_page');

        $transactions = OwnerCreditTransaction::where('owner_user_id', auth()->id())
            ->where('bucket', 'screening')
            ->latest()
            ->paginate(10, ['*'], 'tx_page');

        return compact('eligibility', 'unlimited', 'allowance', 'balance', 'price', 'history', 'transactions', 'result');
    }
}
