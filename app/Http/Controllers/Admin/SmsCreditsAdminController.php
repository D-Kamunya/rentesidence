<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\SmsCreditTransaction;
use App\Services\Sms\SmsCreditsService;
use Illuminate\Http\Request;

class SmsCreditsAdminController extends Controller
{
    public function index()
    {
        $pricePerSms  = getOption('sms_credit_price', 1.00);
        $lowThreshold = getOption('sms_low_credit_threshold', 50);

        $recentPurchases = SmsCreditTransaction::whereIn('type', ['purchase', 'manual_topup', 'package_grant'])
            ->where('status', 'success')
            ->with('owner.user')
            ->latest()
            ->paginate(20);

        $totalRevenue = SmsCreditTransaction::where('type', 'purchase')
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
        updateOption('sms_credit_price',         $request->sms_credit_price);
        updateOption('sms_low_credit_threshold', $request->sms_low_credit_threshold);
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