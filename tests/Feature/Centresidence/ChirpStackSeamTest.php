<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\UtilityWallet;
use App\Centresidence\Support\Money;
use App\Centresidence\Services\ChirpStack\Codec\MeterCodec;
use App\Centresidence\Services\DeviceCommandDispatcher;
use App\Centresidence\Services\DeviceProvisioningService;
use App\Centresidence\Services\TokenEngine;
use App\Http\Controllers\Centresidence\ChirpStackUplinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The LoRaWAN seam: inbound uplink webhook (auth, join→activate, consumption
 * drawdown, dedup) and the outbound downlink dispatcher. Runs on the simulated
 * driver like the rest of the Centresidence suite.
 */
class ChirpStackSeamTest extends CentresidenceDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['centresidence.chirpstack.webhook_secret' => 'testsecret']);
    }

    /** Deploy one real metered device (FK-valid) and return it. */
    private function deployDevice(): Device
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);
        return app(DeviceProvisioningService::class)->deploy(1, 1, $module, 1)->devices()->first();
    }

    private function sendEvent(array $body, string $event, ?string $token = 'testsecret')
    {
        $headers = $token
            ? ['HTTP_AUTHORIZATION' => "Bearer {$token}", 'CONTENT_TYPE' => 'application/json']
            : ['CONTENT_TYPE' => 'application/json'];
        $request = Request::create("/api/centresidence/chirpstack/uplink?event={$event}", 'POST', [], [], [], $headers, json_encode($body));

        return (new ChirpStackUplinkController())($request, app(TokenEngine::class), app(MeterCodec::class));
    }

    public function test_webhook_rejects_missing_or_wrong_secret(): void
    {
        // Auth is checked before any device lookup — no device needed.
        $this->assertSame(401, $this->sendEvent(['deviceInfo' => ['devEui' => 'aa11bb22cc33dd44']], 'join', null)->getStatusCode());
        $this->assertSame(401, $this->sendEvent(['deviceInfo' => ['devEui' => 'aa11bb22cc33dd44']], 'join', 'wrong')->getStatusCode());
    }

    public function test_join_activates_a_provisioning_device(): void
    {
        $device = $this->deployDevice();
        $device->forceFill(['status' => Device::STATUS_PROVISIONING, 'is_simulated' => false, 'activated_at' => null])->save();

        $resp = $this->sendEvent(['deviceInfo' => ['devEui' => strtoupper($device->dev_eui)]], 'join'); // upper-case still matches

        $this->assertSame(200, $resp->getStatusCode());
        $device->refresh();
        $this->assertSame(Device::STATUS_ACTIVE, $device->status);
        $this->assertNotNull($device->activated_at);
    }

    public function test_uplink_records_consumption_against_the_single_wallet_and_dedupes(): void
    {
        $device = $this->deployDevice();
        DB::table('users')->insert(['id' => 999, 'first_name' => 'Test', 'last_name' => 'Tenant']);

        $wallet = UtilityWallet::create([
            'property_module_id' => $device->property_module_id,
            'tenant_user_id'     => 999,
            'unit_label'         => 'litres',
            'balance_units'      => '1000',
        ]);

        $body = ['deviceInfo' => ['devEui' => $device->dev_eui], 'fCnt' => 5, 'fPort' => 10, 'object' => ['consumed' => 40]];

        $this->sendEvent($body, 'up');
        $this->assertSame('960.0000', (string) $wallet->fresh()->balance_units, 'first uplink debits 40');

        // Same frame counter (re-fired webhook) must NOT debit again.
        $this->sendEvent($body, 'up');
        $this->assertSame('960.0000', (string) $wallet->fresh()->balance_units, 'duplicate uplink is ignored');

        // A new frame counter debits again.
        $body['fCnt'] = 6;
        $this->sendEvent($body, 'up');
        $this->assertSame('920.0000', (string) $wallet->fresh()->balance_units, 'next frame debits 40 more');
    }

    public function test_token_credit_targets_the_buyers_own_meter(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'token_unit_label' => 'Litres']);
        $pm = PropertyModule::create([
            'property_id' => 1, 'module_id' => $module->id, 'owner_id' => 1,
            'active_meter_count' => 2,
            'billing_model' => PropertyModule::BILLING_SUBSCRIPTION,
            'status' => PropertyModule::STATUS_ACTIVE,
        ]);
        $pm->tokenConfig()->create(['token_unit_label' => 'Litres', 'units_per_kes' => '5', 'centresidence_commission_per_token_unit' => '0']);

        $meterA = $pm->devices()->create(['name' => 'Meter A', 'dev_eui' => 'aa01', 'status' => Device::STATUS_ACTIVE, 'property_unit_id' => 101]);
        $meterB = $pm->devices()->create(['name' => 'Meter B', 'dev_eui' => 'bb02', 'status' => Device::STATUS_ACTIVE, 'property_unit_id' => 102]);

        DB::table('users')->insert([['id' => 501, 'first_name' => 'A'], ['id' => 502, 'first_name' => 'B']]);
        DB::table('tenants')->insert([
            ['user_id' => 501, 'unit_id' => 101],
            ['user_id' => 502, 'unit_id' => 102],
        ]);

        // The tenant on unit 102 buys — the credit must target Meter B, not the first device.
        $purchase = app(TokenEngine::class)->purchase($pm, 502, Money::fromDecimal('100.00'));

        $cmd = DeviceCommand::find($purchase->device_command_id);
        $this->assertNotNull($cmd, 'a downlink command was queued');
        $this->assertSame($meterB->id, $cmd->device_id, 'token credit targets the buyer’s own meter (unit 102), not activeDevices()->first()');
    }

    public function test_dispatcher_sends_queued_commands_under_simulated_driver(): void
    {
        $device = $this->deployDevice();
        $command = DeviceCommand::create([
            'device_id' => $device->id,
            'command'   => 'credit_tokens',
            'payload'   => ['units' => '100'],
            'status'    => DeviceCommand::STATUS_QUEUED,
            'issued_at' => now(),
        ]);

        $result = app(DeviceCommandDispatcher::class)->dispatchQueued();

        $this->assertSame(1, $result['sent']);
        $this->assertSame(DeviceCommand::STATUS_ACKED, $command->fresh()->status); // simulated = instant ack
    }
}
