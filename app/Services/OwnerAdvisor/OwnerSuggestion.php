<?php

namespace App\Services\OwnerAdvisor;

/**
 * One computed owner suggestion (a value object, not persisted). Mirrors the affiliate
 * SuggestionCandidate in spirit but is its own type so the two rails never entangle.
 */
class OwnerSuggestion
{
    public function __construct(
        public string $key,          // stable id for dedup + dismissal
        public string $category,     // upgrade | financing | cap | sms
        public string $priority,     // high | medium | low
        public string $title,
        public string $body,
        public string $ctaLabel,
        public string $ctaUrl,
        public array $meta = []      // computed numbers for display/telemetry
    ) {
    }

    /** Sort weight — high first. */
    public function weight(): int
    {
        return ['high' => 3, 'medium' => 2, 'low' => 1][$this->priority] ?? 0;
    }

    public function toArray(): array
    {
        return [
            'key'       => $this->key,
            'category'  => $this->category,
            'priority'  => $this->priority,
            'title'     => $this->title,
            'body'      => $this->body,
            'cta_label' => $this->ctaLabel,
            'cta_url'   => $this->ctaUrl,
            'meta'      => $this->meta,
        ];
    }
}
