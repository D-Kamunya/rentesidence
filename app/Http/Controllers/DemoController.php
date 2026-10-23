<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Entry/exit for the guided-demo sandbox. `enter()` is a public, tokenless link an affiliate can
 * share or launch on a call — it signs the viewer into the seeded read-only demo owner and flags
 * the session as demo_mode (which the DemoReadonly guard then enforces + the guided tour auto-runs
 * against). `exit()` tears the session down cleanly. The demo owner is a fabricated account with no
 * real data, money, or gateway creds, so even without the read-only guard the blast radius is nil;
 * the guard makes it genuinely untouchable.
 */
class DemoController extends Controller
{
    public function enter(Request $request)
    {
        $ownerId = (int) getOption('demo_sandbox_owner_user_id');
        if (! $ownerId || ! User::where('id', $ownerId)->exists()) {
            return redirect()->route('login')->with('error', __('The demo is not available right now.'));
        }

        Auth::loginUsingId($ownerId);
        $request->session()->put('demo_mode', true);
        $request->session()->put('demo_autostart_tour', true);

        // Attribution (phase 5): carry the affiliate referral code / lead id through the demo.
        if ($request->filled('ref')) {
            $request->session()->put('demo_ref', (string) $request->query('ref'));
        }
        if ($request->filled('lead')) {
            $request->session()->put('demo_lead', (int) $request->query('lead'));
        }

        return redirect()->route('owner.dashboard');
    }

    public function exit(Request $request)
    {
        $request->session()->forget(['demo_mode', 'demo_autostart_tour', 'demo_ref', 'demo_lead']);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', __('You have left the demo.'));
    }
}
