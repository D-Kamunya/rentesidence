<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Admin settings for the agreement (e-sign) monetization: the monthly free quota for
 * free-plan owners and the per-credit price. Both read via getOption with sane defaults
 * (10 / 50), so the feature works before anything is set here.
 */
class AgreementSettingsController extends Controller
{
    public function index()
    {
        $data['pageTitle']  = __('Agreement Settings');
        $data['freeQuota']  = (int) getOption('agreement_free_quota', 10);
        $data['price']      = (float) getOption('agreement_price', 50);

        return view('admin.agreement.settings', $data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'agreement_free_quota' => 'required|integer|min:0|max:1000',
            'agreement_price'      => 'required|numeric|min:0',
        ]);

        setOption('agreement_free_quota', (int) $request->agreement_free_quota);
        setOption('agreement_price', (float) $request->agreement_price);

        return back()->with('success', __('Agreement settings saved.'));
    }
}
