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
    public function it_seeds_a_bounded_default_cadence_on_first_run(): void
    {
        $this->runEnsureDefaults();

        $this->assertSame((string) REMAINDER_STATUS_ACTIVE, (string) getOption('OVERDUE_REMAINDER_STATUS'));
        $this->assertSame('1,3,7', getOption('OVERDUE_REMAINDER_DAYS'));           // three overdue nudges
        $this->assertSame('3', getOption('reminder_days'));                        // one pre-due nudge
        // "Everyday" must default OFF — that is the unbounded-spam vector.
        $this->assertSame((string) REMAINDER_EVERYDAY_STATUS_DEACTIVATE, (string) getOption('OVERDUE_REMAINDER_EVERYDAY_STATUS'));
        $this->assertSame((string) REMAINDER_EVERYDAY_STATUS_DEACTIVATE, (string) getOption('remainder_everyday_status'));
        $this->assertSame('1', getOption('reminder_defaults_v1'));                 // sentinel set
    }

    /** @test */
    public function first_run_is_authoritative_over_existing_stale_values(): void
    {
        // A live install already holds stale/unconsidered values and no sentinel yet.
        config([
            'settings.OVERDUE_REMAINDER_DAYS'            => '5',
            'settings.OVERDUE_REMAINDER_EVERYDAY_STATUS' => (string) REMAINDER_EVERYDAY_STATUS_ACTIVE, // the spam vector
        ]);

        $this->runEnsureDefaults();

        // Our considered baseline WINS on first run (that's the point of "authoritative").
        $this->assertSame('1,3,7', getOption('OVERDUE_REMAINDER_DAYS'));
        $this->assertSame((string) REMAINDER_EVERYDAY_STATUS_DEACTIVATE, (string) getOption('OVERDUE_REMAINDER_EVERYDAY_STATUS'));
    }

    /** @test */
    public function once_the_baseline_is_locked_a_later_admin_change_is_respected(): void
    {
        $this->runEnsureDefaults();                                   // first run establishes + locks
        setOption('OVERDUE_REMAINDER_DAYS', '2,5,10');                // admin later customises

        $this->runEnsureDefaults();                                   // subsequent tick

        $this->assertSame('2,5,10', getOption('OVERDUE_REMAINDER_DAYS')); // NOT re-clobbered
    }
}
