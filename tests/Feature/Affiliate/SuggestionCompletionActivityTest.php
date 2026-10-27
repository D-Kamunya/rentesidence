<?php

namespace Tests\Feature\Affiliate;

use App\Models\LeadSuggestion;
use App\Services\SuggestionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Completing a suggestion manually ("Done", handled off-platform) must leave a trace on the lead
 * timeline — channel actions already log their own activity, the manual complete previously didn't.
 * Runs on an isolated in-memory SQLite connection.
 */
class SuggestionCompletionActivityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sugg_sqlite',
            'database.connections.sugg_sqlite' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('sugg_sqlite');
        DB::setDefaultConnection('sugg_sqlite');

        Schema::create('lead_suggestions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('lead_id');
            $t->unsignedBigInteger('affiliate_id')->nullable();
            $t->string('message')->nullable();
            $t->string('action_type')->nullable();
            $t->string('category')->nullable();
            $t->string('priority')->nullable();
            $t->string('status')->default('pending');
            $t->string('execution_type')->nullable();
            $t->unsignedBigInteger('executed_by')->nullable();
            $t->timestamp('executed_at')->nullable();
            $t->timestamp('expires_at')->nullable();
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

    public function test_manual_complete_logs_a_suggestion_completed_activity(): void
    {
        $suggestion = LeadSuggestion::create([
            'lead_id' => 7, 'message' => '🔥 Call and set them up on Free',
            'action_type' => 'call', 'category' => 'demo_complete', 'priority' => 'high', 'status' => 'pending',
        ]);

        app(SuggestionService::class)->completeManually($suggestion);

        $this->assertSame('completed', $suggestion->fresh()->status);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => 7,
            'type'    => 'suggestion_completed',
        ]);
        $this->assertStringContainsString(
            'set them up on Free',
            DB::table('lead_activities')->where('type', 'suggestion_completed')->value('description')
        );
    }
}
