<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller; 
use App\Models\OwnerWallet;
use App\Models\Owner;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\Payment\MpesaB2CService;
use App\Jobs\SendWalletNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OwnerWalletController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // OWNER SIDE
    // ──────────────────────────────────────────────────────────

    /**
     * Owner wallet dashboard.
     */

    public function index()
    {
        $wallet = OwnerWallet::forUser(auth()->id());
        $owner = Owner::where('user_id', auth()->id())->first();
 
        // Paginated per tab — uses query string keys mp_page and rent_page
        // so both paginators work independently on the same page.
        $marketplaceTransactions = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->where('transaction_source', 'marketplace')
            ->latest()
            ->paginate(20, ['*'], 'mp_page');
 
        $rentTransactions = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->where('transaction_source', 'rent')
            ->latest()
            ->paginate(20, ['*'], 'rent_page');
 
        // Summary stats across all sources
        $totalEarned = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->where('type', 'credit')
            ->sum('net_amount');
 
        $totalCommission = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->sum('commission_amount');
 
        $totalWithdrawn = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->where('type', 'debit')
            ->sum('net_amount');

        $subscription = null;
        $isTransactionModel = false;
        
        if ($owner) {
            $subscription = \DB::table('owner_packages')
                ->where('user_id', $owner->user_id)
                ->where('status', 1)
                ->latest()
                ->first();
        
            $pricingModel = $subscription?->pricing_model ?? 'free';
            $isTransactionModel = $pricingModel === 'transaction';
        }
 
        return view('owner.wallet.index', compact(
            'wallet',
            'marketplaceTransactions',
            'rentTransactions',
            'totalEarned',
            'totalCommission',
            'totalWithdrawn',
            'isTransactionModel'
        ));
    }

    public function rentTransaction(WalletTransaction $transaction)
    {
        // Security: ensure this transaction belongs to this owner's wallet
        $wallet = OwnerWallet::forUser(auth()->id());
        if ($transaction->owner_wallet_id !== $wallet->id) {
            return response()->json([
                'success' => false,
                'message' => __('Transaction not found.'),
            ], 403);
        }

        // Must be a rent transaction with a linked order
        if (!$transaction->invoice_order_id) {
            return response()->json([
                'success' => false,
                'message' => __('No order linked to this transaction.'),
            ], 404);
        }

        try {
            // Resolve order → invoice → tenant → propertyUnit → property
            $order = \App\Models\Order::with([
                'invoice.tenant.user',
                'invoice.propertyUnit.property',
                'invoice.invoiceItems.invoiceType', // <-- added
                'gateway',
            ])->find($transaction->invoice_order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => __('Order not found.'),
                ], 404);
            }

            $invoice      = $order->invoice;
            $tenant       = $invoice?->tenant;
            $tenantUser   = $tenant?->user;
            $propertyUnit = $invoice?->propertyUnit;
            $property     = $propertyUnit?->property;
            $gateway      = $order->gateway;

            // Collect all invoice types from items
            $types = $invoice?->invoiceItems
                ?->map(fn($item) => $item->invoiceType?->name)
                ->filter()
                ->unique()
                ->values()
                ->join(', ');

            return response()->json([
                'success' => true,
                'data'    => [
                    // Tenant
                    'tenant_name'       => $tenantUser?->name ?? $tenant?->name ?? '—',
                    'tenant_email'      => $tenantUser?->email ?? '—',

                    // Property / unit
                    'unit_name'         => $propertyUnit?->unit_name ?? '—',
                    'property_name'     => $property?->name ?? $property?->title ?? '—',

                    // Invoice
                    'invoice_no'        => $invoice?->invoice_no ?? '—',
                    'invoice_type'      => $types ?: 'Rent', // fallback if none
                    'issue_date'        => $invoice?->created_at?->format('d M Y') ?? '—',
                    'due_date'          => $invoice?->due_date
                                            ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y')
                                            : '—',
                    'paid_on'           => $order->updated_at?->format('d M Y, H:i') ?? '—',
                    'payment_method'    => $gateway ? ucfirst($gateway->slug) : 'M-Pesa',

                    // Amounts
                    'gross_amount'      => $transaction->gross_amount,
                    'commission_rate'   => $transaction->commission_rate,
                    'commission_amount' => $transaction->commission_amount,
                    'net_amount'        => $transaction->net_amount,
                ],
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('rentTransaction detail failed', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Could not load payment details.'),
            ], 500);
        }
    }

    

    /**
     * Submit a withdrawal request (M-Pesa B2C).
     * Records a pending WithdrawalRequest; admin approves → triggers B2C.
     */
  
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'phone'  => ['required', 'string', 'regex:/^[71]\d{8}$/'],
        ]);
 
        $wallet = OwnerWallet::forUser(auth()->id());
        $amount = (float) $request->amount;
 
        if ($amount > $wallet->balance) {
            return response()->json([
                'success' => false,
                'error'   => __('Amount exceeds your available balance.'),
            ]);
        }
 
        DB::beginTransaction();
        try {
            // Reserve the balance immediately so it can't be double-spent
            $wallet->decrement('balance', $amount);
 
            // Create a pending withdrawal request
            $withdrawal = WithdrawalRequest::create([
                'owner_wallet_id' => $wallet->id,
                'amount'          => $amount,
                'phone'           => '+254' . $request->phone,
                'status'          => 'pending',
            ]);
 
            // Debit transaction record (net_amount = amount requested)
            WalletTransaction::create([

                'owner_wallet_id'    => $wallet->id,
                'product_order_id'   => null,
                'invoice_order_id'   => null,
                'transaction_source' => 'marketplace',
                'gross_amount'       => null,
                'commission_rate'    => null,
                'commission_amount'  => null,
                'net_amount'         => $amount,
                'type'               => 'debit',
                'description'        => "Withdrawal request #{$withdrawal->id} — pending",
            ]);
 
            DB::commit();
 
            // ── Notify owner: request received ───────────────────────
            $recipient = auth()->user();
            $emailData = (object) [
                'subject' => __('Withdrawal Request Received — KSh ') . number_format($amount, 2),
                'message' => __('Your withdrawal request of KSh ') . number_format($amount, 2) .
                             __(' to ') . '+254' . $request->phone .
                             __(' has been received and is pending approval. You will be notified once it is processed.'),
            ];
            $notificationData = (object) [
                'title' => __('Withdrawal Request Received'),
                'body'  => __('KSh ') . number_format($amount, 2) . __(' withdrawal is pending approval.'),
                'url'   => route('owner.wallet.index'),
            ];
            SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal);
 
            // ── Notify all admins: action required ───────────────────
            $ownerName         = auth()->user()->name;
            $adminEmailData    = (object) [
                'subject' => __('Withdrawal Request Pending Approval — KSh ') . number_format($amount, 2),
                'message' => $ownerName . __(' has requested a withdrawal of KSh ') . number_format($amount, 2) .
                             __(' to ') . '+254' . $request->phone .
                             __(' and is awaiting your approval. Please review and process this request promptly.'),
            ];
            $adminNotification = (object) [
                'title' => __('Withdrawal Needs Approval'),
                'body'  => $ownerName . __(' requested KSh ') . number_format($amount, 2) . __(' — action required.'),
                'url'   => route('admin.wallet.commissions'),
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
            Log::error('Withdrawal request failed: ' . $e->getMessage());
 
            return response()->json([
                'success' => false,
                'error'   => __('Withdrawal request failed. Please try again.'),
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────
    // ADMIN SIDE
    // ──────────────────────────────────────────────────────────

    /**
     * Admin commissions dashboard.
     */
    public function adminDashboard()
    {
        $totalGmv         = WalletTransaction::sum('gross_amount');
        $totalCommission  = WalletTransaction::sum('commission_amount');
        $totalOwnerBalance= OwnerWallet::sum('balance');
        $totalWithdrawn   = WalletTransaction::where('type', 'debit')->sum('net_amount');

        $wallets           = OwnerWallet::with('user')->get();
        $pendingWithdrawals= WithdrawalRequest::with('wallet.user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $allTransactions   = WalletTransaction::with('wallet.user')
            ->latest()
            ->paginate(30);

        return view('admin.wallet.commissions', compact(
            'totalGmv',
            'totalCommission',
            'totalOwnerBalance',
            'totalWithdrawn',
            'wallets',
            'pendingWithdrawals',
            'allTransactions'
        ));
    }

    /**
     * Admin: view a single owner wallet detail.
     */
    public function adminOwnerWallet(OwnerWallet $wallet)
    {
        $transactions = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->latest()
            ->paginate(20);

        $totalEarned     = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->where('type', 'credit')->sum('net_amount');

        $totalCommission = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->sum('commission_amount');

        $totalWithdrawn  = WalletTransaction::where('owner_wallet_id', $wallet->id)
            ->where('type', 'debit')->sum('net_amount');

        return view('admin.wallet.owner-detail', compact(
            'wallet', 'transactions', 'totalEarned', 'totalCommission', 'totalWithdrawn'
        ));
    }

    /**
     * Admin: Approve a withdrawal → trigger M-Pesa B2C.
     */
    public function approveWithdrawal(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json(['success' => false, 'error' => 'Already processed.']);
        }

        DB::beginTransaction();
        try {
            $result = app(MpesaB2CService::class)->send($withdrawal->phone, $withdrawal->amount);

            if (!($result['success'] ?? false)) {
                throw new \Exception($result['message'] ?? __('M-Pesa B2C request failed.'));
            }

            $withdrawal->update([
                'status'          => 'approved',
                'processed_at'    => now(),
                'mpesa_reference' => $result['reference'] ?? null,
            ]);

            WalletTransaction::where('owner_wallet_id', $withdrawal->owner_wallet_id)
                ->where('description', "Withdrawal request #{$withdrawal->id} — pending")
                ->update(['description' => "Withdrawal #{$withdrawal->id} — paid via M-Pesa B2C"]);

            DB::commit();

            // ── Notify owner: withdrawal approved ────────────────────
            $recipient = $withdrawal->wallet->user;
            if ($recipient) {
                $emailData = (object) [
                    'subject' => __('Withdrawal Approved — KSh ') . number_format($withdrawal->amount, 2),
                    'message' => __('Your withdrawal of KSh ') . number_format($withdrawal->amount, 2) .
                                 __(' has been approved and sent to ') . $withdrawal->phone .
                                 __(' via M-Pesa. It should arrive within minutes.'),
                ];
                $notificationData = (object) [
                    'title' => __('Withdrawal Approved'),
                    'body'  => __('KSh ') . number_format($withdrawal->amount, 2) .
                               __(' has been sent to ') . $withdrawal->phone . '.',
                    'url'   => route('owner.wallet.index'),
                ];
                SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal);
            }

            return response()->json([
                'success' => true,
                'message' => __('Withdrawal approved and sent via M-Pesa.'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal approval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => __('Approval failed: ') . $e->getMessage(),
            ]);
        }
    }

    /**
     * Admin: Reject a withdrawal → refund balance to wallet.
     */
    public function rejectWithdrawal(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json(['success' => false, 'error' => 'Already processed.']);
        }

        DB::beginTransaction();
        try {
            // Refund balance
            $withdrawal->wallet->increment('balance', $withdrawal->amount);

            $withdrawal->update([
                'status'       => 'rejected',
                'processed_at' => now(),
            ]);

            WalletTransaction::where('owner_wallet_id', $withdrawal->owner_wallet_id)
                ->where('description', "Withdrawal request #{$withdrawal->id} — pending")
                ->update(['description' => "Withdrawal #{$withdrawal->id} — rejected (refunded)"]);

            // Credit reversal entry for audit trail
            WalletTransaction::create([
                'owner_wallet_id'   => $withdrawal->owner_wallet_id,
                'product_order_id'  => null,
                'gross_amount'      => null,
                'commission_rate'   => null,
                'commission_amount' => null,
                'net_amount'        => $withdrawal->amount,
                'type'              => 'credit',
                'description'       => "Refund — rejected withdrawal #{$withdrawal->id}",
            ]);

            DB::commit();

            // ── Notify owner: withdrawal rejected, balance restored ───
            $recipient = $withdrawal->wallet->user;
            if ($recipient) {
                $emailData = (object) [
                    'subject' => __('Withdrawal Rejected — KSh ') . number_format($withdrawal->amount, 2) . __(' Returned'),
                    'message' => __('Your withdrawal request of KSh ') . number_format($withdrawal->amount, 2) .
                                 __(' has been rejected. The full amount has been returned to your wallet balance.') .
                                 __(' If you have questions, please contact support.'),
                ];
                $notificationData = (object) [
                    'title' => __('Withdrawal Rejected'),
                    'body'  => __('KSh ') . number_format($withdrawal->amount, 2) .
                               __(' has been returned to your wallet.'),
                    'url'   => route('owner.wallet.index'),
                ];
                SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal);
            }

            return response()->json([
                'success' => true,
                'message' => __('Withdrawal rejected and balance refunded.'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal rejection failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => __('Rejection failed. Please try again.'),
            ]);
        }
    }
}
