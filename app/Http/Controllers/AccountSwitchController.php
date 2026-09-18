<?php

namespace App\Http\Controllers;

use App\Services\AffiliateGraduationService;
use Illuminate\Support\Facades\Auth;

/**
 * The graduation account switch: a person who upgraded from tenant to affiliate holds two linked
 * accounts and moves between them here — a scoped, self-only session swap (never a login to an
 * arbitrary account). The target is resolved strictly from the verified graduation link, so a
 * user can only ever switch into their own counterpart.
 */
class AccountSwitchController extends Controller
{
    public function switch()
    {
        $target = app(AffiliateGraduationService::class)->switchTargetFor(auth()->user());

        if (! $target) {
            return back()->with('error', __('You don\'t have a linked account to switch to.'));
        }

        Auth::login($target['user']);

        return redirect()->route($target['route'])
            ->with('success', __('Switched account.'));
    }
}
