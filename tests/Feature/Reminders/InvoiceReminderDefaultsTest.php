<?php

namespace Tests\Feature\Reminders;

use App\Console\Commands\ReminderInvoice;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The tenant payment-reminder cadence self-heals a bounded PLATFORM default (gentle pre-due nudge
 * + three overdue nudges, never "everyday"), so a fresh install reminds out-of-box — but it must
 * never clobber a value an admin has deliberately set. See comms-redesign-audit + plug-and-play-defaults.
 */
class InvoiceReminderDefaultsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function ($t) {
                $t->id();
                $t->string('option_key')->nullable();
                $t->text('option_value')->nullable();
                $t->timestamps();
            });
        }
        config(['settings' => []]); // start with nothing configured
    }

    private function runEnsureDefaults(): void
    {
        $m = new \ReflectionMethod(ReminderInvoice::class, 'ensureDefaults');
        $m->setAccessible(true);
        $m->invoke(new ReminderInvoice());
    }

    /** @test */
    public function it_seeds_a_bounded_default_cadence_when_nothing_is_configured(): void
    {
        $this->runEnsureDefaults();

        $this->assertSame((string) REMAINDER_STATUS_ACTIVE, (string) getOption('OVERDUE_REMAINDER_STATUS'));
        $this->assertSame('1,3,7', getOption('OVERDUE_REMAINDER_DAYS'));           // three overdue nudges
        $this->assertSame('3', getOption('reminder_days'));                        // one pre-due nudge
        // "Everyday" must default OFF — that is the unbounded-spam vector.
        $this->assertSame((string) REMAINDER_EVERYDAY_STATUS_DEACTIVATE, (string) getOption('OVERDUE_REMAINDER_EVERYDAY_STATUS'));
        $this->assertSame((string) REMAINDER_EVERYDAY_STATUS_DEACTIVATE, (string) getOption('remainder_everyday_status'));
    }

    /** @test */
    public function it_never_overwrites_an_admin_configured_value(): void
    {
        // Admin deliberately chose a custom overdue schedule AND turned pre-due reminders off.
        config(['settings.OVERDUE_REMAINDER_DAYS' => '2,5', 'settings.remainder_status' => (string) REMAINDER_STATUS_DEACTIVATE]);

        $this->runEnsureDefaults();

        $this->assertSame('2,5', getOption('OVERDUE_REMAINDER_DAYS'));                       // preserved
        $this->assertSame((string) REMAINDER_STATUS_DEACTIVATE, (string) getOption('remainder_status')); // preserved (deliberate off)
        // A key the admin never touched still gets its sensible default.
        $this->assertSame((string) REMAINDER_STATUS_ACTIVE, (string) getOption('OVERDUE_REMAINDER_STATUS'));
    }
}
