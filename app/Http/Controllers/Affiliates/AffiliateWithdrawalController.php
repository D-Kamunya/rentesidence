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

        $svc    = app(AffiliateCommissionService::class);
        $amount = round((float) $request->amount, 2);
        $phone  = '+254' . $request->phone;

        try {
            // Balance check + create happen INSIDE the transaction, after locking the
            // affiliate row, so two concurrent requests can't both pass the check and
            // over-draw. Available balance already reserves pending withdrawals.
            $outcome = DB::transaction(function () use ($affiliate, $amount, $phone, $svc) {
                Affiliate::whereKey($affiliate->id)->lockForUpdate()->first();

                $hasInFlight = AffiliateWithdrawal::where('affiliate_id', $affiliate->id)
                    ->whereIn('status', [AFFILIATE_WITHDRAWAL_PENDING, AFFILIATE_WITHDRAWAL_PROCESSING])
                    ->exists();
                if ($hasInFlight) {
                    return ['error' => __('You already have a withdrawal in progress. Please wait for it to complete.')];
                }

                $available = $svc->getAvailableBalance($affiliate->id);
                if ($amount < 1 || $amount > $available) {
                    return ['error' => __('Amount exceeds your available balance.')];
                }

                return ['withdrawal' => AffiliateWithdrawal::create([
                    'affiliate_id'      => $affiliate->id,
                    'amount'            => $amount,
                    'phone'             => $phone,
                    'status'            => AFFILIATE_WITHDRAWAL_PENDING,
                    'settlement_method' => 'b2c',
                ])];
            });

            if (isset($outcome['error'])) {
                return response()->json(['success' => false, 'error' => $outcome['error']]);
            }
            $withdrawal = $outcome['withdrawal'];

            // Notify admins
            $ownerName = auth()->user()->name;
            $adminEmailData = (object) [
                'subject' => __('Affiliate withdrawal request — :amount', ['amount' => currencyPrice($amount)]),
                'message' => __(':name has requested a withdrawal of :amount to :phone and is awaiting your approval.', [
                    'name' => $ownerName, 'amount' => currencyPrice($amount), 'phone' => '+254' . $request->phone,
                ]),
            ];
            $adminNotification = (object) [
                'title' => __('Affiliate withdrawal needs approval'),
                'body'  => __(':name requested :amount.', ['name' => $ownerName, 'amount' => currencyPrice($amount)]),
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
            // DB::transaction() already rolled back on failure — no manual rollback here.
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

        // ── B2C: initiate, then hand off to the async ResultURL ───────────────
        // Daraja's synchronous response only confirms the request was ACCEPTED for
        // processing — not that the money reached the affiliate. So a B2C payout is
        // marked PROCESSING here and only flips to APPROVED (or FAILED) once the
        // ResultURL callback lands. See MpesaController::B2CResult.
        if ($request->method === 'b2c') {
            if (! $withdrawal->phone) {
                return response()->json(['success' => false, 'error' => __('This withdrawal has no phone number for M-Pesa payout. Settle it manually.')]);
            }

            // Send BEFORE the state change and OUTSIDE a wrapping transaction: an
            // accepted request must never be lost to a rollback (that would pay the
            // affiliate with no record). If send is rejected, nothing changed.
            try {
                $result = app(MpesaB2CService::class)->send($withdrawal->phone, $withdrawal->amount);
            } catch (\Throwable $e) {
                Log::error('Affiliate B2C send threw: ' . $e->getMessage(), ['withdrawal_id' => $withdrawal->id]);
                return response()->json(['success' => false, 'error' => __('M-Pesa payout could not be initiated. Please try again.')]);
            }

            if (! ($result['success'] ?? false)) {
                // Rejected up-front — stays PENDING, safe to retry or settle manually.
                Log::warning('Affiliate B2C rejected on send', ['withdrawal_id' => $withdrawal->id, 'message' => $result['message'] ?? null]);
                return response()->json(['success' => false, 'error' => __('M-Pesa rejected the payout: ') . ($result['message'] ?? __('unknown error'))]);
            }

            // Accepted → in-flight. Store the correlation ref so B2CResult can find it.
            $withdrawal->update([
                'status'            => AFFILIATE_WITHDRAWAL_PROCESSING,
                'settlement_method' => 'b2c',
                'mpesa_reference'   => $result['reference'] ?? null,
                'notes'             => $request->notes,
            ]);

            $recipient = $withdrawal->affiliate?->user;
            if ($recipient) {
                $emailData = (object) [
                    'subject' => __('Withdrawal is being processed — :amount', ['amount' => currencyPrice($withdrawal->amount)]),
                    'message' => __('Your withdrawal of :amount is being sent to M-Pesa :phone. You will be notified once it is confirmed.', ['amount' => currencyPrice($withdrawal->amount), 'phone' => $withdrawal->phone]),
                ];
                $notificationData = (object) [
                    'title' => __('Withdrawal processing'),
                    'body'  => __(':amount is being sent to your M-Pesa.', ['amount' => currencyPrice($withdrawal->amount)]),
                    'url'   => route('affiliate.dashboard'),
                ];
                SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal, false);
            }

            return response()->json([
                'success' => true,
                'message' => __('M-Pesa payout initiated. It will be confirmed once M-Pesa completes the transfer.'),
            ]);
        }

        // ── Manual: admin confirms an out-of-band transfer → APPROVED now ─────
        DB::beginTransaction();
        try {
            $withdrawal->update([
                'status'            => AFFILIATE_WITHDRAWAL_APPROVED,
                'settlement_method' => 'manual',
                'processed_at'      => now(),
                'notes'             => $request->notes,
            ]);

            DB::commit();

            $recipient = $withdrawal->affiliate?->user;
            if ($recipient) {
                $emailData = (object) [
                    'subject' => __('Withdrawal approved — :amount', ['amount' => currencyPrice($withdrawal->amount)]),
                    'message' => __('Your withdrawal of :amount has been approved via :method.', ['amount' => currencyPrice($withdrawal->amount), 'method' => __('manual transfer')]),
                ];
                $notificationData = (object) [
                    'title' => __('Withdrawal approved'),
                    'body'  => __(':amount approved via :method.', ['amount' => currencyPrice($withdrawal->amount), 'method' => __('manual transfer')]),
                    'url'   => route('affiliate.dashboard'),
                ];
                SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal, false);
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
            $reasonText = $request->notes ? ' ' . __('Reason: :notes', ['notes' => $request->notes]) : '';
            $emailData = (object) [
                'subject' => __('Withdrawal rejected — :amount', ['amount' => currencyPrice($withdrawal->amount)]),
                'message' => __('Your withdrawal request of :amount has been rejected.', ['amount' => currencyPrice($withdrawal->amount)])
                           . $reasonText . ' ' . __('Please contact support if you have any questions.'),
            ];
            $notificationData = (object) [
                'title' => __('Withdrawal rejected'),
                'body'  => __(':amount withdrawal was rejected.', ['amount' => currencyPrice($withdrawal->amount)]),
                'url'   => route('affiliate.dashboard'),
            ];
            SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal, false);
        }

        return response()->json([
            'success' => true,
            'message' => __('Withdrawal rejected.'),
        ]);
    }
}