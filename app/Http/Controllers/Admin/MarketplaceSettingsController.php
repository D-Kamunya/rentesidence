<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Admin settings for the marketplace escrow/settlement timing. Both values back the
 * escrow model: platform holds a paid order's proceeds and only releases the owner's
 * net once it's safe to. They read via getOption with sane defaults (2 / 7), so the
 * marketplace works before anything is set here (config-not-code).
 *
 *  - marketplace_return_window_days: after an order is marked DELIVERED, how long the
 *    buyer can still cancel/return AND how long we hold before releasing to the owner.
 *    Same value by design — we release exactly when the buyer can no longer pull out.
 *  - marketplace_auto_release_days: the SAFETY-NET grace for orders paid but never marked
 *    delivered, so money never sticks in escrow. Kept longer than the return window on
 *    purpose (releasing an undelivered order early is the dangerous direction).
 */
class MarketplaceSettingsController extends Controller
{
    public function index()
    {
        $data['pageTitle']         = __('Marketplace Settings');
        $data['returnWindowDays']  = (int) getOption('marketplace_return_window_days', 2);
        $data['autoReleaseDays']   = (int) getOption('marketplace_auto_release_days', 7);
        $data['subMarketplaceSettingActiveClass'] = 'active'; // highlights the item in the settings internal sidebar

        return view('admin.marketplace.settings', $data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'marketplace_return_window_days' => 'required|integer|min:0|max:60',
            'marketplace_auto_release_days'  => 'required|integer|min:1|max:120',
        ]);

        // Guardrail: the undelivered safety-net grace must never be SHORTER than the
        // return window, otherwise we could auto-release an undelivered order before a
        // delivered one — which inverts the escrow's whole risk model.
        $returnWindow = (int) $request->marketplace_return_window_days;
        $autoRelease  = (int) $request->marketplace_auto_release_days;

        if ($autoRelease < $returnWindow) {
            return back()
                ->withInput()
                ->with('error', __('The undelivered auto-release grace cannot be shorter than the return/settlement window.'));
        }

        setOption('marketplace_return_window_days', $returnWindow);
        setOption('marketplace_auto_release_days', $autoRelease);

        return back()->with('success', __('Marketplace settings saved.'));
    }
}
