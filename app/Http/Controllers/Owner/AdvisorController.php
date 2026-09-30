<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerAdvisor\OwnerAdvisorService;
use Illuminate\Http\Request;

/** Owner Upgrade Advisor — dismiss/snooze a suggestion (server-side, cross-device). */
class AdvisorController extends Controller
{
    public function dismiss(Request $request, OwnerAdvisorService $advisor)
    {
        $request->validate(['key' => 'required|string|max:60']);

        $advisor->dismiss((int) auth()->id(), $request->input('key'));

        return response()->json(['ok' => true]);
    }
}
