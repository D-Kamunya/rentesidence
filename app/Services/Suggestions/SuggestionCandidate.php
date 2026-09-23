<?php

namespace App\Services\Suggestions;

/**
 * A suggestion a rule wants to exist for a lead. Pure value object — no DB,
 * no side effects. The command persists these via SuggestionService.
 */
class SuggestionCandidate
{
    public function __construct(
        public readonly string $message,
        public readonly string $actionType,
        public readonly string $category,
        public readonly string $priority,
        public readonly int $expiresDays,
    ) {
    }
}
