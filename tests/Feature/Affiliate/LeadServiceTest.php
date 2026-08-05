<?php

namespace Tests\Feature\Affiliate;

use App\Jobs\Mail\SendDemoScheduledMail;
use App\Models\Company;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * LeadService — the lead-lifecycle logic extracted from LeadController. Runs on
 * an independent in-memory SQLite connection (no local MySQL needed).
 */
class LeadServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'lead_sqlite',
            'database.connections.lead_sqlite' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('lead_sqlite');
        DB::setDefaultConnection('lead_sqlite');
        $this->createTables();
    }

    private function createTables(): void
    {
        Schema::create('companies', function ($t) {
            $t->id();
            $t->string('company_name')->nullable();
            $t->string('normalized_name')->nullable();
            $t->string('country')->nullable();
            $t->string('city')->nullable();
            $t->string('phone')->nullable();
            $t->integer('estimated_units')->nullable();
            $t->string('email')->nullable();
            $t->string('website')->nullable();
            $t->string('property_type')->nullable();
            $t->string('sales_status')->nullable();
            $t->timestamps();
        });
        Schema::create('leads', function ($t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable();
            $t->unsignedBigInteger('affiliate_id')->nullable();
            $t->string('product')->nullable();
            $t->string('contact_person_name')->nullable();
            $t->string('contact_person_role')->nullable();
            $t->string('temperature')->nullable();
            $t->string('status')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('last_activity_at')->nullable();
            $t->timestamp('demo_scheduled_at')->nullable();
            $t->string('demo_meeting_link')->nullable();
            $t->timestamp('ownership_expires_at')->nullable();
            $t->timestamps();
        });
        Schema::create('lead_activities', function ($t) {
            $t->id();
            $t->unsignedBigInteger('lead_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('type');
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }

    private function svc(): LeadService
    {
        return app(LeadService::class);
    }

    private function makeLead(array $attrs = []): Lead
    {
        $company = Company::create(['company_name' => 'Acme', 'normalized_name' => 'acme']);

        return Lead::create(array_merge([
            'company_id' => $company->id, 'affiliate_id' => 1,
            'contact_person_name' => 'X', 'contact_person_role' => 'Y',
            'temperature' => 'cold', 'status' => 'active',
        ], $attrs));
    }

    public function test_set_temperature_updates_and_logs_activity(): void
    {
        $lead = $this->makeLead();
        $this->svc()->setTemperature($lead, 'hot');

        $this->assertSame('hot', $lead->fresh()->temperature);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'temperature_update']);
    }

    public function test_add_note_appends_and_logs(): void
    {
        $lead = $this->makeLead(['notes' => 'first']);
        $this->svc()->addNote($lead, 'second');

        $notes = $lead->fresh()->notes;
        $this->assertStringContainsString('first', $notes);
        $this->assertStringContainsString('second', $notes);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'note_added']);
    }

    public function test_mark_lost_sets_status_and_company_inactive(): void
    {
        $lead = $this->makeLead();
        $this->svc()->markLost($lead);

        $this->assertSame('lost', $lead->fresh()->status);
        $this->assertSame('inactive', $lead->company->fresh()->sales_status);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'lead_lost']);
    }

    public function test_create_lead_refuses_a_duplicate_active_company(): void
    {
        $data = [
            'company_name' => 'Beta Ltd', 'country' => 'KE', 'city' => 'Nairobi', 'phone' => '0700000000',
            'contact_person_name' => 'A', 'contact_person_role' => 'B',
        ];
        $this->svc()->createLead($data, 1); // first lead — model sets ownership_expires_at 60d out

        $this->expectException(\RuntimeException::class);
        $this->svc()->createLead($data, 1); // same company, still active → refused
    }

    public function test_create_lead_defaults_to_the_registry_default_product(): void
    {
        $lead = $this->svc()->createLead([
            'company_name' => 'Gamma Ltd', 'country' => 'KE', 'city' => 'Nairobi', 'phone' => '0711111111',
            'contact_person_name' => 'A', 'contact_person_role' => 'B',
        ], 1);

        $this->assertSame(\App\Services\AffiliateOs\ProductRegistry::default(), $lead->product);
        $this->assertSame('property_sales', $lead->product);
    }

    public function test_schedule_demo_transitions_and_dispatches_mail(): void
    {
        Bus::fake();
        $lead = $this->makeLead();

        $this->svc()->scheduleDemo($lead, now()->addDay()->toDateTimeString());

        $this->assertSame('demo_scheduled', $lead->fresh()->status);
        $this->assertNull($lead->fresh()->demo_meeting_link); // no link → stays null
        $this->assertSame('contacted', $lead->company->fresh()->sales_status);
        Bus::assertDispatched(SendDemoScheduledMail::class);
    }

    public function test_schedule_demo_stores_and_forwards_meeting_link(): void
    {
        Bus::fake();
        $lead = $this->makeLead();
        $link = 'https://meet.google.com/abc-defg-hij';

        $this->svc()->scheduleDemo($lead, now()->addDay()->toDateTimeString(), $link);

        $this->assertSame($link, $lead->fresh()->demo_meeting_link);
        Bus::assertDispatched(SendDemoScheduledMail::class, fn ($job) => $job->meetingLink === $link);
    }

    public function test_schedule_demo_normalises_blank_link_to_null(): void
    {
        Bus::fake();
        $lead = $this->makeLead();

        $this->svc()->scheduleDemo($lead, now()->addDay()->toDateTimeString(), '');

        $this->assertNull($lead->fresh()->demo_meeting_link);
        Bus::assertDispatched(SendDemoScheduledMail::class, fn ($job) => $job->meetingLink === null);
    }
}
