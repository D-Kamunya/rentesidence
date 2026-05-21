<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Jobs\RetrySmsJob;
use App\Models\Owner;
use App\Models\SmsHistory;
use App\Models\SmsCreditTransaction;
use App\Services\Sms\SmsCreditsService;
use Illuminate\Http\Request;

class SmsCreditsController extends Controller
{
    public function index()
    {
        $owner   = Owner::where('user_id', auth()->id())->firstOrFail();
        $balance = $owner->sms_credits;

        $transactions = SmsCreditTransaction::where('owner_user_id', auth()->id())
            ->latest()
            ->paginate(15, ['*'], 'tx_page');

        $stats = SmsCreditTransaction::where('owner_user_id', auth()->id())
            ->selectRaw("
                SUM(CASE WHEN type = 'deduct'   AND status = 'success' THEN quantity ELSE 0 END) as total_sent,
                SUM(CASE WHEN status = 'failed'                         THEN quantity ELSE 0 END) as total_failed,
                SUM(CASE WHEN type IN ('purchase','package_grant','manual_topup') AND status = 'success' THEN quantity ELSE 0 END) as total_purchased
            ")
            ->first();

        $failedMessages = SmsCreditsService::getRetryableFailed(auth()->id(), 30);
        $pricePerSms    = (float) getOption('sms_credit_price', 1.00);
        $lowThreshold   = (int)   getOption('sms_low_credit_threshold', 50);

        return view('owner.sms-credits.index', compact(
            'balance', 'transactions', 'stats',
            'failedMessages', 'pricePerSms', 'lowThreshold'
        ));
    }

    public function retryOne(Request $request)
    {
        $request->validate(['sms_history_id' => 'required|integer|exists:sms_histories,id']);

        $record = SmsHistory::where('id', $request->sms_history_id)
            ->where('owner_user_id', auth()->id())
            ->where('status', SMS_STATUS_FAILED)
            ->where('error', 'Insufficient SMS credits')
            ->firstOrFail();

        RetrySmsJob::dispatch($record->id, auth()->id());

        return back()->with('success', __('SMS queued for retry.'));
    }

    public function retryAll()
    {
        $failed = SmsCreditsService::getRetryableFailed(auth()->id(), 30);

        if ($failed->isEmpty()) {
            return back()->with('info', __('No failed messages to retry.'));
        }

        foreach ($failed as $record) {
            RetrySmsJob::dispatch($record->id, auth()->id());
        }

        return back()->with('success', __(
            ':count messages queued for retry.',
            ['count' => $failed->count()]
        ));
    }
}