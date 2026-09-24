<?php

namespace App\Services;

use App\Models\UserTour;
use Illuminate\Support\Facades\Schema;

/**
 * One-time onboarding tours, keyed by tour id so the same rail serves the tenant welcome tour and
 * any future role tours. Fully guarded — a missing table (bare install) degrades to "not shown",
 * never an error.
 */
class OnboardingTourService
{
    public function completed(int $userId, string $key): bool
    {
        try {
            if (! Schema::hasTable('user_tours')) {
                return false;
            }

            return UserTour::where('user_id', $userId)->where('tour_key', $key)
                ->whereNotNull('completed_at')->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function markComplete(int $userId, string $key): void
    {
        try {
            if (! Schema::hasTable('user_tours')) {
                return;
            }

            UserTour::updateOrCreate(
                ['user_id' => $userId, 'tour_key' => $key],
                ['completed_at' => now()]
            );
        } catch (\Throwable $e) {
            // never break the page
        }
    }
}
