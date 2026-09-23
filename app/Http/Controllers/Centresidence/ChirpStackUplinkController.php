<?php

namespace App\Http\Controllers\Centresidence;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Models\UtilityWallet;
use App\Centresidence\Services\ChirpStack\Codec\MeterCodec;
use App\Centresidence\Services\TokenEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Inbound LoRaWAN seam: ChirpStack's HTTP integration posts device events here.
 *   join  → activate the device (provisioning → active)
 *   up    → decode consumption (device-specific MeterCodec) → debit the tenant's wallet
 *   txack → mark the matching downlink command delivered
 *
 * Fail-closed: requires the shared webhook secret (config chirpstack.webhook_secret,
 * sent by ChirpStack as `Authorization: Bearer <secret>`). Uplinks are deduped on
 * (device, fCnt) so a re-fired webhook never double-debits a wallet.
 */
class ChirpStackUplinkController extends Controller
{
    public function __invoke(Request $request, TokenEngine $tokenEngine, MeterCodec $codec)
    {
        $secret = (string) config('centresidence.chirpstack.webhook_secret');
        if ($secret === '' || ! hash_equals($secret, (string) $request->bearerToken())) {
            return response()->json(['ok' => false], 401);
        }

        $event = (string) $request->query('event', $request->input('event', 'up'));
        $body  = $request->all();
        $devEui = strtolower((string) data_get($body, 'deviceInfo.devEui', data_get($body, 'devEUI', '')));

        if ($devEui === '') {
            return response()->json(['ok' => true, 'skipped' => 'no devEui']);
        }

        $device = Device::whereRaw('LOWER(REPLACE(dev_eui, ":", "")) = ?', [$devEui])->first();
        if (! $device) {
            Log::info('ChirpStack uplink for unknown device', ['dev_eui' => $devEui, 'event' => $event]);
            return response()->json(['ok' => true, 'skipped' => 'unknown device']);
        }

        switch ($event) {
            case 'join':
                $this->activate($device);
                return response()->json(['ok' => true, 'activated' => true]);

            case 'txack':
            case 'ack':
                $this->ackDownlink($device);
                return response()->json(['ok' => true]);

            case 'up':
                return $this->handleUplink($request, $device, $body, $tokenEngine, $codec);

            default:
                // status / log / location events — acknowledge, nothing to do.
                $device->forceFill(['last_seen_at' => Carbon::now()])->save();
                return response()->json(['ok' => true]);
        }
    }

    private function handleUplink(Request $request, Device $device, array $body, TokenEngine $tokenEngine, MeterCodec $codec)
    {
        // First uplink also activates a still-provisioning device.
        if ($device->status !== Device::STATUS_ACTIVE) {
            $this->activate($device);
        } else {
            $device->forceFill(['last_seen_at' => Carbon::now()])->save();
        }

        // Dedup re-fired webhooks on the frame counter.
        $fcnt = data_get($body, 'fCnt', data_get($body, 'fcnt'));
        if ($fcnt !== null && (string) ($device->metadata['last_fcnt'] ?? null) === (string) $fcnt) {
            return response()->json(['ok' => true, 'deduped' => true]);
        }

        $units = $codec->decodeUplink(
            (string) data_get($body, 'data', ''),
            (int) data_get($body, 'fPort', 0),
            ['dev_eui' => $device->dev_eui, 'device' => $device, 'object' => data_get($body, 'object')]
        );

        // Record the frame counter regardless, so retries of a no-consumption frame also dedup.
        if ($fcnt !== null) {
            $device->forceFill(['metadata' => array_merge($device->metadata ?? [], ['last_fcnt' => (string) $fcnt])])->save();
        }

        if ($units === null || (float) $units <= 0) {
            return response()->json(['ok' => true, 'consumption' => 0]);
        }

        $wallet = $this->resolveWallet($device);
        if (! $wallet) {
            Log::warning('ChirpStack consumption could not be attributed to a wallet', ['device_id' => $device->id, 'units' => $units]);
            return response()->json(['ok' => true, 'skipped' => 'no wallet']);
        }

        $tokenEngine->recordConsumption($wallet, (string) $units, $device, 'telemetry');
        return response()->json(['ok' => true, 'consumed' => $units]);
    }

    private function activate(Device $device): void
    {
        $device->forceFill([
            'status'       => Device::STATUS_ACTIVE,
            'is_simulated' => false,
            'activated_at' => $device->activated_at ?: Carbon::now(),
            'last_seen_at' => Carbon::now(),
        ])->save();
    }

    /** Best-effort: mark this device's most recent sent downlink delivered. */
    private function ackDownlink(Device $device): void
    {
        $device->forceFill(['last_seen_at' => Carbon::now()])->save();

        DeviceCommand::where('device_id', $device->id)
            ->where('status', DeviceCommand::STATUS_SENT)
            ->latest('id')
            ->limit(1)
            ->update(['status' => DeviceCommand::STATUS_ACKED, 'acked_at' => Carbon::now()]);
    }

    /**
     * Resolve which tenant's wallet a meter's consumption belongs to:
     *  1. device bound to a unit → that unit's active tenant's wallet;
     *  2. else a single-wallet module (one meter, one tenant) → that wallet;
     *  3. else ambiguous → null (never guess; money attribution must be exact).
     */
    private function resolveWallet(Device $device): ?UtilityWallet
    {
        if ($device->property_unit_id) {
            $tenantUserId = DB::table('tenants')
                ->where('unit_id', $device->property_unit_id)
                ->whereNull('deleted_at')
                ->where('status', TENANT_STATUS_ACTIVE)
                ->value('user_id');

            if ($tenantUserId) {
                return UtilityWallet::where('property_module_id', $device->property_module_id)
                    ->where('tenant_user_id', $tenantUserId)
                    ->first();
            }
        }

        $wallets = UtilityWallet::where('property_module_id', $device->property_module_id)->get();
        return $wallets->count() === 1 ? $wallets->first() : null;
    }
}
