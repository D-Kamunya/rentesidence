<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\ForcePasswordChange;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * The first-login gate: an account issued a system password (must_change_password = 1) is held
 * on the change-password screen until they set their own; everyone else passes through.
 */
class ForcePasswordChangeTest extends TestCase
{
    private function passThrough(Request $request)
    {
        return (new ForcePasswordChange())->handle($request, fn ($r) => response('reached'));
    }

    private function requestAs(?User $user, string $uri = '/owner/dashboard'): Request
    {
        $request = Request::create($uri, 'GET');
        $request->setUserResolver(fn () => $user);
        return $request;
    }

    public function test_flagged_user_is_redirected_to_change_password(): void
    {
        $user = new User();
        $user->must_change_password = 1;

        $response = $this->passThrough($this->requestAs($user));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('change-password', $response->headers->get('Location'));
    }

    public function test_unflagged_user_passes_through(): void
    {
        $user = new User();
        $user->must_change_password = 0;

        $response = $this->passThrough($this->requestAs($user));

        $this->assertSame('reached', (string) $response->getContent());
    }

    public function test_guest_passes_through(): void
    {
        $response = $this->passThrough($this->requestAs(null));

        $this->assertSame('reached', (string) $response->getContent());
    }

    public function test_flagged_user_can_still_log_out(): void
    {
        $user = new User();
        $user->must_change_password = 1;

        $response = $this->passThrough($this->requestAs($user, '/logout'));

        $this->assertSame('reached', (string) $response->getContent());
    }
}
