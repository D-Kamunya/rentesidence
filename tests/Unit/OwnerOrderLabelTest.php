<?php

namespace Tests\Unit;

use App\Services\OwnerSubscriptionOrderService;
use Tests\TestCase;

/**
 * The admin All-Orders "name" column is rendered raw by DataTables, so the owner label must escape
 * owner-set name/email (stored-XSS guard) and degrade gracefully instead of printing a bare "()".
 */
class OwnerOrderLabelTest extends TestCase
{
    private function label(object $order): string
    {
        $svc = new OwnerSubscriptionOrderService();
        $m = new \ReflectionMethod($svc, 'ownerLabel');
        $m->setAccessible(true);

        return $m->invoke($svc, $order);
    }

    public function test_name_and_email_are_both_shown(): void
    {
        $out = $this->label((object) ['first_name' => 'Dennis', 'last_name' => 'Kamunya', 'email' => 'd@example.com', 'user_id' => 4]);
        $this->assertStringContainsString('Dennis Kamunya', $out);
        $this->assertStringContainsString('d@example.com', $out);
    }

    public function test_markup_in_a_name_is_escaped(): void
    {
        $out = $this->label((object) ['first_name' => '<script>alert(1)</script>', 'last_name' => '', 'email' => '', 'user_id' => 7]);
        $this->assertStringNotContainsString('<script>', $out);
        $this->assertStringContainsString('&lt;script&gt;', $out);
    }

    public function test_blank_user_shows_traceable_fallback_not_empty_parens(): void
    {
        $out = $this->label((object) ['first_name' => null, 'last_name' => null, 'email' => null, 'user_id' => 42]);
        $this->assertStringNotContainsString('()', $out);
        $this->assertStringContainsString('#42', $out);
    }

    public function test_email_only_falls_back_to_email(): void
    {
        $out = $this->label((object) ['first_name' => '', 'last_name' => '', 'email' => 'solo@example.com', 'user_id' => 9]);
        $this->assertStringContainsString('solo@example.com', $out);
    }
}
