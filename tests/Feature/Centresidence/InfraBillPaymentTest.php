<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Services\InfraBillPaymentService;
use App\Http\Controllers\Centresidence\InfraBillCallbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * B2 stage 3 — the payable infra bill. The owner pays the outstanding infra
 * (metered + non-metered, NOT the plan) via STK; the callback marks the invoices
 * paid, which clears their standing and lifts the readonly gate. Idempotent.
 */
class InfraBillPaymentTest extends CentresidenceDatabaseTestCase
{
    private function svc(): InfraBillPaymentService
    {
        return app(InfraBillPaymentService::class);
    }

    private function invoice(int $ownerId, array $attrs = []): CentresidenceCommissionInvoice
    {
        return CentresidenceCommissionInvoice::create(array_merge([
            'owner_id' => $ownerId, 'property_id' => 1,
            'billing_month' => Carbon::parse('2026-08-01'),
            'subscription_amount' => 1500,
            'metered_commission_total' => 300, 'non_metered_commission_total' => 200,
            'total_amount' => 2000, 'status' => CentresidenceCommissionInvoice::STATUS_PENDING,
        ], $attrs));
    }

    private function fireCallback(int $owner, int $resultCode, string $cid = 'CHECKOUT-INFRA-1', bool $recordPush = true, ?string $token = null)
    {
        // A genuine callback is preceded by a push we recorded (binds the callback to it).
        if ($recordPush) {
            \App\Centresidence\Models\StkPending::record('infra_bill', $cid, ['owner_user_id' => $owner], 500.0);
        }

        $body = ['Body' => ['stkCallback' => [
            'ResultCode' => $resultCode,
            'CheckoutRequestID' => $cid,
            'CallbackMetadata' => ['Item' => [['Name' => 'MpesaReceiptNumber', 'Value' => 'ABC123']]],
        ]]];
        // Genuine callbacks carry the server-only token embedded in the ResultURL at push.
        $token = $token ?? b2cCallbackSecret();
        $url   = "/api/centresidence/infra-bill/{$owner}/callback" . ($token !== '' ? "?token={$token}" : '');
        $req   = Request::create($url, 'POST', [], [], [], [], json_encode($body));

        return app(InfraBillCallbackController::class)->__invoke($req, $owner, $this->svc());
    }

    public function test_outstanding_sums_infra_only(): void
    {
        $this->invoice(1);

        $out = $this->svc()->outstanding(1);
        $this->assertSame(500.0, $out['total']); // 300 + 200 infra, not the 1500 plan
        $this->assertCount(1, $out['invoices']);
    }

    public function test_mark_paid_settles_infra_and_clears_fallback(): void
    {
        $inv = $this->invoice(1);

        $count = $this->svc()->markPaid(1, 'ABC123');

        $inv->refresh();
        $this->assertSame(1, $count);
        $this->assertSame(CentresidenceCommissionInvoice::STATUS_PAID, $inv->status);
        $this->assertNotNull($inv->paid_at);
        $this->assertSame('300.00', (string) $inv->metered_paid_total);
        $this->assertSame(0.0, $this->svc()->outstanding(1)['total']); // nothing left owed
    }

    public function test_success_callback_marks_paid(): void
    {
        $this->invoice(1);

        $this->fireCallback(1, 0);

        $this->assertSame(
            CentresidenceCommissionInvoice::STATUS_PAID,
            CentresidenceCommissionInvoice::where('owner_id', 1)->first()->status
        );
    }

    public function test_failed_callback_leaves_bill_unpaid(): void
    {
        $this->invoice(1);

        $this->fireCallback(1, 1032); // user cancelled

        $this->assertSame(500.0, $this->svc()->outstanding(1)['total']);
    }

    public function test_initiate_log_driver_settles_immediately_without_stk(): void
    {
        config(['centresidence.collections.driver' => 'log']);
        $this->invoice(1);

        $result = $this->svc()->initiate(1);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['settled'] ?? false);
        $this->assertSame(0.0, $this->svc()->outstanding(1)['total']); // bill cleared
    }

    public function test_callback_is_idempotent(): void
    {
        $this->invoice(1);

        $this->fireCallback(1, 0);
        $second = $this->svc()->markPaid(1); // re-fire → nothing left to settle

        $this->assertSame(0, $second);
        $this->assertSame(0.0, $this->svc()->outstanding(1)['total']);
    }

    public function test_forged_callback_without_valid_token_is_rejected(): void
    {
        $this->invoice(1);

        // A crafted success callback WITH a recorded push + correct CheckoutRequestID but
        // NO server token (or a wrong one) must clear nothing — the token, embedded in the
        // ResultURL at push, is never exposed to the payer.
        $this->fireCallback(1, 0, 'CHECKOUT-INFRA-1', recordPush: true, token: '');
        $this->assertSame(500.0, $this->svc()->outstanding(1)['total']);

        $this->fireCallback(1, 0, 'CHECKOUT-INFRA-1', recordPush: false, token: 'wrong-token');
        $this->assertSame(500.0, $this->svc()->outstanding(1)['total']);

        // With the correct token it settles (proves the gate isn't blanket-blocking).
        $this->fireCallback(1, 0);
        $this->assertSame(0.0, $this->svc()->outstanding(1)['total']);
    }

    public function test_forged_callback_without_a_push_is_rejected(): void
    {
        $this->invoice(1);

        // No pending push recorded → simulates a crafted callback hitting the public
        // webhook. It must clear nothing (debt stays, readonly gate stays down).
        $this->fireCallback(1, 0, 'FORGED-CID', recordPush: false);

        $this->assertSame(500.0, $this->svc()->outstanding(1)['total']);
    }
}
