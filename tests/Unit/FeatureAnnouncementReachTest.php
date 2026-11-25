<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\FeatureAnnouncementController as Ctrl;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Reach computation for feature announcements — seen ÷ targeted-audience, so an admin knows when a
 * "what's new" note has reached enough people to pause it.
 */
class FeatureAnnouncementReachTest extends TestCase
{
    /** role id => number of users with that role. */
    private function counts(): Collection
    {
        return collect([
            USER_ROLE_OWNER           => 40,
            USER_ROLE_TENANT          => 100,
            USER_ROLE_AFFILIATE       => 20,
            USER_ROLE_FINANCE_PARTNER => 5,
            USER_ROLE_MAINTAINER      => 10,
        ]);
    }

    public function test_all_audience_targets_every_visible_role(): void
    {
        // 40+100+20+5+10 = 175 targeted; 35 seen.
        $r = Ctrl::computeReach('all', 35, $this->counts());
        $this->assertSame(175, $r['target']);
        $this->assertSame(20, $r['pct']); // 35/175 = 20%
    }

    public function test_specific_audience_sums_only_listed_roles(): void
    {
        // Owners (40) only, 10 seen → 25%.
        $r = Ctrl::computeReach((string) USER_ROLE_OWNER, 10, $this->counts());
        $this->assertSame(40, $r['target']);
        $this->assertSame(25, $r['pct']);
    }

    public function test_multi_role_audience_sums_the_roles(): void
    {
        // Owners (40) + affiliates (20) = 60; 30 seen → 50%.
        $r = Ctrl::computeReach(USER_ROLE_OWNER . ',' . USER_ROLE_AFFILIATE, 30, $this->counts());
        $this->assertSame(60, $r['target']);
        $this->assertSame(50, $r['pct']);
    }

    public function test_empty_audience_yields_null_pct(): void
    {
        $r = Ctrl::computeReach((string) USER_ROLE_OWNER, 0, collect());
        $this->assertSame(0, $r['target']);
        $this->assertNull($r['pct']);
    }

    public function test_seen_is_capped_so_reach_never_exceeds_100(): void
    {
        // More seen rows than current audience (role churn) must read 100%, not 125%.
        $r = Ctrl::computeReach((string) USER_ROLE_OWNER, 50, $this->counts());
        $this->assertSame(100, $r['pct']);
    }
}
