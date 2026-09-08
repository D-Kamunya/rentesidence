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
    public function it_never_overwrites_an_existing_reviewed_value(): void
    {
        Setting::create(['option_key' => 'terms_conditions', 'option_value' => '<p>Counsel-reviewed final text.</p>']);

        (new TermsConditionsSeeder())->run();

        $this->assertSame(
            '<p>Counsel-reviewed final text.</p>',
            Setting::where('option_key', 'terms_conditions')->value('option_value')
        );
    }
}
