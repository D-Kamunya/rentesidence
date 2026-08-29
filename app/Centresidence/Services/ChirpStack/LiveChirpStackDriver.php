<?php

namespace App\Centresidence\Services\ChirpStack;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Services\ChirpStack\Codec\MeterCodec;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Live ChirpStack (v4) adapter over its REST/gRPC-gateway API. Registers
 * gateways/devices and enqueues downlinks; inbound join/uplinks are handled by
 * the webhook (ChirpStackUplinkController). Registration failures are logged,
 * never thrown — provisioning still creates the logical records and the device
 * activates when its join uplink arrives. The meter-specific byte format lives
 * entirely in the injected MeterCodec.
 */
class LiveChirpStackDriver implements ChirpStackDriver
{
    public function __construct(private MeterCodec $codec)
    {
    }

    public function autoActivates(): bool
    {
        // Real devices activate only once ChirpStack confirms a join/uplink.
        return false;
    }

    public function registerGateway(Gateway $gateway): void
    {
        try {
            $resp = $this->http()->post('/api/gateways', [
                'gateway' => array_filter([
                    'gatewayId'  => $this->hex($gateway->eui),
                    'name'       => $gateway->name ?: ('gw-' . $gateway->id),
                    'description' => 'Centresidence gateway #' . $gateway->id,
                    'tenantId'   => config('centresidence.chirpstack.tenant_id'),
                    'statsInterval' => 30,
                ], fn ($v) => $v !== null && $v !== ''),
            ]);

            $this->stampNetwork($gateway, $resp->successful() || $this->alreadyExists($resp), $resp->status(), $resp->json());
            if (! $resp->successful() && ! $this->alreadyExists($resp)) {
                Log::warning('ChirpStack registerGateway failed', ['gateway_id' => $gateway->id, 'status' => $resp->status(), 'body' => $resp->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('ChirpStack registerGateway error', ['gateway_id' => $gateway->id, 'error' => $e->getMessage()]);
            $this->stampNetwork($gateway, false, null, ['error' => $e->getMessage()]);
        }
    }

    public function registerDevice(Device $device): void
    {
        try {
            $resp = $this->http()->post('/api/devices', [
                'device' => array_filter([
                    'devEui'          => $this->hex($device->dev_eui),
                    'name'            => $device->name ?: ('dev-' . $device->id),
                    'description'     => 'Centresidence device #' . $device->id,
                    'applicationId'   => config('centresidence.chirpstack.application_id'),
                    'deviceProfileId' => config('centresidence.chirpstack.device_profile'),
                    'isDisabled'      => false,
                ], fn ($v) => $v !== null && $v !== ''),
            ]);

            $ok = $resp->successful() || $this->alreadyExists($resp);
            $this->stampNetwork($device, $ok, $resp->status(), $resp->json());

            // Register the OTAA AppKey when we hold it (meter label / metadata).
            $appKey = $device->metadata['app_key'] ?? null;
            if ($ok && $appKey) {
                $this->http()->post("/api/devices/{$this->hex($device->dev_eui)}/keys", [
                    'deviceKeys' => [
                        'devEui' => $this->hex($device->dev_eui),
                        'nwkKey' => $appKey, // LoRaWAN 1.0.x: nwkKey holds the AppKey
                        'appKey' => $appKey,
                    ],
                ]);
            }

            if (! $ok) {
                Log::warning('ChirpStack registerDevice failed', ['device_id' => $device->id, 'status' => $resp->status(), 'body' => $resp->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('ChirpStack registerDevice error', ['device_id' => $device->id, 'error' => $e->getMessage()]);
            $this->stampNetwork($device, false, null, ['error' => $e->getMessage()]);
        }
    }

    public function sendDownlink(DeviceCommand $command): bool
    {
        $device = $command->device;
        if (! $device || ! $device->dev_eui) {
            $command->forceFill(['status' => DeviceCommand::STATUS_FAILED, 'response' => ['error' => 'no device / dev_eui']])->save();
            return false;
        }

        try {
            $frame = $this->codec->encodeDownlink($command);

            $resp = $this->http()->post("/api/devices/{$this->hex($device->dev_eui)}/queue", [
                'queueItem' => [
                    'devEui'    => $this->hex($device->dev_eui),
                    'confirmed' => true,
                    'fPort'     => (int) $frame['f_port'],
                    'data'      => $frame['data'], // base64
                ],
            ]);

            if ($resp->successful()) {
                $command->forceFill([
                    'status'   => DeviceCommand::STATUS_SENT,
                    'response' => ['queued' => true, 'chirpstack' => $resp->json()],
                ])->save();
                return true;
            }

            $command->forceFill(['status' => DeviceCommand::STATUS_FAILED, 'response' => ['status' => $resp->status(), 'body' => $resp->body()]])->save();
            Log::warning('ChirpStack downlink enqueue failed', ['command_id' => $command->id, 'status' => $resp->status()]);
            return false;
        } catch (\Throwable $e) {
            $command->forceFill(['status' => DeviceCommand::STATUS_FAILED, 'response' => ['error' => $e->getMessage()]])->save();
            Log::error('ChirpStack downlink error', ['command_id' => $command->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('centresidence.chirpstack.api_url'), '/'))
            ->withToken((string) config('centresidence.chirpstack.api_token'))
            ->timeout((int) config('centresidence.chirpstack.timeout', 10))
            ->acceptJson();
    }

    /** ChirpStack EUIs are lowercase hex with no separators. */
    private function hex(?string $eui): string
    {
        return strtolower(preg_replace('/[^0-9a-fA-F]/', '', (string) $eui));
    }

    private function alreadyExists($resp): bool
    {
        return $resp->status() === 409
            || str_contains(strtolower((string) $resp->body()), 'already exists');
    }

    private function stampNetwork($model, bool $ok, ?int $status, $body): void
    {
        $model->forceFill([
            'metadata' => array_merge($model->metadata ?? [], [
                'chirpstack' => $ok ? 'registered' : 'registration_failed',
                'chirpstack_status' => $status,
                'chirpstack_synced_at' => Carbon::now()->toIso8601String(),
            ]),
        ])->save();
    }
}
