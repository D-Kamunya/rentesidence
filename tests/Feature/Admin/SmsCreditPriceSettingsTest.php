<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\SmsCreditsAdminController;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The admin "Standard price per SMS credit" card: saving it used to 500 (it called the non-existent
 * updateOption()), and it edited the stale global option instead of the Free package that actually
 * charges free owners — so it showed 2 while the free tier charged 3. This guards both: the save
 * persists without error, to the Free package (source of truth) AND the global fallback.
 */
class SmsCreditPriceSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.sms_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'sms_sqlite']);
        DB::purge('sms_sqlite');
        \Illuminate\Database\Eloquent\Model::unguard();

        Schema::create('settings', function ($t) {
            $t->id();
            $t->string('option_key')->unique();
            $t->text('option_value')->nullable();
            $t->timestamps();
        });
        Schema::create('packages', function ($t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('pricing_model')->nullable();
            $t->unsignedTinyInteger('status')->default(1);
            $t->unsignedTinyInteger('is_default')->default(0);
            $t->unsignedTinyInteger('is_trail')->default(0);
            $t->decimal('sms_price_per_credit', 10, 2)->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
    }

    public function test_saving_persists_to_free_package_and_option_without_500(): void
    {
        // The Free tier currently charges 2.00 (stale); the card should be able to set it to 3.00.
        Package::create([
            'name' => 'Free', 'pricing_model' => 'free', 'status' => 1,
            'is_default' => 1, 'is_trail' => 0, 'sms_price_per_credit' => 2.00,
        ]);

        $request = Request::create('/admin/sms-credits/settings', 'PUT', [
            'sms_credit_price'         => 3,
            'sms_low_credit_threshold' => 30,
        ]);
        $this->app->instance('request', $request);

        // The regression: this used to throw (undefined updateOption) → 500.
        $response = (new SmsCreditsAdminController)->updateSettings($request);
        $this->assertNotNull($response);

        // Source of truth — the Free package — now reads 3 (decimal formatting differs by driver).
        $this->assertSame(3.0, (float) Package::where('pricing_model', 'free')->first()->sms_price_per_credit);
        // Global fallback kept in sync.
        $this->assertSame(3.0, (float) Setting::where('option_key', 'sms_credit_price')->value('option_value'));
        $this->assertSame(30, (int) Setting::where('option_key', 'sms_low_credit_threshold')->value('option_value'));
    }
}
