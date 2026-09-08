<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * The owner Terms & Conditions accept-gate. show() renders the current T&C + an accept form;
 * accept() records who/when/which-version so the acceptance binds and is auditable. Paired with
 * the EnsureTermsAccepted middleware on the owner group.
 */
class TermsAcceptanceController extends Controller
{
    public function show()
    {
        return view('owner.terms.accept', [
            'pageTitle' => __('Terms & Conditions'),
            'terms'     => getOption('terms_conditions'),
            'version'   => (string) getOption('terms_version', '1.0'),
            'appName'   => getOption('app_name') ?: 'Centresidence',
        ]);
    }

    public function accept(Request $request)
    {
        $request->validate(['accept' => 'accepted'], [
            'accept.accepted' => __('Please tick the box to accept the Terms & Conditions.'),
        ]);

        $request->user()->forceFill([
            'terms_accepted_at'      => now(),
            'terms_accepted_version' => (string) getOption('terms_version', '1.0'),
        ])->save();

        return redirect()->route('owner.dashboard')
            ->with('success', __('Thank you — your acceptance of the Terms & Conditions has been recorded.'));
    }
}
