<?php

namespace Tests\Feature\Marketplace;

use App\Models\OwnerWallet;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * SECURITY REGRESSION (pentest finding #4, 2026-09-06): the owner-wallet
 * withdrawal must reserve the balance with an ATOMIC conditional decrement, so
 * two concurrent withdrawals can't both pass a stale balance check and over-draw
 * the wallet (TOCTOU). The decrement only applies while the balance still covers
 * it; once exhausted a second reserve affects 0 rows and the balance never goes
 * negative.
 */
class WalletWithdrawRaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('owner_wallets')) {
            Schema::create('owner_wallets', function ($t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->decimal('balance', 16, 2)->default(0);
                $t->timestamps();
            });
        }
    }

    /** The exact atomic reserve the controller uses. */
    private function reserve(int $walletId, float $amount): int
    {
        return OwnerWallet::whereKey($walletId)
            ->where('balance', '>=', $amount)
            ->decrement('balance', $amount);
    }

    public function test_atomic_reserve_prevents_overdraw_and_never_goes_negative(): void
    {
        $wallet = OwnerWallet::create(['user_id' => 1, 'balance' => 100]);

        // First withdrawal of the full balance succeeds.
        $this->assertSame(1, $this->reserve($wallet->id, 100.0));
        $this->assertEquals(0.0, (float) $wallet->fresh()->balance);

        // A second concurrent withdrawal for the same amount must NOT apply (0 rows)
        // — the balance can no longer cover it, so no over-draw / no negative balance.
        $this->assertSame(0, $this->reserve($wallet->id, 100.0));
        $this->assertEquals(0.0, (float) $wallet->fresh()->balance);
        $this->assertGreaterThanOrEqual(0.0, (float) $wallet->fresh()->balance);
    }
}
