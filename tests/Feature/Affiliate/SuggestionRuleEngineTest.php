<?php

namespace Tests\Feature\Affiliate;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Services\Suggestions\PropertySalesSuggestionStrategy;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * The property-sales suggestion rules, tested purely — leads are built in memory
 * (no DB) and the strategy reads only their loaded state. Locks the extracted
 * behaviour so the fat-command → service refactor is safe.
 */
class SuggestionRuleEngineTest extends TestCase
{
    private function lead(array $attrs, ?Collection $activities = null): Lead
    {
        $lead = (new Lead())->forceFill($attrs);
        $lead->setRelation('company', null);
        $lead->setRelation('activities', $activities ?? collect());

        return $lead;
    }

    /** @return \App\Services\Suggestions\SuggestionCandidate[] */
    private function candidates(Lead $lead): array
    {
        return (new PropertySalesSuggestionStrategy())->candidatesFor($lead, now());
    }

    public function test_hot_active_idle_lead_gets_a_high_priority_call(): void
    {
        $c = $this->candidates($this->lead([
            'status' => 'active', 'temperature' => 'hot',
            'last_activity_at' => now()->subHours(50),
        ]));

        $this->assertCount(1, $c);
        $this->assertSame('call', $c[0]->actionType);
        $this->assertSame('high', $c[0]->priority);
    }

    public function test_recently_active_lead_gets_nothing(): void
    {
        $c = $this->candidates($this->lead([
            'status' => 'active', 'temperature' => 'hot',
            'last_activity_at' => now()->subHours(2), // under the 48h idle threshold
        ]));

        $this->assertCount(0, $c);
    }

    public function test_demo_in_20_hours_gets_an_email_reminder(): void
    {
        // 20h sits safely in the (12, 24] band — avoids the hour-truncation boundary.
        $c = $this->candidates($this->lead([
            'status' => 'demo_scheduled',
            'demo_scheduled_at' => now()->addHours(20),
            'last_activity_at' => now(),
        ]));

        $this->assertCount(1, $c);
        $this->assertSame('email', $c[0]->actionType);
        $this->assertSame('reminder', $c[0]->category);
    }

    public function test_converted_lead_without_recent_checkin_gets_retention(): void
    {
        $c = $this->candidates($this->lead([
            'status' => 'converted', 'temperature' => 'warm',
            'updated_at' => now()->subDays(40),
        ]));

        $this->assertCount(1, $c);
        $this->assertSame('retention', $c[0]->category);
    }

    public function test_trial_expired_activity_triggers_an_urgent_call(): void
    {
        $activity = new LeadActivity();
        $activity->type = 'trial_expired';
        $activity->created_at = now()->subDay();

        $c = $this->candidates($this->lead([
            'status' => 'active', 'temperature' => 'cold',
            'last_activity_at' => now(), // keeps the ACTIVE rule from firing
        ], collect([$activity])));

        $this->assertCount(1, $c);
        $this->assertSame('trial_expired', $c[0]->category);
        $this->assertSame('high', $c[0]->priority);
    }
}
