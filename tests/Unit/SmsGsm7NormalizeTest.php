<?php

namespace Tests\Unit;

use App\Services\SmsMail\AdvantaSmsService;
use PHPUnit\Framework\TestCase;

/**
 * SMS cost guard: the single send chokepoint downgrades smart punctuation to GSM-7 so a stray
 * em-dash/curly-quote can never force UCS-2 encoding (70-char segments) and silently double the
 * credit cost. See comms-redesign-audit (SMS sweep, GSM-7 standing check).
 */
class SmsGsm7NormalizeTest extends TestCase
{
    /** @test */
    public function it_downgrades_smart_punctuation_to_ascii(): void
    {
        $in  = "Sign in \u{2014} Email: a\u{2019}b \u{201C}quote\u{201D} wait\u{2026} done";
        $out = AdvantaSmsService::normalizeGsm7($in);

        $this->assertSame("Sign in - Email: a'b \"quote\" wait... done", $out);
    }

    /** @test */
    public function it_replaces_the_offending_unicode_code_points(): void
    {
        foreach (["\u{2014}", "\u{2013}", "\u{2018}", "\u{2019}", "\u{201C}", "\u{201D}", "\u{2026}", "\u{00A0}"] as $ch) {
            $this->assertStringNotContainsString($ch, AdvantaSmsService::normalizeGsm7("x{$ch}y"));
        }
    }

    /** @test */
    public function it_leaves_plain_gsm7_text_untouched(): void
    {
        $plain = "New paid order #ORD-1001 on Centresidence. Log in to organize dispatch.";
        $this->assertSame($plain, AdvantaSmsService::normalizeGsm7($plain));
    }
}
