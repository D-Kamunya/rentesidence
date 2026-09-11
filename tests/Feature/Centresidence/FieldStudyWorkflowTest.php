<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\FieldStudyRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Field-study → quotation → apply workflow for custom installs (reticulated gas). Owner requests a
 * survey (IDOR-scoped to their own property + a real field-study module); admin records the quote;
 * owner proceeds only once quoted. See token-tariff-ownership (gas workflow).
 */
class FieldStudyWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $defs = [
            'modules'    => fn ($t) => [$t->id(), $t->string('key')->nullable(), $t->string('name')->nullable(), $t->boolean('is_active')->default(true), $t->boolean('requires_field_study')->default(false)],
            'properties' => fn ($t) => [$t->id(), $t->unsignedBigInteger('owner_user_id')->nullable(), $t->string('name')->nullable()],
            'property_units' => fn ($t) => [$t->id(), $t->unsignedBigInteger('property_id')->nullable(), $t->string('unit_name')->nullable()],
            'field_study_requests' => fn ($t) => [$t->id(), $t->unsignedBigInteger('owner_id'), $t->unsignedBigInteger('property_id')->nullable(), $t->unsignedInteger('units')->nullable(), $t->unsignedBigInteger('module_id'), $t->string('status')->default('requested'), $t->text('note')->nullable(), $t->decimal('quoted_amount', 14, 2)->nullable(), $t->text('quote_note')->nullable(), $t->unsignedBigInteger('quoted_by')->nullable(), $t->timestamp('quoted_at')->nullable()],
        ];
        foreach ($defs as $name => $cols) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, function ($t) use ($cols) { $cols($t); $t->timestamps(); $t->softDeletes(); });
            }
        }
    }

    private function actor(int $id, int $role = USER_ROLE_OWNER): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }

    /** @test */
    public function owner_can_request_a_survey_for_a_field_study_module_on_their_property(): void
    {
        $moduleId = DB::table('modules')->insertGetId(['key' => 'gas_meter', 'name' => 'Reticulated Gas', 'is_active' => true, 'requires_field_study' => true]);
        $propId   = DB::table('properties')->insertGetId(['owner_user_id' => 10, 'name' => 'Court A']);

        $this->withoutMiddleware()->actingAs($this->actor(10))
            ->post(route('owner.financing.surveys.request'), ['module_id' => $moduleId, 'property_id' => $propId, 'note' => '12 units'])
            ->assertRedirect(route('owner.financing.surveys'));

        $this->assertSame(1, FieldStudyRequest::where('owner_id', 10)->where('status', 'requested')->count());
    }

    /** @test */
    public function a_request_for_another_owners_property_is_rejected(): void
    {
        $moduleId = DB::table('modules')->insertGetId(['key' => 'gas_meter', 'name' => 'Gas', 'is_active' => true, 'requires_field_study' => true]);
        $propId   = DB::table('properties')->insertGetId(['owner_user_id' => 10, 'name' => 'Court A']); // owner 10's

        $this->withoutMiddleware()->actingAs($this->actor(99)) // owner 99 attacks
            ->post(route('owner.financing.surveys.request'), ['module_id' => $moduleId, 'property_id' => $propId])
            ->assertSessionHas('error');

        $this->assertSame(0, FieldStudyRequest::count());
    }

    /** @test */
    public function admin_quote_moves_it_to_quoted_and_owner_can_then_proceed(): void
    {
        $moduleId = DB::table('modules')->insertGetId(['key' => 'gas_meter', 'name' => 'Gas', 'is_active' => true, 'requires_field_study' => true]);
        $reqId = FieldStudyRequest::create(['owner_id' => 10, 'property_id' => null, 'module_id' => $moduleId, 'status' => 'requested'])->id;

        // Owner can't proceed before a quote exists.
        $this->withoutMiddleware()->actingAs($this->actor(10))
            ->post(route('owner.financing.surveys.proceed', $reqId))->assertSessionHas('error');

        // Admin records the quote.
        $this->withoutMiddleware()->actingAs($this->actor(1, USER_ROLE_ADMIN))
            ->post(route('admin.centresidence.field-studies.quote', $reqId), ['quoted_amount' => 250000, 'quote_note' => 'incl. install'])
            ->assertRedirect();

        $req = FieldStudyRequest::find($reqId);
        $this->assertSame('quoted', $req->status);
        $this->assertEqualsWithDelta(250000.0, (float) $req->quoted_amount, 0.01);

        // Accepting reveals the financiers — redirects to the module page carrying the quote (fsr).
        // Status stays 'quoted' until a finance application is actually submitted (storeFromQuote).
        $this->withoutMiddleware()->actingAs($this->actor(10))
            ->post(route('owner.financing.surveys.proceed', $reqId))
            ->assertRedirect(route('owner.financing.module', ['moduleId' => $req->module_id, 'fsr' => $reqId]));
        $this->assertSame('quoted', FieldStudyRequest::find($reqId)->status);
    }
}
