<?php

namespace Tests\Feature\Legal;

use App\Http\Middleware\EnsureTermsAccepted;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The owner Terms-acceptance gate: an owner whose recorded acceptance doesn't match the current
 * terms_version is redirected to accept; a matching owner and any non-owner pass through. Bumping
 * the version re-gates everyone. See roadmap item 0a / agency-system-design.
 */
class TermsAcceptanceGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // The gate self-activates only once the acceptance columns exist (graceful deploy-lag guard).
        if (! Schema::hasTable('users')) {
            Schema::create('users', function ($t) {
                $t->id();
                $t->string('terms_accepted_version')->nullable();
            });
        } elseif (! Schema::hasColumn('users', 'terms_accepted_version')) {
            Schema::table('users', fn ($t) => $t->string('terms_accepted_version')->nullable());
        }
    }

    private function pass(User $user): bool
    {
        config(['settings.terms_version' => '1.0']);
        $request = Request::create('/owner');
        $request->setUserResolver(fn () => $user);

        $response = (new EnsureTermsAccepted())->handle($request, fn () => response('OK'));

        return $response->getContent() === 'OK'; // true = passed through, false = redirected
    }

    private function owner(?string $acceptedVersion): User
    {
        return (new User)->forceFill(['role' => USER_ROLE_OWNER, 'terms_accepted_version' => $acceptedVersion]);
    }

    /** @test */
    public function an_owner_who_has_not_accepted_is_gated(): void
    {
        $this->assertFalse($this->pass($this->owner(null)));
    }

    /** @test */
    public function an_owner_on_an_old_version_is_re_gated(): void
    {
        $this->assertFalse($this->pass($this->owner('0.9')));
    }

    /** @test */
    public function an_owner_on_the_current_version_passes(): void
    {
        $this->assertTrue($this->pass($this->owner('1.0')));
    }

    /** @test */
    public function a_non_owner_is_never_gated_by_this_middleware(): void
    {
        $tenant = (new User)->forceFill(['role' => USER_ROLE_TENANT, 'terms_accepted_version' => null]);
        $this->assertTrue($this->pass($tenant));
    }

    /** @test */
    public function the_redirect_target_is_the_accept_screen(): void
    {
        config(['settings.terms_version' => '1.0']);
        $request = Request::create('/owner');
        $request->setUserResolver(fn () => $this->owner(null));

        $response = (new EnsureTermsAccepted())->handle($request, fn () => response('OK'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('accept-terms', $response->getTargetUrl());
    }
}
