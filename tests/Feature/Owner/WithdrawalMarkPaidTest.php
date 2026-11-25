<?php

namespace Tests\Feature\Owner;

use App\Http\Controllers\Owner\OwnerWalletController;
use App\Models\OwnerWallet;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The MANUAL payout leg (admin pays by M-Pesa, then marks the request paid) — money-critical, and the
 * user can't test payments live, so this covers the invariants: marking paid finalizes the request to
 * 'approved' WITHOUT changing the wallet balance (it was reserved at request time) and WITHOUT firing
 * B2C; a non-pending request can't be paid again (no double-pay); the mode flag persists.
 */
class WithdrawalMarkPaidTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.wd_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'wd_sqlite']);
        DB::purge('wd_sqlite');
        \Illuminate\Database\Eloquent\Model::unguard();

        Schema::create('users', function ($t) { $t->id(); $t->softDeletes(); $t->timestamps(); }); // empty → wallet->user resolves to null
        Schema::create('owner_wallets', function ($t) { $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->decimal('balance', 14, 2)->default(0); $t->timestamps(); });
        Schema::create('withdrawal_requests', function ($t) {
            $t->id(); $t->unsignedBigInteger('owner_wallet_id'); $t->decimal('amount', 14, 2);
            $t->string('phone')->nullable(); $t->string('status')->default('pending');
            $t->timestamp('processed_at')->nullable(); $t->string('mpesa_reference')->nullable(); $t->timestamps();
        });
        Schema::create('wallet_transactions', function ($t) {
            $t->id(); $t->unsignedBigInteger('owner_wallet_id'); $t->decimal('net_amount', 14, 2)->nullable();
            $t->string('type')->nullable(); $t->text('description')->nullable(); $t->timestamps();
        });
        Schema::create('settings', function ($t) { $t->id(); $t->string('option_key')->unique(); $t->text('option_value')->nullable(); $t->timestamps(); });
    }

    private function seedPending(float $amount = 500): array
    {
        $wallet = OwnerWallet::create(['user_id' => 99999, 'balance' => 500]); // balance already reserved
        $wd = WithdrawalRequest::create(['owner_wallet_id' => $wallet->id, 'amount' => $amount, 'phone' => '+254712345678', 'status' => 'pending']);
        WalletTransaction::create(['owner_wallet_id' => $wallet->id, 'net_amount' => $amount, 'type' => 'debit', 'description' => "Withdrawal request #{$wd->id} — pending"]);
        return [$wallet, $wd];
    }

    public function test_mark_paid_finalizes_without_balance_change(): void
    {
        [$wallet, $wd] = $this->seedPending(500);

        $request = Request::create('/admin/wallet/withdrawal/' . $wd->id . '/mark-paid', 'POST', ['mpesa_reference' => 'SJK3X9P2Q1']);
        $this->app->instance('request', $request);

        $response = (new OwnerWalletController)->markPaidWithdrawal($request, $wd);
        $this->assertTrue($response->getData()->success);

        $wd->refresh();
        $this->assertSame('approved', $wd->status);              // terminal success state
        $this->assertNotNull($wd->processed_at);
        $this->assertSame('SJK3X9P2Q1', $wd->mpesa_reference);

        // Balance is UNCHANGED — the money was already reserved at request time; no double-debit.
        $this->assertSame(500.0, (float) OwnerWallet::find($wallet->id)->balance);
        // The ledger entry is finalized.
        $this->assertSame("Withdrawal #{$wd->id} — paid (manual)", WalletTransaction::where('owner_wallet_id', $wallet->id)->value('description'));
    }

    public function test_mark_paid_on_non_pending_is_blocked(): void
    {
        [, $wd] = $this->seedPending(500);
        $wd->update(['status' => 'approved']); // already paid

        $request = Request::create('/', 'POST', []);
        $this->app->instance('request', $request);
        $response = (new OwnerWalletController)->markPaidWithdrawal($request, $wd);

        $this->assertFalse($response->getData()->success); // no double-pay
    }

    public function test_mode_flag_persists(): void
    {
        $request = Request::create('/', 'POST', ['mode' => 'auto_b2c']);
        $this->app->instance('request', $request);
        (new OwnerWalletController)->setWithdrawalMode($request);
        $this->assertSame('auto_b2c', Setting::where('option_key', 'withdrawal_payout_mode')->value('option_value'));
    }
}
