<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use App\Http\Requests\AffiliateRegisterRequest;
use App\Models\Affiliate;
use App\Models\User;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AffiliateController extends Controller
{
    public $affiliateService;
    public function __construct()
    {
        $this->affiliateService = new AffiliateService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->affiliateService->getAllData($request);
        } else {
            $data['pageTitle'] = __('Affiliates');
            return view('admin.affiliates.index', $data);
        }
    }

    public function affiliate_register_form()
    {
        $data['pageTitle'] = __('Add Affiliate');
        $data['navAffiliatesAddMMShowClass'] = 'active';
        return view('admin.affiliates.add', $data);
    }

    public function affiliate_register_store(AffiliateRegisterRequest $request)
    {
        // registerAffiliate owns its own transaction now (single source of truth), so the
        // controller just surfaces the outcome.
        try {
            $this->affiliateService->registerAffiliate($request->validated());
            return back()->with('success', __("AFFILIATE REGISTERED SUCCESSFULLY"));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Suspend an affiliate (breach of operational rules). Reversible.
     * Blocks their access at the affiliate middleware; earned commissions,
     * referrals and history are preserved.
     */
    public function suspend($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $affiliate->status = AFFILIATE_STATUS_INACTIVE;
        $affiliate->save();

        return back()->with('success', __('Affiliate suspended successfully.'));
    }

    /**
     * Reinstate a suspended affiliate — restores platform access.
     */
    public function reinstate($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $affiliate->status = AFFILIATE_STATUS_ACTIVE;
        $affiliate->save();

        return back()->with('success', __('Affiliate reinstated successfully.'));
    }

    /**
     * Archive (soft-delete) an affiliate — for cleaning up stale/test records so the register
     * stays accurate. Guarded HARD on money: refuses while the affiliate has any unpaid balance
     * or an in-flight (pending/processing) withdrawal, so funds are never orphaned. The soft
     * delete preserves commission + referral history for audit; reversible via restore.
     */
    public function destroy($id)
    {
        $affiliate = Affiliate::findOrFail($id);

        $balance = app(\App\Services\AffiliateCommissionService::class)->getAvailableBalance((int) $affiliate->id);
        if ($balance > 0) {
            return back()->with('error', __('Can\'t archive — this affiliate still has an unpaid balance of :amt. Settle or zero it first.', ['amt' => number_format($balance, 2)]));
        }

        $inFlight = \App\Models\AffiliateWithdrawal::where('affiliate_id', $affiliate->id)
            ->whereIn('status', [AFFILIATE_WITHDRAWAL_PENDING, AFFILIATE_WITHDRAWAL_PROCESSING])
            ->exists();
        if ($inFlight) {
            return back()->with('error', __('Can\'t archive — this affiliate has a withdrawal in progress. Wait for it to clear first.'));
        }

        $affiliate->status = AFFILIATE_STATUS_INACTIVE;
        $affiliate->save();
        $affiliate->delete(); // soft delete — archived; history kept

        return back()->with('success', __('Affiliate archived. Their commission history is kept for audit.'));
    }
}
