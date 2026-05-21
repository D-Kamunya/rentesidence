<?php

namespace App\Jobs;

use App\Models\SmsHistory;
use App\Services\SmsMail\AdvantaSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetrySmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $smsHistoryId,
        private int $ownerUserId
    ) {}

    public function handle(): void
    {
        $record = SmsHistory::find($this->smsHistoryId);

        if (!$record) {
            Log::warning("RetrySmsJob: sms_history record {$this->smsHistoryId} not found.");
            return;
        }

        $record->update([
            'status'        => SMS_STATUS_PENDING,
            'error' => 'Retrying...',
        ]);

        AdvantaSmsService::sendSms(
            [$record->mobile],
            $record->message,
            $this->ownerUserId
        );
    }
}