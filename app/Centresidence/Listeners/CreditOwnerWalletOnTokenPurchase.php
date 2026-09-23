<?php

namespace App\Centresidence\Listeners;

use App\Centresidence\Events\TokenPurchased;
use App\Centresidence\Models\PropertyModule;
use App\Models\OwnerWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Schema;

/**
 * Credits the owner's NET token revenue (after commission + any fallback) to
 * their OwnerWallet on every token sale — the uniform payout path that works
 * for BOTH subscription and transaction owners (token money always flows
 * through Centresidence regardless of mode).
 *
 * Lives in the integration layer (a listener), so the TokenEngine stays
 * decoupled from the legacy wallet tables. Guarded by table existence so the
 * simulation sandbox and non-wallet installs are unaffected.
 */
class CreditOwnerWalletOnTokenPurchase
{
    public function handle(TokenPurchased $event): void
    {
        if (! Schema::hasTable('owner_wallets') || ! Schema::hasTable('wallet_transactions')) {
            return;
        }

        $purchase = $event->purchase;
        $net = (float) $purchase->owner_revenue_net;
        if ($net <= 0) {
            return;
        }

        $module = PropertyModule::with('module')->find($purchase->property_module_id);
        $ownerId = (int) optional($module)->owner_id;
        if (! $ownerId) {
            return;
        }

        $wallet = OwnerWallet::forUser($ownerId);
        $wallet->increment('balance', $net);

        WalletTransaction::create([
            'owner_wallet_id'    => $wallet->id,
            'transaction_source' => 'token',
            'gross_amount'       => (float) $purchase->owner_revenue_gross,
            'commission_rate'    => 0,
            'commission_amount'  => (float) $purchase->centresidence_commission + (float) $purchase->fallback_deducted,
            'net_amount'         => $net,
            'type'               => 'credit',
            'description'        => 'Token sale — ' . (optional(optional($module)->module)->name ?? 'utility'),
        ]);
    }
}
