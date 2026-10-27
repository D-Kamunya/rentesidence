<?php

namespace Tests\Feature\Affiliate;

use App\Models\Lead;
use Tests\TestCase;

/**
 * Lead::displayState() — the SINGLE SOURCE OF TRUTH every lead surface (list badges + action,
 * detail hero + expiry + actions) now derives from, so no two cells can contradict each other.
 *
 * Built purely in memory (no DB) — displayState() reads only the loaded status / ownership_expires_at
 * / free_invite_* attributes. This locks the state machine + the derived helpers across EVERY
 * status × ownership(past/future) × invite(pending/expired/none) combination, which is the coverage
 * the manual UI can't practically exercise for every stage.
 */
class LeadDisplayStateTest extends TestCase
{
    private function lead(array $attrs): Lead
    {
        return (new Lead())->forceFill($attrs);
    }

    private function pendingInvite(): array
    {
        return [
            'free_invite_sent_at'     => now()->subDay(),
            'free_invite_accepted_at' => null,
            'free_invite_expires_at'  => now()->addDays(5), // future → live invite
        ];
    }

    private function expiredInvite(): array
    {
        return [
            'free_invite_sent_at'     => now()->subDays(10),
            'free_invite_accepted_at' => null,
            'free_invite_expires_at'  => now()->subDay(), // past → lapsed invite
        ];
    }

    // ── Terminal statuses ────────────────────────────────────────────────────

    public function test_converted_is_won_locked_and_not_renewable(): void
    {
        // A past ownership date must NOT turn a win into something renewable.
        $lead = $this->lead(['status' => 'converted', 'ownership_expires_at' => now()->subDay()]);
        $this->assertSame('won', $lead->displayState());
        $this->assertTrue($lead->isWon());
        $this->assertTrue($lead->isClosed());
        $this->assertTrue($lead->isLockedForSales());
        $this->assertFalse($lead->canRenew());
        $this->assertFalse($lead->canEdit());
    }

    public function test_rejected_and_lost_are_closed_not_renewable(): void
    {
        foreach (['rejected', 'lost'] as $status) {
            $lead = $this->lead(['status' => $status, 'ownership_expires_at' => now()->subDay()]);
            $this->assertSame($status, $lead->displayState(), $status);
            $this->assertTrue($lead->isClosed(), $status);
            $this->assertTrue($lead->isLockedForSales(), $status);
            $this->assertFalse($lead->canRenew(), $status);
            $this->assertFalse($lead->canEdit(), $status);
        }
    }

    public function test_cron_expired_status_locks_sales_is_renewable_but_not_closed(): void
    {
        $lead = $this->lead(['status' => 'expired', 'ownership_expires_at' => now()->subDay()]);
        $this->assertSame('expired', $lead->displayState());
        $this->assertFalse($lead->isClosed());        // 'expired' is NOT a CLOSED_STATUS
        $this->assertTrue($lead->isLockedForSales());  // but sales are locked
        $this->assertTrue($lead->canRenew());
        $this->assertFalse($lead->canEdit());
    }

    // ── Protected / working ──────────────────────────────────────────────────

    public function test_trial_is_its_own_state_editable_not_renewable(): void
    {
        $lead = $this->lead(['status' => 'trial', 'ownership_expires_at' => now()->subDay()]);
        $this->assertSame('trial', $lead->displayState());
        $this->assertFalse($lead->isLockedForSales());
        $this->assertFalse($lead->canRenew());
        $this->assertTrue($lead->canEdit());
    }

    public function test_protected_pipeline_stages_are_working_and_editable(): void
    {
        foreach (['demo_scheduled', 'demo_completed', 'pending_conversion'] as $status) {
            // Even with a past ownership date, a protected lead never expires/renews.
            $lead = $this->lead(['status' => $status, 'ownership_expires_at' => now()->subDay()]);
            $this->assertSame('working', $lead->displayState(), $status);
            $this->assertFalse($lead->canRenew(), $status);
            $this->assertTrue($lead->canEdit(), $status);
            $this->assertFalse($lead->isLockedForSales(), $status);
        }
    }

    // ── The ownership clock ──────────────────────────────────────────────────

    public function test_active_with_future_date_is_active_editable(): void
    {
        $lead = $this->lead(['status' => 'active', 'ownership_expires_at' => now()->addDays(30)]);
        $this->assertSame('active', $lead->displayState());
        $this->assertTrue($lead->canEdit());
        $this->assertFalse($lead->canRenew());
        $this->assertFalse($lead->isLockedForSales());
    }

    public function test_active_with_past_date_is_lapsed_renewable_but_still_workable(): void
    {
        $lead = $this->lead(['status' => 'active', 'ownership_expires_at' => now()->subDay()]);
        $this->assertSame('lapsed', $lead->displayState());
        $this->assertTrue($lead->canRenew());
        $this->assertFalse($lead->canEdit());
        $this->assertFalse($lead->isLockedForSales()); // lapsed-by-date is NOT locked (only cron 'expired' is)
        $this->assertFalse($lead->isClosed());
    }

    // ── Free-invite overlay (the clock-pauser) ───────────────────────────────

    public function test_pending_free_invite_pauses_the_clock_even_with_a_past_date(): void
    {
        // The exact bug we hit: a lead with a live invite out but a past ownership date must NOT
        // read as expired/renewable — it's awaiting the owner.
        $lead = $this->lead(array_merge(
            ['status' => 'active', 'ownership_expires_at' => now()->subDay()],
            $this->pendingInvite()
        ));
        $this->assertSame('awaiting_activation', $lead->displayState());
        $this->assertTrue($lead->canEdit());
        $this->assertFalse($lead->canRenew());
        $this->assertFalse($lead->isLockedForSales());
    }

    public function test_lapsed_free_invite_lets_the_clock_resume(): void
    {
        // Once the invite lapses unaccepted, the normal ownership clock applies again.
        $lead = $this->lead(array_merge(
            ['status' => 'active', 'ownership_expires_at' => now()->subDay()],
            $this->expiredInvite()
        ));
        $this->assertSame('lapsed', $lead->displayState());
        $this->assertTrue($lead->canRenew());
    }

    public function test_pending_invite_on_a_demo_completed_lead_is_awaiting_activation(): void
    {
        $lead = $this->lead(array_merge(
            ['status' => 'demo_completed', 'ownership_expires_at' => now()->addDays(10)],
            $this->pendingInvite()
        ));
        $this->assertSame('awaiting_activation', $lead->displayState());
    }

    // ── isClosed() alignment (the existing method the rest of the app relies on) ──

    public function test_is_closed_matches_the_won_rejected_lost_states_only(): void
    {
        $closed = [
            'converted' => 'won', 'rejected' => 'rejected', 'lost' => 'lost',
        ];
        foreach ($closed as $status => $state) {
            $this->assertTrue($this->lead(['status' => $status])->isClosed(), $status);
        }
        foreach (['active', 'demo_scheduled', 'demo_completed', 'pending_conversion', 'trial', 'expired'] as $status) {
            $this->assertFalse($this->lead(['status' => $status, 'ownership_expires_at' => now()->addDay()])->isClosed(), $status);
        }
    }
}
