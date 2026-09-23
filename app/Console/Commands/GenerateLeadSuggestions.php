<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SuggestionService;
use App\Services\Suggestions\SuggestionRuleEngine;
use App\Models\LeadSuggestion;
use App\Models\Lead;
use App\Jobs\Mail\SendLeadSuggestionsMail;

/**
 * Thin orchestrator: expire stale suggestions, load the eligible leads, ask the
 * SuggestionRuleEngine for each lead's next-best actions, persist them, and
 * notify affiliates. All the RULES live in the per-product strategies under
 * App\Services\Suggestions (the Affiliate-OS seam) — not here.
 */
class GenerateLeadSuggestions extends Command
{
    protected $signature = 'leads:generate-suggestions {--notify : Send email notifications to affiliates}';
    protected $description = 'Generate lead suggestions for affiliates based on lead status and activity';

    public function handle(SuggestionRuleEngine $engine, SuggestionService $service)
    {
        $this->info('Starting lead suggestion generation...');

        // Auto-expire old suggestions first.
        $expiredCount = LeadSuggestion::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
        $this->info("Expired {$expiredCount} old suggestions");

        // Eager-load activities + suggestions so the per-lead loop makes no extra queries.
        $leads = Lead::with(['activities', 'affiliate', 'suggestions'])
            ->whereNotNull('affiliate_id')             // exclude unclaimed marketplace leads
            ->whereNotIn('status', Lead::CLOSED_STATUSES) // skip rejected / expired / lost
            ->get();

        $suggestionCount = 0;
        $affiliatesWithSuggestions = [];

        foreach ($leads as $lead) {
            // Cap: at most 2 pending suggestions per lead (count the loaded relation).
            $pending = $lead->relationLoaded('suggestions')
                ? $lead->suggestions->where('status', 'pending')->count()
                : $lead->suggestions()->where('status', 'pending')->count();
            if ($pending >= 2) {
                continue;
            }

            $candidates = $engine->candidatesFor($lead);
            if (empty($candidates)) {
                continue;
            }

            // Count only suggestions actually PERSISTED this run (createSuggestion
            // returns false when dedup skipped an identical pending one) — so the
            // affiliate email reports real new actions, not leads-with-candidates.
            $createdForLead = 0;
            $createdHighForLead = 0;
            foreach ($candidates as $c) {
                if ($service->createSuggestion($lead, $c->message, $c->actionType, $c->category, $c->priority, $c->expiresDays)) {
                    $createdForLead++;
                    if ($c->priority === 'high') {
                        $createdHighForLead++;
                    }
                }
            }

            // Nothing new persisted (all deduped) → don't count or notify.
            if ($createdForLead === 0) {
                continue;
            }

            $suggestionCount += $createdForLead;

            if ($lead->affiliate) {
                if (!isset($affiliatesWithSuggestions[$lead->affiliate_id])) {
                    $affiliatesWithSuggestions[$lead->affiliate_id] = [
                        'affiliate' => $lead->affiliate,
                        'suggestions' => 0,
                        'high_priority' => 0,
                    ];
                }
                $affiliatesWithSuggestions[$lead->affiliate_id]['suggestions'] += $createdForLead;
                $affiliatesWithSuggestions[$lead->affiliate_id]['high_priority'] += $createdHighForLead;
            }
        }

        $this->info("Generated {$suggestionCount} new suggestions for " . count($affiliatesWithSuggestions) . " affiliates");

        // Send email notifications
        if ($this->option('notify') && count($affiliatesWithSuggestions) > 0) {
            if (getOption('send_email_status', 0) != ACTIVE) {
                $this->warn('Email notifications disabled in settings');
            } else {
                foreach ($affiliatesWithSuggestions as $data) {
                    SendLeadSuggestionsMail::dispatch(
                        $data['affiliate']->email,
                        $data['affiliate']->first_name,
                        $data['suggestions'],
                        $data['high_priority'],
                    );
                }
                $this->info('Queued notifications for ' . count($affiliatesWithSuggestions) . ' affiliates');
            }
        }

        return 0;
    }
}
