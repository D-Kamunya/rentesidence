<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeadSuggestionService;
use App\Models\LeadSuggestion;
use App\Models\Lead;
use App\Models\User;
use App\Jobs\Mail\SendLeadSuggestionsMail;
use Carbon\Carbon;

class GenerateLeadSuggestions extends Command
{
    protected $signature = 'leads:generate-suggestions {--notify : Send email notifications to affiliates}';
    protected $description = 'Generate lead suggestions for affiliates based on lead status and activity';

    public function handle()
    {
        $this->info('Starting lead suggestion generation...');

        // Auto-expire old suggestions FIRST
        $expiredCount = LeadSuggestion::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expiredCount} old suggestions");

        $service = new LeadSuggestionService();
        $leads = Lead::with(['activities', 'affiliate'])
            ->whereNotNull('affiliate_id')       // Exclude unclaimed marketplace leads
            ->whereNotIn('status', Lead::CLOSED_STATUSES) // Skip rejected, expired and lost leads
            ->get();

        $suggestionCount = 0;
        $affiliatesWithSuggestions = [];

        foreach ($leads as $lead) {
            $lastTime = $lead->last_activity_at ?? $lead->updated_at ?? $lead->created_at;
            $hours = now()->diffInHours($lastTime);

            // Limit suggestions per lead
            $activeSuggestions = $lead->suggestions()
                ->where('status', 'pending')
                ->count();

            if ($activeSuggestions >= 2) continue;

            $createdSuggestion = false;

            // =========================
            // ACTIVE STATUS
            // =========================
            if ($lead->status === 'active' && $hours >= 48) {
                switch ($lead->temperature) {
                    case 'cold':
                        $service->createSuggestion($lead,
                            '📧 Send introduction email(recommended) with brochure to ' . $lead->company->company_name,
                            'email',
                            'intro',
                            'medium',
                            3
                        );
                        $createdSuggestion = true;
                        break;

                    case 'warm':
                        $service->createSuggestion($lead,
                            '💬 Reach out via WhatsApp(recommended) and introduce system to ' . $lead->company->company_name,
                            'whatsapp',
                            'intro',
                            'high',
                            2
                        );
                        $createdSuggestion = true;
                        break;

                    case 'hot':
                        $service->createSuggestion($lead,
                            '🔥 Call(recommended) ' . $lead->company->company_name . ' immediately - Hot lead waiting!',
                            'call',
                            'intro',
                            'high',
                            1
                        );
                        $createdSuggestion = true;
                        break;
                }
            }

            // =========================
            // DEMO SCHEDULED
            // =========================
            if ($lead->status === 'demo_scheduled' && $lead->demo_scheduled_at) {
                $hoursUntilDemo = now()->diffInHours($lead->demo_scheduled_at, false);

                // Only create if demo is in the future
                if ($hoursUntilDemo > 0) {
                    if ($hoursUntilDemo <= 24 && $hoursUntilDemo > 12) {
                        $service->createSuggestion($lead,
                            '📅 Demo with ' . $lead->company->company_name . ' in 24 hours - Send email(recommended) reminder',
                            'email',
                            'reminder',
                            'medium',
                            3
                        );
                        $createdSuggestion = true;
                    }

                    if ($hoursUntilDemo <= 12 && $hoursUntilDemo > 2) {
                        $service->createSuggestion($lead,
                            '⏰ Demo with ' . $lead->company->company_name . ' in 12 hours - WhatsApp(recommended) reminder',
                            'whatsapp',
                            'reminder',
                            'high',
                            2
                        );
                        $createdSuggestion = true;
                    }

                    if ($hoursUntilDemo <= 2 && $hoursUntilDemo > 0) {
                        $service->createSuggestion($lead,
                            '📞 Demo with ' . $lead->company->company_name . ' in 2 hours - Call(recommended) to confirm!',
                            'call',
                            'reminder',
                            'high',
                            1
                        );
                        $createdSuggestion = true;
                    }
                }
            }

            // =========================
            // DEMO COMPLETED
            // =========================
            if ($lead->status === 'demo_completed' && $hours >= 12) {
                if (in_array($lead->temperature, ['hot', 'warm'])) {
                    $service->createSuggestion($lead,
                        '🔥 Strike while hot! Call(recommended) ' . $lead->company->company_name . ' and request trial',
                        'call',
                        'demo_complete',
                        'high',
                        1
                    );
                    $createdSuggestion = true;
                } else {
                    $service->createSuggestion($lead,
                        '📋 Follow up with ' . $lead->company->company_name . ' after demo',
                        'whatsapp',
                        'demo_complete',
                        'medium',
                        2
                    );
                    $createdSuggestion = true;
                }
            }


            // =========================
            // TRIAL
            // =========================
            if ($lead->status === 'trial' && $hours >= 72) {
                    $service->createSuggestion($lead,
                        '💬 Check in with ' . $lead->company->company_name . ' during trial',
                        'whatsapp',
                        'trial',
                        'medium',
                        2
                    );
                    $createdSuggestion = true;
                }

            // =========================
            // CONVERTED — RETENTION CHECK-IN
            // =========================
            if ($lead->status === 'converted') {
                // Monthly check-in — fire once every 30 days
                $lastCheckIn = $lead->activities()
                    ->whereIn('type', ['call_made', 'whatsapp_sent', 'email_sent', 'note_added'])
                    ->latest()
                    ->first();
            
                $daysSinceCheckIn = $lastCheckIn
                    ? now()->diffInDays($lastCheckIn->created_at)
                    : now()->diffInDays($lead->updated_at);
            
                if ($daysSinceCheckIn >= 30) {
                    if ($lead->temperature === 'hot') {
                        $service->createSuggestion($lead,
                            '🏆 Check in with ' . $lead->company->company_name . ' — ensure they\'re getting full value and introduce latest features',
                            'call',
                            'retention',
                            'medium',
                            3
                        );
                    } else {
                        $service->createSuggestion($lead,
                            '💬 Monthly check-in with ' . $lead->company->company_name . ' — usage, cashflow tools & new features',
                            'whatsapp',
                            'retention',
                            'low',
                            4
                        );
                    }
                    $createdSuggestion = true;
                }
            }

            // =========================
            // LOST / EXPIRED
            // =========================
            if (in_array($lead->status, ['lost', 'expired']) && $hours >= 168) { // 1 week
                $service->createSuggestion($lead,
                    '🔄 Re-engage ' . $lead->company->company_name . ' with soft email(recommended) outreach',
                    'email',
                    'reengage',
                    'low',
                    5
                );
                $createdSuggestion = true;
            }

            // =========================
            // TRIAL EXPIRED ACTIVITY
            // =========================
            $trialExpiredActivity = $lead->activities()
                ->where('type', 'trial_expired')
                ->where('created_at', '>=', now()->subDays(2))
                ->first();

            if ($trialExpiredActivity) {
                $service->createSuggestion($lead,
                    '⏰ Trial expired for ' . $lead->company->company_name . ' - call(recommended) for convertion or extention',
                    'call',
                    'trial_expired',
                    'high',
                    1
                );
                $createdSuggestion = true;
            }

            // Track for notifications
            if ($createdSuggestion && $lead->affiliate) {
                $suggestionCount++;
                if (!isset($affiliatesWithSuggestions[$lead->affiliate_id])) {
                    $affiliatesWithSuggestions[$lead->affiliate_id] = [
                        'affiliate' => $lead->affiliate,
                        'suggestions' => 0,
                        'high_priority' => 0
                    ];
                }
                $affiliatesWithSuggestions[$lead->affiliate_id]['suggestions']++;
                
                // Count high priority
                $latestSuggestion = $lead->suggestions()->latest()->first();
                if ($latestSuggestion && $latestSuggestion->priority === 'high') {
                    $affiliatesWithSuggestions[$lead->affiliate_id]['high_priority']++;
                }
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