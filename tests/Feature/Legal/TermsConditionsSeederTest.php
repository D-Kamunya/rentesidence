<?php

namespace Tests\Feature\Legal;

use App\Models\Setting;
use Database\Seeders\TermsConditionsSeeder;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The default owner/agency T&C seeds a real, comprehensive backstop (editable live) and NEVER
 * clobbers a value counsel has since edited. See roadmap item 0a / agency-system-design.
 */
class TermsConditionsSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function ($t) {
                $t->id();
                $t->string('option_key')->nullable();
                $t->longText('option_value')->nullable();
                $t->timestamps();
            });
        }
        Setting::query()->delete();
        config(['settings' => []]);
    }

    /** @test */
    public function it_seeds_a_comprehensive_default_when_none_exists(): void
    {
        (new TermsConditionsSeeder())->run();

        $value = Setting::where('option_key', 'terms_conditions')->value('option_value');
        $this->assertNotEmpty($value);
        // Covers the model we decided at the sitting.
        foreach (['Fees', 'escrow', 'Confirm receipt', 'Agenc', 'Settlement mode', 'payout destination', 'Data Protection Act', 'Governing law'] as $needle) {
            $this->assertStringContainsString($needle, $value, "T&C should mention: {$needle}");
        }
    }

    /** @test */
    public function the_html_is_newline_free_so_the_frontend_nl2br_renders_clean(): void
    {
        (new TermsConditionsSeeder())->run();
        $value = Setting::where('option_key', 'terms_conditions')->value('option_value');

        // The public policy page renders {!! nl2br($description) !!}; literal newlines would inject
        // stray <br> between block tags. The seeded HTML must therefore contain none.
        $this->assertStringNotContainsString("\n", $value);
    }

    /** @test */
    public function first_run_is_authoritative_over_a_stale_value_and_sets_the_sentinel(): void
    {
        // A truncated/stale stub already exists (e.g. the old varchar(191) truncation).
        Setting::create(['option_key' => 'terms_conditions', 'option_value' => '<p>stale stub</p>']);
        config(['settings.terms_conditions' => '<p>stale stub</p>']);

        (new TermsConditionsSeeder())->run();

        $value = Setting::where('option_key', 'terms_conditions')->value('option_value');
        $this->assertStringContainsString('Agenc', $value);          // full draft won
        $this->assertStringNotContainsString('stale stub', $value);  // stub replaced
        $this->assertSame('1', getOption('terms_conditions_seed_v1'));
        $this->assertSame('1.0', getOption('terms_version'));
    }

    /** @test */
    public function once_locked_a_later_run_does_not_overwrite_counsel_edits(): void
    {
        (new TermsConditionsSeeder())->run();                       // first run establishes + locks
        setOption('terms_conditions', '<p>Counsel-reviewed final text.</p>');

        (new TermsConditionsSeeder())->run();                       // subsequent run

        $this->assertStringContainsString('Counsel-reviewed final text.', getOption('terms_conditions'));
    }
}
