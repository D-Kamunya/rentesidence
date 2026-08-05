<?php

namespace App\Services\Suggestions;

use App\Models\Lead;
use App\Services\AffiliateOs\ProductRegistry;
use Carbon\Carbon;

/**
 * Picks the right per-product strategy for a lead and returns its suggestion
 * candidates. Today Centresidence property-sales is the only product; when the
 * Affiliate OS grows (Solidus, Nexterra, Crylac), register a strategy per
 * product here and dispatch on the lead's product — the command, persistence,
 * email, and leaderboard all stay unchanged. (See docs/affiliate-os-design.md.)
 */
class SuggestionRuleEngine
{
    /** @return SuggestionCandidate[] */
    public function candidatesFor(Lead $lead, ?Carbon $now = null): array
    {
        return $this->strategyFor($lead)->candidatesFor($lead, $now ?? Carbon::now());
    }

    private function strategyFor(Lead $lead): LeadSuggestionStrategy
    {
        // Dispatch on the lead's product via the registry; unregistered/legacy
        // products fall back to property-sales (ProductRegistry handles that).
        return ProductRegistry::suggestionStrategy($lead->product ?? ProductRegistry::default());
    }
}
