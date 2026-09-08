<?php

namespace Tests\Feature\Sms;

use App\Services\Sms\SmsCreditsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The paused-SMS re-engagement digest's SELECTION logic: only messages blocked specifically by
 * insufficient credits, within the look-back window, count toward an owner's backlog. See
 * comms-redesign-audit / roadmap item 0b.
 */
class PausedBacklogDigestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! Schema::hasTable('sms_histories')) {
            Schema::create('sms_histories', function ($t) {
                $t->id();
                $t->unsignedBigInteger('owner_user_id');
                $t->tinyInteger('status')->default(0);
                $t->string('message')->nullable();
                $t->string('error')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }
        DB::table('sms_histories')->truncate();
    }

    private function row(int $owner, int $status, ?string $error, string $ago = '1 day'): void
    {
        DB::table('sms_histories')->insert([
            'owner_user_id' => $owner, 'status' => $status, 'error' => $error,
            'created_at' => now()->sub($ago), 'updated_at' => now()->sub($ago),
        ]);
    }

    /** @test */
    public function only_credit_blocked_messages_inside_the_window_count(): void
    {
        $this->row(10, SMS_STATUS_FAILED, 'Insufficient SMS credits', '2 days');  // ✓ counts
        $this->row(10, SMS_STATUS_FAILED, 'Insufficient SMS credits', '3 days');  // ✓ counts
        $this->row(10, SMS_STATUS_FAILED, 'Insufficient SMS credits', '3 days');  // ✓ counts (3 total)
        $this->row(11, SMS_STATUS_FAILED, 'Insufficient SMS credits', '40 days'); // ✗ outside window
        $this->row(12, SMS_STATUS_DELIVERED, null, '1 day');                      // ✗ not failed
        $this->row(13, SMS_STATUS_FAILED, 'Network error', '1 day');              // ✗ other failure, not credits

        $backlog = SmsCreditsService::ownersWithPausedBacklog(7);

        $this->assertEquals([10 => 3], $backlog->toArray());
        $this->assertCount(3, SmsCreditsService::getRetryableFailed(10, 7));
        $this->assertCount(0, SmsCreditsService::getRetryableFailed(11, 7)); // old row excluded
    }

    /** @test */
    public function the_command_runs_cleanly_with_no_backlog(): void
    {
        $this->artisan('sms:paused-digest')
            ->expectsOutputToContain('notified 0 owner(s)')
            ->assertExitCode(0);
    }
}
