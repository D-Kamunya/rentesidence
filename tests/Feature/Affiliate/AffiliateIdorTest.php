<?php

namespace Tests\Feature\Affiliate;

use App\Http\Controllers\Affiliates\ActionExecutionController;
use App\Http\Controllers\Affiliates\LeadController;
use App\Http\Controllers\Affiliates\LeadSuggestionController;
use App\Models\Lead;
use App\Models\LeadSuggestion;
use App\Models\User;
use App\Services\LeadService;
use App\Services\SuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * IDOR guards (bank-onboarding-critical): an affiliate must never read or mutate
 * another affiliate's lead/suggestion via a guessable id. These invoke the REAL
 * controllers with a foreign affiliate acting and assert 403 — exercising the
 * ownership checks directly. (Full HTTP/routing tests are blocked by legacy
 * MySQL-only migrations that don't run on the sqlite harness; the routes sitting
 * behind auth middleware is verified separately by route-group inspection.)
 *
 * NOTE: leads/suggestions.affiliate_id stores the owning USER id (see claim() and
 * every guard), so we act as the user whose id is the lead's affiliate_id.
 */
class AffiliateIdorTest extends AffiliateDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->timestamps();
            $t->softDeletes(); // User model uses SoftDeletes
        });

        Schema::create('companies', function ($t) {
            $t->id();
            $t->string('company_name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->timestamps();
        });

        Schema::create('leads', function ($t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable();
            $t->unsignedBigInteger('affiliate_id')->nullable(); // owning USER id
            $t->string('product')->nullable();
            $t->string('status')->nullable();
            $t->string('temperature')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('last_activity_at')->nullable();
            $t->timestamp('ownership_expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('lead_activities', function ($t) {
            $t->id();
            $t->unsignedBigInteger('lead_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('type')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('lead_suggestions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('lead_id');
            $t->unsignedBigInteger('affiliate_id')->nullable(); // owning USER id
            $t->string('message')->nullable();
            $t->string('action_type')->nullable();
            $t->string('category')->nullable();
            $t->string('priority')->nullable();
            $t->string('status')->nullable();
            $t->timestamp('executed_at')->nullable();
            $t->string('execution_type')->nullable();
            $t->unsignedBigInteger('executed_by')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });
    }

    // ── Fixtures ───────────────────────────────────────────────────────────

    private function user(int $id): User
    {
        return User::create(['id' => $id, 'first_name' => "U{$id}", 'email' => "u{$id}@test.dev"]);
    }

    private function leadOwnedBy(int $ownerUserId): Lead
    {
        $company = \App\Models\Company::create(['company_name' => 'Acme', 'email' => 'a@a.dev', 'phone' => '0700000000']);

        return Lead::create([
            'company_id'           => $company->id,
            'affiliate_id'         => $ownerUserId,
            'product'              => 'property_sales',
            'status'               => 'active',
            'temperature'          => 'warm',
            'ownership_expires_at' => now()->addDays(60),
        ]);
    }

    private function suggestionFor(Lead $lead): LeadSuggestion
    {
        return LeadSuggestion::create([
            'lead_id'      => $lead->id,
            'affiliate_id' => $lead->affiliate_id,
            'message'      => 'Call now',
            'action_type'  => 'call',
            'category'     => 'intro',
            'priority'     => 'high',
            'status'       => 'pending',
            'expires_at'   => now()->addDays(3),
        ]);
    }

    private function assertForbidden(callable $fn): void
    {
        try {
            $fn();
            $this->fail('Expected a 403 HttpException, but none was thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    // ── LeadController ──────────────────────────────────────────────────────

    public function test_foreign_affiliate_cannot_view_a_lead(): void
    {
        $this->user(1);
        $this->user(2);
        $lead = $this->leadOwnedBy(1);

        $this->actingAs(User::find(2)); // act as the non-owner

        $this->assertForbidden(fn () => app(LeadController::class)->show($lead->id, app(SuggestionService::class)));
    }

    public function test_foreign_affiliate_cannot_add_a_note(): void
    {
        $this->user(1);
        $this->user(2);
        $lead = $this->leadOwnedBy(1);

        $this->actingAs(User::find(2));

        $req = Request::create('/', 'POST', ['note' => 'hijack']);
        $this->assertForbidden(fn () => app(LeadController::class)->addNote($req, $lead, app(LeadService::class)));
    }

    public function test_owner_can_add_a_note(): void
    {
        $this->user(1);
        $lead = $this->leadOwnedBy(1);

        $this->actingAs(User::find(1));

        $req = Request::create('/', 'POST', ['note' => 'legit']);
        app(LeadController::class)->addNote($req, $lead, app(LeadService::class));

        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'note_added']);
    }

    // ── LeadSuggestionController ─────────────────────────────────────────────

    public function test_foreign_affiliate_cannot_complete_a_suggestion(): void
    {
        $this->user(1);
        $this->user(2);
        $suggestion = $this->suggestionFor($this->leadOwnedBy(1));

        $this->actingAs(User::find(2));

        $this->assertForbidden(fn () => app(LeadSuggestionController::class)->complete($suggestion->id));
    }

    public function test_foreign_affiliate_cannot_dismiss_a_suggestion(): void
    {
        $this->user(1);
        $this->user(2);
        $suggestion = $this->suggestionFor($this->leadOwnedBy(1));

        $this->actingAs(User::find(2));

        $this->assertForbidden(fn () => app(LeadSuggestionController::class)->dismiss($suggestion->id));
    }

    // ── ActionExecutionController ────────────────────────────────────────────

    public function test_foreign_affiliate_cannot_send_whatsapp_on_a_lead(): void
    {
        $this->user(1);
        $this->user(2);
        $lead = $this->leadOwnedBy(1);

        $this->actingAs(User::find(2));

        // Guard runs before the template lookup, so any template id is fine.
        $this->assertForbidden(fn () => app(ActionExecutionController::class)->whatsapp($lead->id, 1));
    }
}
