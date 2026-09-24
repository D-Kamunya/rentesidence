<?php

namespace App\Http\Controllers;

use App\Services\OnboardingTourService;
use Illuminate\Http\Request;

/** Marks a one-time onboarding tour complete for the signed-in user (role-agnostic). */
class TourController extends Controller
{
    public function complete(Request $request, OnboardingTourService $tours)
    {
        $request->validate(['key' => 'required|string|max:60']);

        $tours->markComplete((int) auth()->id(), $request->input('key'));

        return response()->json(['ok' => true]);
    }
}
