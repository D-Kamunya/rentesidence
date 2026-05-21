<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdrawal;
use App\Models\Affiliate;
use App\Models\AffiliateCommissionPayment;
use App\Services\AffiliateCommissionService;
use App\Services\Payment\MpesaB2CService;
use App\Jobs\SendWalletNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffiliateWithdrawalController extends Controller
{
    public function adminIndex()
    {
        $withdrawals = AffiliateWithdrawal::with(['affiliate.user'])
            ->latest()
            ->paginate(30);

        $pendingCount   = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_PENDING)->count();
        $pendingAmount  = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_PENDING)->sum('amount');
        $approvedCount  = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_APPROVED)->count();
        $approvedAmount = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_APPROVED)->sum('amount');
        $rejectedCount  = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_REJECTED)->count();
        $rejectedAmount = AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_REJECTED)->sum('amount');
        $totalAffiliates = AffiliateWithdrawal::distinct('affiliate_id')->count('affiliate_id');

        // ── Affiliate Performance Summary ──
        $affiliateSummaries = Affiliate::with(['user'])
            ->whereHas('withdrawals')
            ->withSum(['withdrawals as total_withdrawn' => function ($q) {
                $q->where('status', AFFILIATE_WITHDRAWAL_APPROVED);
            }], 'amount')
            ->withSum(['withdrawals as total_pending' => function ($q) {
                $q->where('status', AFFILIATE_WITHDRAWAL_PENDING);
            }], 'amount')
            ->withCount(['withdrawals as pending_count' => function ($q) {
                $q->where('status', AFFILIATE_WITHDRAWAL_PENDING);
            }])
            ->get()
            ->sortByDesc('pending_count');

        return view('admin.affiliates.withdrawals', compact(
            'withdrawals', 'pendingCount', 'pendingAmount',
            'approvedCount', 'approvedAmount',
            'rejectedCount', 'rejectedAmount',
            'totalAffiliates', 'affiliateSummaries'
        ));
    }

    // ── NEW: View affiliate earnings detail ─────────────────
    public function affiliateEarnings($affiliateId)
    {
        $affiliate = Affiliate::with(['user'])->findOrFail($affiliateId);
        $svc = app(AffiliateCommissionService::class);

        // Stats
        $availableBalance = $svc->getAvailableBalance($affiliateId);
        $lifetimeEarned   = $svc->getLifeTimeGrossCommissions($affiliateId);
        $totalWithdrawn   = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->where('status', AFFILIATE_WITHDRAWAL_APPROVED)
            ->sum('amount');
        $currentMonthPayout = $svc->getLatestPeriodPayout(
            $affiliateId,
            (int) now()->format('n'),
            (int) now()->format('Y')
        );

        // Monthly commission history (last 12 months)
        $monthlyCommissions = AffiliateCommissionPayment::where('affiliate_id', $affiliateId)
            ->whereIn('id', function ($q) use ($affiliateId) {
                $q->selectRaw('MAX(id)')
                  ->from('affiliate_commission_payments')
                  ->where('affiliate_id', $affiliateId)
                  ->groupBy('period_year', 'period_month');
            })
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->take(12)
            ->get()
            ->map(function ($row) {
                return [
                    'period' => \Carbon\Carbon::createFromDate($row->period_year, $row->period_month, 1)->format('M Y'),
                    'subscription_payout' => $row->new_commission_payout + $row->recurring_commission_payout,
                    'rent_payout'         => $row->rent_commission_payout,
                    'marketplace_payout'  => $row->marketplace_commission_payout,
                    'total_payout'        => $row->total_commission_payout,
                ];
            });

        // Pending withdrawals for this affiliate
        $pendingWithdrawals = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->where('status', AFFILIATE_WITHDRAWAL_PENDING)
            ->latest()
            ->get();

        // Recent withdrawals (last 10)
        $recentWithdrawals = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($wd) {
                return [
                    'id'                => $wd->id,
                    'amount'            => $wd->amount,
                    'phone'             => $wd->phone,
                    'status'            => $wd->status,
                    'settlement_method' => $wd->settlement_method,
                    'mpesa_reference'   => $wd->mpesa_reference,
                    'notes'             => $wd->notes,
                    'requested_at'      => $wd->created_at->format('M d, Y H:i'),
                    'processed_at'      => $wd->processed_at ? \Carbon\Carbon::parse($wd->processed_at)->format('M d, Y H:i') : null,
                    'status_label'      => ucfirst($wd->status),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'affiliate' => [
                    'id'    => $affiliate->id,
                    'name'  => $affiliate->user->name ?? '—',
                    'email' => $affiliate->user->email ?? '—',
                    'referral_code' => $affiliate->referral_code ?? '—',
                ],
                'stats' => [
                    'available_balance'    => $availableBalance,
                    'lifetime_earned'      => $lifetimeEarned,
                    'total_withdrawn'      => (float) $totalWithdrawn,
                    'current_month_payout' => $currentMonthPayout,
                ],
                'monthly_commissions' => $monthlyCommissions,
                'pending_withdrawals' => $pendingWithdrawals,
                'recent_withdrawals'  => $recentWithdrawals,
            ],
        ]);
    }
    
    // ── Affiliate: request withdrawal ─────────────────────────

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'phone'  => ['required', 'string', 'regex:/^[71]\d{8}$/'],
        ]);

        $affiliate = auth()->user()->affiliate;
        if (!$affiliate) {
            return response()->json(['success' => false, 'error' => __('Affiliate account not found.')]);
        }

        $svc     = app(AffiliateCommissionService::class);
        $balance = $svc->getAvailableBalance($affiliate->id);
        $amount  = (float) $request->amount;

        if ($amount > $balance) {
            return response()->json([
                'success' => false,
                'error'   => __('Amount exceeds your available balance.'),
            ]);
        }

        DB::beginTransaction();
        try {
            $withdrawal = AffiliateWithdrawal::create([
                'affiliate_id'      => $affiliate->id,
                'amount'            => $amount,
                'phone'             => '+254' . $request->phone,
                'status'            => AFFILIATE_WITHDRAWAL_PENDING,
                'settlement_method' => 'b2c',
            ]);

            DB::commit();

            // Notify admins
            $ownerName = auth()->user()->name;
            $adminEmailData = (object) [
                'subject' => __('Affiliate Withdrawal Request — KSh ') . number_format($amount, 2),
                'message' => $ownerName . __(' has requested a withdrawal of KSh ')
                           . number_format($amount, 2) . __(' to ') . '+254' . $request->phone
                           . __(' and is awaiting your approval.'),
            ];
            $adminNotification = (object) [
                'title' => __('Affiliate Withdrawal Needs Approval'),
                'body'  => $ownerName . __(' requested KSh ') . number_format($amount, 2),
                'url'   => route('admin.affiliate.withdrawals'),
            ];
            \App\Models\User::where('role', USER_ROLE_ADMIN)
                ->each(function ($admin) use ($adminEmailData, $adminNotification, $withdrawal) {
                    SendWalletNotificationJob::dispatch($admin, $adminEmailData, $adminNotification, $withdrawal);
                });

            return response()->json([
                'success' => true,
                'message' => __('Withdrawal request submitted. You will receive funds once approved.'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Affiliate withdrawal request failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => __('Withdrawal request failed. Please try again.'),
            ]);
        }
    }

    // ── Admin: approve (B2C or manual) ────────────────────────

    public function approve(Request $request, AffiliateWithdrawal $withdrawal)
    {
        $request->validate([
            'method' => ['required', 'in:b2c,manual'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        if ($withdrawal->status !== AFFILIATE_WITHDRAWAL_PENDING) {
            return response()->json(['success' => false, 'error' => __('Already processed.')]);
        }

        DB::beginTransaction();
        try {
            if ($request->method === 'b2c') {
                $result = app(MpesaB2CService::class)->send($withdrawal->phone, $withdrawal->amount);

                if (!($result['success'] ?? false)) {
                    throw new \Exception($result['message'] ?? __('M-Pesa B2C request failed.'));
                }

                $withdrawal->update([
                    'status'            => AFFILIATE_WITHDRAWAL_APPROVED,
                    'settlement_method' => 'b2c',
                    'mpesa_reference'   => $result['reference'] ?? null,
                    'processed_at'      => now(),
                    'notes'             => $request->notes,
                ]);

            } else {
                // Manual settlement — admin confirms they paid outside system
                $withdrawal->update([
                    'status'            => AFFILIATE_WITHDRAWAL_APPROVED,
                    'settlement_method' => 'manual',
                    'processed_at'      => now(),
                    'notes'             => $request->notes,
                ]);
            }

            DB::commit();

            // Notify affiliate
            $recipient = $withdrawal->affiliate?->user;
            if ($recipient) {
                $method  = $request->method === 'b2c' ? 'M-Pesa' : 'manual transfer';
                $emailData = (object) [
                    'subject' => __('Withdrawal Approved — KSh ') . number_format($withdrawal->amount, 2),
                    'message' => __('Your withdrawal of KSh ') . number_format($withdrawal->amount, 2)
                               . __(' has been approved via ') . $method . '.',
                ];
                $notificationData = (object) [
                    'title' => __('Withdrawal Approved'),
                    'body'  => __('KSh ') . number_format($withdrawal->amount, 2) . __(' approved via ') . $method . '.',
                    'url'   => route('affiliate.dashboard'),
                ];
                SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal);
            }

            return response()->json([
                'success' => true,
                'message' => __('Withdrawal approved successfully.'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Affiliate withdrawal approval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => __('Approval failed: ') . $e->getMessage(),
            ]);
        }
    }

    // ── Admin: reject ──────────────────────────────────────────

    public function reject(Request $request, AffiliateWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== AFFILIATE_WITHDRAWAL_PENDING) {
            return response()->json(['success' => false, 'error' => __('Already processed.')]);
        }

        $withdrawal->update([
            'status'       => AFFILIATE_WITHDRAWAL_REJECTED,
            'processed_at' => now(),
            'notes'        => $request->notes,
        ]);

        // Notify affiliate
        $recipient = $withdrawal->affiliate?->user;
        if ($recipient) {
            $reasonText = $request->notes ? __(' Reason: ') . $request->notes : '';
            $emailData = (object) [
                'subject' => __('Withdrawal Rejected — KSh ') . number_format($withdrawal->amount, 2),
                'message' => __('Your withdrawal request of KSh ') . number_format($withdrawal->amount, 2)
                           . __(' has been rejected.') . $reasonText . __(' Please contact support if you have questions.'),
            ];
            $notificationData = (object) [
                'title' => __('Withdrawal Rejected'),
                'body'  => __('KSh ') . number_format($withdrawal->amount, 2) . __(' withdrawal was rejected.'),
                'url'   => route('affiliate.dashboard'),
            ];
            SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal);
        }

        return response()->json([
            'success' => true,
            'message' => __('Withdrawal rejected.'),
        ]);
    }
}