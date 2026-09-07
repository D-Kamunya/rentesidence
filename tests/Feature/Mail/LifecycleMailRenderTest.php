<?php

namespace Tests\Feature\Mail;

use App\Mail\LifecycleMail;
use Tests\TestCase;

/**
 * Regression guard for the CS lifecycle mail redesign: every lifecycle email funnels through
 * LifecycleMail -> mail.lifecycle -> mail.layouts.cs. This proves the reusable renderer emits
 * the CS brand shell and the CS parts (not hand-rolled HTML), so a future off-layout mail is
 * caught. See comms-redesign-audit.
 */
class LifecycleMailRenderTest extends TestCase
{
    private function render(array $data): string
    {
        // Rendering the Blade body does not require a DB — getOption() reads config('settings').
        return view('mail.lifecycle', ['m' => array_merge(['subject' => 'Test'], $data)])->render();
    }

    /** @test */
    public function it_renders_through_the_cs_brand_shell(): void
    {
        $html = $this->render([
            'eyebrow' => 'Welcome',
            'title'   => 'Your trial is ready',
            'blocks'  => [
                ['type' => 'text', 'html' => 'Hello <strong>Acme</strong>,'],
            ],
        ]);

        // CS shell markers (from mail.layouts.cs header/footer).
        $this->assertStringContainsString('Real Estate', $html);
        $this->assertStringContainsString('#0F2A4A', $html, 'CS navy header must be present');
        // Heading part rendered eyebrow + title.
        $this->assertStringContainsString('Welcome', $html);
        $this->assertStringContainsString('Your trial is ready', $html);
    }

    /** @test */
    public function it_renders_each_block_type_from_the_cs_parts(): void
    {
        $html = $this->render([
            'eyebrow' => 'Trial approved',
            'title'   => 'Trial account created',
            'blocks'  => [
                ['type' => 'text', 'html' => 'A short intro line.'],
                ['type' => 'panel', 'variant' => 'blue', 'title' => 'Your login', 'rows' => [
                    ['k' => 'Login email',        'v' => 'client@example.com'],
                    ['k' => 'Temporary password', 'v' => 'Ab12Cd34', 'mono' => true],
                ]],
                ['type' => 'button', 'url' => 'https://example.com/login', 'label' => 'Sign in'],
                ['type' => 'note', 'text' => 'Keep this private.'],
            ],
        ]);

        $this->assertStringContainsString('A short intro line.', $html);          // text part
        $this->assertStringContainsString('Your login', $html);                   // panel title
        $this->assertStringContainsString('Ab12Cd34', $html);                     // panel value
        $this->assertStringContainsString('Consolas,Menlo,monospace', $html);     // mono row styling
        $this->assertStringContainsString('https://example.com/login', $html);    // button url
        $this->assertStringContainsString('Sign in', $html);                      // button label
        $this->assertStringContainsString('#FBF3E4', $html);                      // amber note background
    }

    /** @test */
    public function panel_values_are_html_escaped_against_stored_xss(): void
    {
        // A malicious company name lands in a panel row; the panel part escapes with {{ }}.
        $html = $this->render([
            'title'  => 'Lead details',
            'blocks' => [
                ['type' => 'panel', 'variant' => 'blue', 'title' => 'Lead', 'rows' => [
                    ['k' => 'Company', 'v' => '<script>alert(1)</script>'],
                ]],
            ],
        ]);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    /** @test */
    public function the_mailable_sets_its_subject(): void
    {
        $mail = new LifecycleMail('My subject line', ['title' => 'Hi', 'blocks' => []]);
        $this->assertSame('My subject line', $mail->subject);
        $this->assertSame('My subject line', $mail->m['subject']);
    }
}
