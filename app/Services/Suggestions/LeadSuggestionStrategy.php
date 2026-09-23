<?php

namespace App\Services\Suggestions;

use App\Models\Lead;
use Carbon\Carbon;

/**
 * A product's lead-suggestion rules. THE OS SEAM: each product (Centresidence,
 * Solidus, Nexterra, Crylac) supplies one of these with its own lead stages +
 * nudge rules, and the shared engine/command/leaderboard machinery stays the
 * same. Implementations are pure — they read a lead's loaded state and return
 * candidates; they never touch the database or persist anything.
 */
interface LeadSuggestionStrategy
{
    /** @return SuggestionCandidate[] */
    public function candidatesFor(Lead $lead, Carbon $now): array;
}
