<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\PartnerRemittanceBatch;
use App\Centresidence\Services\PartnerRemittanceService;
use App\Http\Controllers\Centresidence\PartnerRemittanceCallbackController;
use Illuminate\Http\Request;

/**
 * B1 — partner remittance confirmation. A B2B payout leaves the batch SENT
 * (request accepted); the async M-Pesa result callback then confirms it landed
 * (CONFIRMED) or flags it FAILED. Idempotent — only a SENT batch transitions.
 */
class PartnerRemittanceConfirmationTest extends CentresidenceDatabaseTestCase
{
    private function partner(): FinancePartner
    {
        return FinancePartner::create([
            'company_name' => 'Acme Bank',
            'status' => FinancePartner::STATUS_ACTIVE,
            'settlement_account_details' => ['type' => 'mpesa_paybill', 'paybill' => '400200', 'account' => 'ACME'],
        ]);
    }

    private function sentBatch(): PartnerRemittanceBatch
    {
        return PartnerRemittanceBatch::create([
            'finance_partner_id' => $this->partner()->id,
            'total_amount' => 20000,
            'status' => PartnerRemittanceBatch::STATUS_SENT,
            'reference' => 'AG_CONVERSATION_ID', // provisional ConversationID from send()
        ]);
    }

    private function fireCallback(int $batchId, array $result)
    {
        $req = Request::create("/api/centresidence/remittance/{$batchId}/callback", 'POST', [], [], [], [], json_encode(['Result' => $result]));

        return app(PartnerRemittanceCallbackController::class)
            ->__invoke($req, $batchId, app(PartnerRemittanceService::class));
    }

    private function successResult(): array
    {
        return [
            'ResultCode' => 0,
            'ResultDesc' => 'The service request is processed successfully.',
            'ConversationID' => 'AG_CONVERSATION_ID', // must echo the batch's provisional ref
            'TransactionID' => 'REC123',
            'ResultParameters' => ['ResultParameter' => [
                ['Key' => 'TransactionReceipt', 'Value' => 'REC123'],
            ]],
        ];
    }

    public function test_success_callback_confirms_the_batch(): void
    {
        $batch = $this->sentBatch();

        $this->fireCallback($batch->id, $this->successResult());

        $batch->refresh();
        $this->assertSame(PartnerRemittanceBatch::STATUS_CONFIRMED, $batch->status);
        $this->assertNotNull($batch->confirmation_received_at);
        $this->assertSame('REC123', $batch->reference); // provisional ref upgraded to the receipt
    }

    public function test_failure_callback_marks_failed_and_retryable(): void
    {
        $batch = $this->sentBatch();

        $this->fireCallback($batch->id, ['ResultCode' => 2001, 'ConversationID' => 'AG_CONVERSATION_ID', 'ResultDesc' => 'The initiator information is invalid.']);

        $batch->refresh();
        $this->assertSame(PartnerRemittanceBatch::STATUS_FAILED, $batch->status);
        $this->assertStringContainsString('initiator', $batch->notes);
    }

    public function test_forged_result_with_wrong_conversation_id_is_rejected(): void
    {
        $batch = $this->sentBatch();

        // A crafted result that doesn't echo the batch's ConversationID must not be able
        // to confirm (or fail) the payout.
        $this->fireCallback($batch->id, [
            'ResultCode' => 0, 'ConversationID' => 'WRONG_ID', 'TransactionID' => 'REC999',
        ]);

        $this->assertSame(PartnerRemittanceBatch::STATUS_SENT, $batch->fresh()->status);
    }

    public function test_callback_is_idempotent_no_double_confirm(): void
    {
        $batch = $this->sentBatch();

        $this->fireCallback($batch->id, $this->successResult());
        $firstConfirmedAt = $batch->fresh()->confirmation_received_at;

        // A re-fired callback must not change an already-confirmed batch.
        $this->fireCallback($batch->id, $this->successResult());

        $batch->refresh();
        $this->assertSame(PartnerRemittanceBatch::STATUS_CONFIRMED, $batch->status);
        $this->assertEquals($firstConfirmedAt, $batch->confirmation_received_at);
    }

    public function test_callback_does_not_touch_a_batch_that_was_not_sent(): void
    {
        $batch = $this->sentBatch();
        $batch->update(['status' => PartnerRemittanceBatch::STATUS_PREPARED]); // never sent

        $this->fireCallback($batch->id, $this->successResult());

        $this->assertSame(PartnerRemittanceBatch::STATUS_PREPARED, $batch->fresh()->status);
    }

    public function test_unknown_batch_is_acknowledged_without_error(): void
    {
        $response = $this->fireCallback(999999, $this->successResult());

        $this->assertSame(0, $response->getData(true)['ResultCode']);
    }
}
