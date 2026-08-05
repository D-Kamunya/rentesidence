<?php

namespace App\Services\Suggestions;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Centresidence's property-sales lead-nudge rules — extracted verbatim from the
 * GenerateLeadSuggestions command. Reasons over stage × temperature × idle-hours
 * × time-to-demo and returns the next-best actions. Reads the lead's ALREADY
 * LOADED activities/company (no per-lead queries).
 */
class PropertySalesSuggestionStrategy implements LeadSuggestionStrategy
{
    public function candidatesFor(Lead $lead, Carbon $now): array
    {
        $name           = optional($lead->company)->company_name ?? __('this lead');
        $lastActivityAt = $lead->last_activity_at ?? $lead->updated_at ?? $lead->created_at;
        $idleHours      = $lastActivityAt ? $now->diffInHours($lastActivityAt) : 0;
        $activities     = $this->activities($lead);

        $out = [];

        // ── ACTIVE ──────────────────────────────────────────────
        if ($lead->status === 'active' && $idleHours >= 48) {
            $candidate = match ($lead->temperature) {
                'cold' => new SuggestionCandidate('📧 Send introduction email(recommended) with brochure to ' . $name, 'email', 'intro', 'medium', 3),
                'warm' => new SuggestionCandidate('💬 Reach out via WhatsApp(recommended) and introduce system to ' . $name, 'whatsapp', 'intro', 'high', 2),
                'hot'  => new SuggestionCandidate('🔥 Call(recommended) ' . $name . ' immediately - Hot lead waiting!', 'call', 'intro', 'high', 1),
                default => null,
            };
            if ($candidate) {
                $out[] = $candidate;
            }
        }

        // ── DEMO SCHEDULED ──────────────────────────────────────
        if ($lead->status === 'demo_scheduled' && $lead->demo_scheduled_at) {
            $hoursUntilDemo = $now->diffInHours($lead->demo_scheduled_at, false);
            if ($hoursUntilDemo <= 24 && $hoursUntilDemo > 12) {
                $out[] = new SuggestionCandidate('📅 Demo with ' . $name . ' in 24 hours - Send email(recommended) reminder', 'email', 'reminder', 'medium', 3);
            }
            if ($hoursUntilDemo <= 12 && $hoursUntilDemo > 2) {
                $out[] = new SuggestionCandidate('⏰ Demo with ' . $name . ' in 12 hours - WhatsApp(recommended) reminder', 'whatsapp', 'reminder', 'high', 2);
            }
            if ($hoursUntilDemo <= 2 && $hoursUntilDemo > 0) {
                $out[] = new SuggestionCandidate('📞 Demo with ' . $name . ' in 2 hours - Call(recommended) to confirm!', 'call', 'reminder', 'high', 1);
            }
        }

        // ── DEMO COMPLETED ──────────────────────────────────────
        if ($lead->status === 'demo_completed' && $idleHours >= 12) {
            $out[] = in_array($lead->temperature, ['hot', 'warm'])
                ? new SuggestionCandidate('🔥 Strike while hot! Call(recommended) ' . $name . ' and request trial', 'call', 'demo_complete', 'high', 1)
                : new SuggestionCandidate('📋 Follow up with ' . $name . ' after demo', 'whatsapp', 'demo_complete', 'medium', 2);
        }

        // ── TRIAL ───────────────────────────────────────────────
        if ($lead->status === 'trial' && $idleHours >= 72) {
            $out[] = new SuggestionCandidate('💬 Check in with ' . $name . ' during trial', 'whatsapp', 'trial', 'medium', 2);
        }

        // ── CONVERTED — retention check-in (every 30 days) ──────
        if ($lead->status === 'converted') {
            $lastCheckIn = $activities
                ->whereIn('type', ['call_made', 'whatsapp_sent', 'email_sent', 'note_added'])
                ->sortByDesc('created_at')
                ->first();
            $daysSinceCheckIn = $lastCheckIn && $lastCheckIn->created_at
                ? $now->diffInDays($lastCheckIn->created_at)
                : ($lead->updated_at ? $now->diffInDays($lead->updated_at) : PHP_INT_MAX);

            if ($daysSinceCheckIn >= 30) {
                $out[] = $lead->temperature === 'hot'
                    ? new SuggestionCandidate('🏆 Check in with ' . $name . ' — ensure they\'re getting full value and introduce latest features', 'call', 'retention', 'medium', 3)
                    : new SuggestionCandidate('💬 Monthly check-in with ' . $name . ' — usage, cashflow tools & new features', 'whatsapp', 'retention', 'low', 4);
            }
        }

        // ── LOST / EXPIRED (re-engage after a week) ─────────────
        if (in_array($lead->status, ['lost', 'expired']) && $idleHours >= 168) {
            $out[] = new SuggestionCandidate('🔄 Re-engage ' . $name . ' with soft email(recommended) outreach', 'email', 'reengage', 'low', 5);
        }

        // ── TRIAL EXPIRED activity in the last 2 days ───────────
        $trialExpired = $activities->first(fn ($a) => ($a->type ?? null) === 'trial_expired'
            && $a->created_at
            && $a->created_at->gte($now->copy()->subDays(2)));
        if ($trialExpired) {
            $out[] = new SuggestionCandidate('⏰ Trial expired for ' . $name . ' - call(recommended) for convertion or extention', 'call', 'trial_expired', 'high', 1);
        }

        return $out;
    }

    /** Use loaded activities if present; only query as a fallback (command eager-loads them). */
    private function activities(Lead $lead): Collection
    {
        return $lead->relationLoaded('activities') ? $lead->activities : $lead->activities()->get();
    }
}
