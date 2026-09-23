<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadSuggestion;
use Illuminate\Support\Collection;

/**
 * The suggestion domain in one place: create (from the rule engine), complete
 * (from a channel action or manually), and dismiss. Consolidates logic that was
 * previously split across LeadSuggestionService, ActionExecutionController, and
 * LeadSuggestionController. Pairs with App\Services\Suggestions\SuggestionRuleEngine
 * (which decides WHAT to suggest); this persists and closes them.
 */
class SuggestionService
{
    /**
     * Persist a suggestion for a lead, unless an identical pending one already
     * exists (dedup). Returns true when a row was actually created, false when
     * the dedup skipped it — so callers can count only real new suggestions.
     */
    public function createSuggestion(Lead $lead, string $message, string $actionType, string $category, string $priority, int $expiresDays): bool
    {
        $exists = LeadSuggestion::where('lead_id', $lead->id)
            ->where('message', $message)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return false;
        }

        LeadSuggestion::create([
            'lead_id'      => $lead->id,
            'affiliate_id' => $lead->affiliate_id,
            'message'      => $message,
            'action_type'  => $actionType,
            'category'     => $category,
            'priority'     => $priority,
            'status'       => 'pending',
            'expires_at'   => now()->addDays($expiresDays),
        ]);

        return true;
    }

    /**
     * Complete the suggestion the affiliate acted from. When we know WHICH one
     * (its id threaded through the channel action), complete THAT one regardless
     * of channel — the UI offers all channels, so matching on action_type wrongly
     * left a non-matching suggestion open. Falls back to channel matching only
     * when there's no suggestion context. Only touches pending rows (idempotent).
     */
    public function completeForLead(int $leadId, string $channel, ?int $suggestionId): void
    {
        $query = LeadSuggestion::where('lead_id', $leadId)->where('status', 'pending');

        if ($suggestionId) {
            $query->whereKey($suggestionId);
        } else {
            $query->where('action_type', $channel);
        }

        $query->update([
            'status'         => 'completed',
            'executed_at'    => now(),
            'execution_type' => $channel,
            'executed_by'    => auth()->id(),
        ]);
    }

    /** Mark a suggestion complete manually — the affiliate handled it off-platform. */
    public function completeManually(LeadSuggestion $suggestion): void
    {
        $suggestion->update([
            'status'         => 'completed',
            'executed_at'    => now(),
            'execution_type' => 'manual',
            'executed_by'    => auth()->id(),
        ]);
    }

    /** Dismiss a suggestion — the affiliate is choosing not to act on it. */
    public function dismiss(LeadSuggestion $suggestion): void
    {
        $suggestion->update([
            'status'      => 'dismissed',
            'executed_by' => auth()->id(),
        ]);
    }

    /**
     * Prepare each suggestion's per-channel templates + the engine-recommended
     * channel, so the view just renders (moves heavy per-suggestion prep out of
     * the show blade).
     *
     * @return array<int,array{suggestion:LeadSuggestion,whatsapp:Collection,email:Collection,call:Collection,recommended:string}>
     */
    public function channelsFor(Collection $suggestions, Collection $templatesByCategory): array
    {
        return $suggestions->map(function (LeadSuggestion $s) use ($templatesByCategory) {
            $templates = $templatesByCategory->get($s->category, collect());

            return [
                'suggestion'  => $s,
                'whatsapp'    => $templates->where('action_type', 'whatsapp')->values(),
                'email'       => $templates->where('action_type', 'email')->values(),
                'call'        => $templates->where('action_type', 'call')->values(),
                'recommended' => $s->action_type,
            ];
        })->all();
    }
}
