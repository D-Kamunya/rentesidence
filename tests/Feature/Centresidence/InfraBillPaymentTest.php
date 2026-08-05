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

    private function fireCallback(int $owner, int $resultCode)
    {
        $body = ['Body' => ['stkCallback' => [
            'ResultCode' => $resultCode,
            'CallbackMetadata' => ['Item' => [['Name' => 'MpesaReceiptNumber', 'Value' => 'ABC123']]],
        ]]];
        $req = Request::create("/api/centresidence/infra-bill/{$owner}/callback", 'POST', [], [], [], [], json_encode($body));

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
}
