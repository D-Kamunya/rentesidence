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
     * Edit an affiliate's CONTACT DETAILS only (name / email / phone) — for special cases like a
     * demo/typo email or a changed number where the affiliate can't reach their own Profile. The
     * PASSWORD is deliberately NOT here: it stays the affiliate's own domain (they set it on first
     * login and reset it via forgot-password), so an admin can never set or see it.
     */
    public function edit($id)
    {
        $affiliate = Affiliate::with('user')->findOrFail($id);
        if (! $affiliate->user) {
            return redirect()->route('admin.affiliates.index')->with('error', __('This affiliate has no linked account to edit.'));
        }
        $data['pageTitle'] = __('Edit Affiliate');
        $data['affiliate'] = $affiliate;
        $data['user']      = $affiliate->user;
        return view('admin.affiliates.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $affiliate = Affiliate::with('user')->findOrFail($id);
        $user = $affiliate->user;
        if (! $user) {
            return redirect()->route('admin.affiliates.index')->with('error', __('This affiliate has no linked account to edit.'));
        }

        // Uniqueness excludes this user's own row. No password field by design.
        $validated = $request->validate([
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'unique:users,contact_number,' . $user->id],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        try {
            $user->first_name     = $validated['first_name'];
            $user->last_name      = $validated['last_name'];
            $user->contact_number = $validated['contact_number'];
            $user->email          = $validated['email'];
            $user->save();

            return redirect()->route('admin.affiliates.index')->with('success', __('Affiliate details updated.'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Admin view of ONE affiliate's earnings — available balance, lifetime earned, total withdrawn,
     * this-month payout, and a 12-month per-source breakdown. Reachable for EVERY affiliate from the
     * Affiliates list (the Accounts & Withdrawals "View earnings" overlay only lists affiliates who
     * have made a withdrawal, so an affiliate who earned but never withdrew was invisible to admin).
     */
    public function earnings($id)
    {
        $affiliate = \App\Models\Affiliate::with('user')->findOrFail($id);
        $svc = app(\App\Services\AffiliateCommissionService::class);

        $data['pageTitle']          = __('Affiliate Earnings');
        $data['affiliate']          = $affiliate;
        $data['availableBalance']   = $svc->getAvailableBalance((int) $id);
        $data['lifetimeEarned']     = $svc->getLifeTimeGrossCommissions((int) $id);
        $data['totalWithdrawn']     = (float) \App\Models\AffiliateWithdrawal::where('affiliate_id', $id)
            ->where('status', AFFILIATE_WITHDRAWAL_APPROVED)->sum('amount');
        $data['currentMonthPayout'] = $svc->getLatestPeriodPayout((int) $id, (int) now()->format('n'), (int) now()->format('Y'));

        $data['monthly'] = \App\Models\AffiliateCommissionPayment::where('affiliate_id', $id)
            ->whereIn('id', function ($q) use ($id) {
                $q->selectRaw('MAX(id)')->from('affiliate_commission_payments')
                  ->where('affiliate_id', $id)->groupBy('period_year', 'period_month');
            })
            ->orderByDesc('period_year')->orderByDesc('period_month')->take(12)->get()
            ->map(function ($row) {
                $subscription = $row->new_commission_payout + $row->recurring_commission_payout;
                $other = max(0, $row->total_commission_payout - $subscription
                    - $row->rent_commission_payout - $row->marketplace_commission_payout);
                return (object) [
                    'period'       => \Carbon\Carbon::createFromDate($row->period_year, $row->period_month, 1)->format('M Y'),
                    'subscription' => $subscription,
                    'rent'         => $row->rent_commission_payout,
                    'marketplace'  => $row->marketplace_commission_payout,
                    'other'        => $other,
                    'total'        => $row->total_commission_payout,
                ];
            });

        $data['recentWithdrawals'] = \App\Models\AffiliateWithdrawal::where('affiliate_id', $id)
            ->latest()->take(10)->get();

        return view('admin.affiliates.earnings', $data);
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
