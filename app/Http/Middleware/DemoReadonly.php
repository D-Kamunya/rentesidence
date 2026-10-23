<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Hard read-only guard for the guided-demo session. When session('demo_mode') is set, every
 * state-changing request (anything but GET/HEAD/OPTIONS) is refused BEFORE it reaches a
 * controller — so a demo viewer can never mutate the demo account, trigger an M-Pesa/STK push,
 * send SMS, or run any write, whatever route they reach. Registered globally in the `web` group
 * so there is no surface it does not cover. The client also blocks form submits for a friendly
 * toast; this is the server backstop, and the thing that actually makes the sandbox safe.
 */
class DemoReadonly
{
    /** Relative paths always allowed, even for a non-GET, in demo mode (leaving the demo). */
    private const ALLOW = ['demo/exit', 'logout'];

    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->get('demo_mode')) {
            return $next($request);
        }

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');
        if (in_array($path, self::ALLOW, true)) {
            return $next($request);
        }

        $msg = __('This is a read-only live demo — changes are switched off here. Start free to use it for real.');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => false, 'demo' => true, 'message' => $msg], 200);
        }

        return redirect()->back()->with('demo_notice', $msg);
    }
}
