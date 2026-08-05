<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Http\Middleware\EnforceInfraStanding;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * B2 stage 2 — the readonly/degraded gate. When an owner's infra bill is OVERDUE,
 * money-making/expansion writes (invoicing, adding properties/tenants, new
 * financing, listing products) are blocked; reads, operational writes, and the
 * pay flow stay open. Non-owners and current-standing owners are never gated.
 */
class InfraStandingGateTest extends CentresidenceDatabaseTestCase
{
    private function actAsOwner(int $id = 1, int $role = USER_ROLE_OWNER): void
    {
        Auth::setUser(new User(['id' => $id, 'role' => $role]));
    }

    private function overdueInfra(int $ownerId): void
    {
        CentresidenceCommissionInvoice::create([
            'owner_id' => $ownerId, 'property_id' => 1,
            'billing_month' => Carbon::now()->subMonths(2)->startOfMonth(), // well past grace
            'subscription_amount' => 1500,
            'metered_commission_total' => 300, 'non_metered_commission_total' => 200,
            'total_amount' => 2000, 'status' => CentresidenceCommissionInvoice::STATUS_PENDING,
        ]);
    }

    private function runGate(string $routeName, string $method = 'POST')
    {
        $req = Request::create('/owner/x', $method);
        $req->setRouteResolver(fn () => (new Route([$method], 'owner/x', []))->name($routeName));
        $req->setLaravelSession($this->app['session']->driver());

        return app(EnforceInfraStanding::class)->handle($req, fn ($r) => new Response('PASSED', 200));
    }

    public function test_gated_write_is_blocked_when_infra_overdue(): void
    {
        $this->actAsOwner();
        $this->overdueInfra(1);

        $resp = $this->runGate('owner.invoice.store');

        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertNotSame('PASSED', $resp->getContent());
    }

    public function test_gated_write_passes_when_infra_current(): void
    {
        $this->actAsOwner(); // no overdue invoice seeded → current

        $this->assertSame('PASSED', $this->runGate('owner.invoice.store')->getContent());
    }

    public function test_non_gated_write_passes_even_when_overdue(): void
    {
        $this->actAsOwner();
        $this->overdueInfra(1);

        // A tenant-facing / operational write is not gated.
        $this->assertSame('PASSED', $this->runGate('owner.ticket.reply')->getContent());
    }

    public function test_get_request_passes_even_on_a_gated_name(): void
    {
        $this->actAsOwner();
        $this->overdueInfra(1);

        $this->assertSame('PASSED', $this->runGate('owner.invoice.store', 'GET')->getContent());
    }

    public function test_non_owner_is_never_gated(): void
    {
        $this->actAsOwner(1, USER_ROLE_ADMIN);
        $this->overdueInfra(1);

        $this->assertSame('PASSED', $this->runGate('owner.invoice.store')->getContent());
    }
}
