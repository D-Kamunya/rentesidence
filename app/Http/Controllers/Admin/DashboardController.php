<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\OwnerCreditTransaction;
use App\Models\Package;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\SubscriptionOrder;
use App\Models\Tenant;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['pageTitle'] = __('Dashboard');
        $data['totalOwner'] = User::where('role', USER_ROLE_OWNER)->count();
        $data['totalProperty'] = Property::count();
        $data['totalUnit'] = PropertyUnit::count();
        // Active TENANCIES only — Tenant::count() also counts moved-out (CLOSE) and the new
        // self-registered ownerless "Helper" rows (no unit), which inflated the figure ABOVE
        // total units. "Total tenants" should mean people currently occupying a unit.
        $data['totalTenant'] = Tenant::where('status', TENANT_STATUS_ACTIVE)->count();
        // Free "Tenant Helper" accounts — self-registered tenants with no landlord (owner_user_id null).
        $data['freeTenant'] = User::where('role', USER_ROLE_TENANT)->whereNull('owner_user_id')->count();
        // The rest of the user base, for uptake tracking across every account type.
        $data['totalAffiliate']      = User::where('role', USER_ROLE_AFFILIATE)->count();
        $data['totalMaintainer']     = User::where('role', USER_ROLE_MAINTAINER)->count();
        $data['totalFinancePartner'] = User::where('role', USER_ROLE_FINANCE_PARTNER)->count();

        // Owners by their CURRENT active plan (latest active owner_package per owner) — plan-mix uptake.
        $data['ownersByPackage'] = \App\Models\OwnerPackage::query()
            ->whereIn('owner_packages.id', function ($q) {
                $q->selectRaw('MAX(id)')->from('owner_packages')->where('status', ACTIVE)->groupBy('user_id');
            })
            ->join('packages', 'owner_packages.package_id', '=', 'packages.id')
            ->selectRaw('packages.name as name, COUNT(*) as c')
            ->groupBy('packages.name')
            ->orderByDesc('c')
            ->get();

        // Account growth over time — marketing-response signal.
        $data['signups'] = $this->signupGrowth();

        $data['packages'] = Package::limit(10)->get();
        $data['hasPendingWithdrawals'] = WithdrawalRequest::where('status', 'pending')->exists();
        $data['affiliatePendingCount'] = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_PENDING)->count();
        $data['affiliatePendingAmount'] = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_PENDING)->sum('amount');

        $data['earnings'] = $this->platformEarnings();

        $data['orders'] =  SubscriptionOrder::query()
            ->leftJoin('packages', 'subscription_orders.package_id', '=', 'packages.id')
            ->leftJoin('gateways', 'subscription_orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('users', 'subscription_orders.user_id', '=', 'users.id')
            ->select(['subscription_orders.*', 'packages.name as packageName', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug'])
            ->limit(10)
            ->get();

        return view('admin.dashboard')->with($data);
    }

    /**
     * New-account growth: this month vs last (a delta), a per-role split for the current month, and a
     * six-month trend — so uptake and marketing response are readable at a glance. Admins are excluded
     * (staff, not adoption). Uses created_at; degrades to zeros on an empty table, never errors.
     */
    private function signupGrowth(): array
    {
        $roles = [
            USER_ROLE_OWNER          => __('Owners'),
            USER_ROLE_TENANT         => __('Tenants'),
            USER_ROLE_AFFILIATE      => __('Affiliates'),
            USER_ROLE_MAINTAINER     => __('Maintainers'),
            USER_ROLE_FINANCE_PARTNER => __('Finance partners'),
        ];
        $monthStart     = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();

        $notAdmin = fn ($q) => $q->where('role', '!=', USER_ROLE_ADMIN);

        $thisMonth = User::where($notAdmin)->where('created_at', '>=', $monthStart)->count();
        $lastMonth = User::where($notAdmin)->whereBetween('created_at', [$lastMonthStart, $monthStart])->count();

        $byRole = [];
        foreach ($roles as $role => $label) {
            $byRole[] = [
                'label' => $label,
                'count' => User::where('role', $role)->where('created_at', '>=', $monthStart)->count(),
            ];
        }

        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonthsNoOverflow($i)->startOfMonth();
            $end   = (clone $start)->endOfMonth();
            $trend[] = [
                'label' => $start->format('M'),
                'count' => User::where($notAdmin)->whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        return [
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'by_role'    => $byRole,
            'trend'      => $trend,
        ];
    }

    /**
     * Platform earnings across every revenue stream we run — subscriptions, marketplace commission,
     * the tokenized infrastructure (Centresidence) commission + infrastructure invoices, financing
     * (origination + servicing fees netted from remittances), and the prepaid credit "token" buckets
     * (SMS / agreement / screening). Each figure is an actual table
     * sum; streams never overlap (marketplace, token and rent are distinct `transaction_source`s, and
     * the credit buckets live in a separate ledger). Missing tables degrade to zero, never error.
     */
    private function platformEarnings(): array
    {
        $monthStart = now()->startOfMonth();
        $streams = [];

        // ── Subscriptions (owner plan revenue) ───────────────────────────────
        $subsBase  = SubscriptionOrder::where('payment_status', ORDER_PAYMENT_STATUS_PAID);
        $streams['subscriptions'] = [
            'label' => __('Subscriptions'),
            'note'  => __('Owner plan payments'),
            'icon'  => 'ri-vip-crown-2-line',
            'accent' => 'blue',
            'all'   => (float) (clone $subsBase)->sum('total'),
            'month' => (float) (clone $subsBase)->where('created_at', '>=', $monthStart)->sum('total'),
        ];

        // ── Marketplace (our commission on sales; GMV shown as context) ───────
        $mpCommissionAll = $mpCommissionMonth = $mpGmv = 0.0;
        $tokenCommissionAll = $tokenCommissionMonth = 0.0;
        if (Schema::hasTable('wallet_transactions')) {
            $mpCommissionAll   = (float) WalletTransaction::marketplace()->sum('commission_amount');
            $mpCommissionMonth = (float) WalletTransaction::marketplace()->where('created_at', '>=', $monthStart)->sum('commission_amount');
            $mpGmv             = (float) WalletTransaction::marketplace()->sum('gross_amount');

            $tokenCommissionAll   = (float) WalletTransaction::where('transaction_source', 'token')->sum('commission_amount');
            $tokenCommissionMonth = (float) WalletTransaction::where('transaction_source', 'token')->where('created_at', '>=', $monthStart)->sum('commission_amount');
        }
        $streams['marketplace'] = [
            'label'  => __('Marketplace'),
            'note'   => __('Commission on :gmv in sales', ['gmv' => currencyPrice($mpGmv)]),
            'icon'   => 'ri-store-2-line',
            'accent' => 'green',
            'all'    => $mpCommissionAll,
            'month'  => $mpCommissionMonth,
        ];

        // ── Tokenized infrastructure (Centresidence): token commission + infra invoices ──
        $infraAll = $infraMonth = 0.0;
        if (Schema::hasTable('centresidence_owner_infrastructure_invoices')
            && Schema::hasColumn('centresidence_owner_infrastructure_invoices', 'paid_total')) {
            $infraAll   = (float) DB::table('centresidence_owner_infrastructure_invoices')->sum('paid_total');
            $infraMonth = (float) DB::table('centresidence_owner_infrastructure_invoices')
                ->where('paid_at', '>=', $monthStart)->sum('paid_total');
        }
        $streams['infrastructure'] = [
            'label'  => __('Infrastructure & tokens'),
            'note'   => __('Gas token commission + infrastructure billing'),
            'icon'   => 'ri-flashlight-line',
            'accent' => 'amber',
            'all'    => $tokenCommissionAll + $infraAll,
            'month'  => $tokenCommissionMonth + $infraMonth,
        ];

        // ── Financing (our origination + servicing fees, netted from remittances) ──
        // Both realised fees are stored per remittance batch (with a timestamp), so all-time
        // and this-month attribute cleanly. Origination on the batch equals the sum of what
        // was collected into finance_facilities.origination_fee_collected.
        $finAll = $finMonth = 0.0;
        if (Schema::hasTable('partner_remittance_batches')
            && Schema::hasColumn('partner_remittance_batches', 'origination_fee')) {
            $finBase  = DB::table('partner_remittance_batches');
            $finAll   = (float) (clone $finBase)->sum('origination_fee')
                      + (float) (clone $finBase)->sum('servicing_fee');
            $finMonth = (float) (clone $finBase)->where('created_at', '>=', $monthStart)->sum('origination_fee')
                      + (float) (clone $finBase)->where('created_at', '>=', $monthStart)->sum('servicing_fee');
        }
        $streams['financing'] = [
            'label'  => __('Financing'),
            'note'   => __('Origination + servicing fees on facilities'),
            'icon'   => 'ri-bank-line',
            'accent' => 'teal',
            'all'    => $finAll,
            'month'  => $finMonth,
        ];

        // ── Prepaid credit "token" buckets (SMS / agreement / screening) ──────
        $bucketRows = [];
        $bucketsAll = $bucketsMonth = 0.0;
        if (Schema::hasTable('owner_credit_transactions')) {
            foreach ((array) config('credits.buckets', []) as $key => $cfg) {
                $base  = OwnerCreditTransaction::bucket($key)->where('type', 'purchase')->where('status', 'success');
                $all   = (float) (clone $base)->sum('amount_paid');
                $month = (float) (clone $base)->where('created_at', '>=', $monthStart)->sum('amount_paid');
                $bucketRows[] = [
                    'label' => $cfg['label'] ?? ucfirst($key),
                    'all'   => $all,
                    'month' => $month,
                ];
                $bucketsAll   += $all;
                $bucketsMonth += $month;
            }
        }
        $streams['credits'] = [
            'label'   => __('Prepaid credits'),
            'note'    => __('SMS, agreement & screening tokens'),
            'icon'    => 'ri-coin-line',
            'accent'  => 'violet',
            'all'     => $bucketsAll,
            'month'   => $bucketsMonth,
            'buckets' => $bucketRows,
        ];

        $totalAll   = array_sum(array_column($streams, 'all'));
        $totalMonth = array_sum(array_column($streams, 'month'));

        return [
            'streams'     => $streams,
            'total_all'   => $totalAll,
            'total_month' => $totalMonth,
        ];
    }

    public function notification()
    {
        $data['pageTitle'] = __('Notification');
        Notification::query()
            ->where(function ($q) {
                $q->where('notifications.user_id', auth()->id())
                    ->orWhere('notifications.user_id', null);
            })
            ->update(['is_seen' => ACTIVE]);
        return view('admin.notification')->with($data);
    }
}
