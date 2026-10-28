<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aligns the three paid bands that were below the 2×-units SMS-grant rule (Enterprise, Corporate,
 * Institutional) so every subscription band's grant is a consistent ~2 SMS/unit — the "your plan
 * silently covers your SMS" promise then holds at every size. Done as a targeted update (not a
 * catalogue re-baseline) so it never clobbers an admin's custom price edits; only a band still at
 * its old grant is changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->bump('Enterprise', 600, 800);
        $this->bump('Corporate', 800, 1200);
        $this->bump('Institutional', 1000, 2000);
    }

    public function down(): void
    {
        $this->bump('Enterprise', 800, 600);
        $this->bump('Corporate', 1200, 800);
        $this->bump('Institutional', 2000, 1000);
    }

    private function bump(string $name, int $from, int $to): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('packages', 'monthly_sms_credits')) {
            return;
        }
        DB::table('packages')
            ->where('name', $name)
            ->where('monthly_sms_credits', $from)   // only if still at the old value (respect admin edits)
            ->update(['monthly_sms_credits' => $to]);
    }
};
