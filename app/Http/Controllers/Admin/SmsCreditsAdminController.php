<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\OwnerCreditTransaction;
use App\Services\Sms\SmsCreditsService;
use Illuminate\Http\Request;

class SmsCreditsAdminController extends Controller
{
    public function index()
    {
        // The card edits the FREE-TIER SMS rate. What actually charges free owners is the Free
        // package's per-credit price (CreditService reads packages.sms_price_per_credit); the global
        // sms_credit_price option is only the fallback for plans that don't set their own. Show the
        // Free package's rate when set (that's why the free tier reads 3.00, not the stale global 2.00).
        $freePackage  = function_exists('defaultFreePackage') ? defaultFreePackage() : null;
        $pricePerSms  = $freePackage && $freePackage->sms_price_per_credit !== null
            ? (float) $freePackage->sms_price_per_credit
            : (float) getOption('sms_credit_price', 3.00);
        $lowThreshold = getOption('sms_low_credit_threshold', 30);

        $recentPurchases = OwnerCreditTransaction::where('bucket', 'sms')
            ->whereIn('type', ['purchase', 'manual_topup', 'package_grant'])
            ->where('status', 'success')
            ->with('owner.user')
            ->latest()
            ->paginate(20);

        $totalRevenue = OwnerCreditTransaction::where('bucket', 'sms')
            ->where('type', 'purchase')
            ->where('status', 'success')
            ->sum('amount_paid');

        return view('admin.sms-credits.index', compact(
            'pricePerSms', 'lowThreshold', 'recentPurchases', 'totalRevenue'
        ));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'sms_credit_price'         => 'required|numeric|min:0.01',
            'sms_low_credit_threshold' => 'required|integer|min:1',
        ]);

        // Write the free-tier rate to its source of truth — the Free package (what actually charges
        // free owners) — and keep the global fallback option in sync so nothing reads a stale value.
        $freePackage = function_exists('defaultFreePackage') ? defaultFreePackage() : null;
        if ($freePackage) {
            $freePackage->sms_price_per_credit = $request->sms_credit_price;
            $freePackage->save();
        }

        // setOption — NOT updateOption (which doesn't exist; calling it 500'd every save).
        setOption('sms_credit_price',         $request->sms_credit_price);
        setOption('sms_low_credit_threshold', $request->sms_low_credit_threshold);

        return back()->with('success', __('SMS credit settings updated.'));
    }

    public function manualTopup(Request $request)
    {
        $request->validate([
            'owner_user_id' => 'required|exists:owners,user_id',
            'quantity'      => 'required|integer|min:1',
            'note'          => 'nullable|string|max:255',
        ]);
        SmsCreditsService::addCredits(
            $request->owner_user_id, $request->quantity,
            'manual_topup', 0, '',
            $request->note ?? 'Admin manual top-up'
        );
        return back()->with(
            'success',
            $request->quantity . ' credits added successfully.'
        );
    }
}