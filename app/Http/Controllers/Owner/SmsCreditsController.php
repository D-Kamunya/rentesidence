<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Jobs\RetrySmsJob;
use App\Models\Owner;
use App\Models\SmsHistory;
use App\Models\OwnerCreditTransaction;
use App\Services\Sms\SmsCreditsService;
use Illuminate\Http\Request;

class SmsCreditsController extends Controller
{
    public function index()
    {
        $owner    = Owner::where('user_id', auth()->id())->firstOrFail();
        $balance  = $owner->sms_credits;
        $creditPools = SmsCreditsService::breakdown(auth()->id()); // granted (resets) + purchased (kept)

        $transactions = OwnerCreditTransaction::where('owner_user_id', auth()->id())
            ->where('bucket', 'sms')
            ->latest()
            ->paginate(15, ['*'], 'tx_page');

        $stats = OwnerCreditTransaction::where('owner_user_id', auth()->id())
            ->where('bucket', 'sms')
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
            'balance', 'creditPools', 'transactions', 'stats',
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

        // Don't let a retry fire while credits are still depleted — it would just
        // fail again and re-log. Require at least one credit (the UI hides the
        // button too, but guard server-side regardless).
        if (SmsCreditsService::balance(auth()->id()) < 1) {
            return back()->with('error', __('Top up your SMS credits first — retrying now would just fail again.'));
        }

        RetrySmsJob::dispatch($record->id, auth()->id());

        return back()->with('success', __('SMS queued for retry.'));
    }

    public function retryAll()
    {
        $failed = SmsCreditsService::getRetryableFailed(auth()->id(), 30);

        if ($failed->isEmpty()) {
            return back()->with('info', __('No failed messages to retry.'));
        }

        $balance = SmsCreditsService::balance(auth()->id());
        if ($balance < 1) {
            return back()->with('error', __('Top up your SMS credits first — retrying now would just fail again.'));
        }

        // Only queue as many as the current balance can actually send, so the
        // overflow doesn't immediately re-fail. The rest wait for the next top-up.
        $toRetry = $failed->take($balance);
        foreach ($toRetry as $record) {
            RetrySmsJob::dispatch($record->id, auth()->id());
        }

        $queued    = $toRetry->count();
        $remaining = $failed->count() - $queued;

        $message = $remaining > 0
            ? __(':queued message(s) queued for retry. Top up to retry the remaining :remaining.', ['queued' => $queued, 'remaining' => $remaining])
            : __(':count message(s) queued for retry.', ['count' => $queued]);

        return back()->with('success', $message);
    }
}