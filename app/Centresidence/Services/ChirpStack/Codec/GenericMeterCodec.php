<?php

namespace App\Centresidence\Services\ChirpStack\Codec;

use App\Centresidence\Models\DeviceCommand;

/**
 * A safe, documented DEFAULT codec so the pipeline is testable before a real
 * meter exists. It is deliberately simple and MUST be replaced with a
 * meter-specific codec at go-live (config('centresidence.chirpstack.codec')).
 *
 * Conventions used here (typical LoRaWAN utility meters, but confirm per device):
 *  - Uplink: if ChirpStack already ran a network-side codec, we trust its decoded
 *    `object` (looks for a `consumed`/`delta`/`litres`/`units` key). Otherwise the
 *    raw payload is read as a big-endian unsigned integer = units consumed since
 *    the last frame (a DELTA — meters that report cumulative totals need a codec
 *    that subtracts the device's previous reading).
 *  - Downlink: `credit_tokens` → the whole-unit amount packed big-endian into 4
 *    bytes on the configured fPort.
 */
class GenericMeterCodec implements MeterCodec
{
    public function decodeUplink(string $base64Payload, int $fPort, array $context = []): ?string
    {
        // Prefer ChirpStack's own decoded object when a device-profile codec ran.
        $object = $context['object'] ?? null;
        if (is_array($object)) {
            foreach (['consumed', 'delta', 'consumption', 'litres', 'liters', 'units', 'usage'] as $key) {
                if (isset($object[$key]) && is_numeric($object[$key]) && (float) $object[$key] > 0) {
                    return (string) $object[$key];
                }
            }
            return null; // decoded frame carried no consumption (heartbeat/status)
        }

        // Fall back to raw bytes: big-endian unsigned integer = delta units.
        $bytes = base64_decode($base64Payload, true);
        if ($bytes === false || $bytes === '') {
            return null;
        }
        $value = 0;
        foreach (str_split($bytes) as $byte) {
            $value = ($value * 256) + ord($byte);
        }

        return $value > 0 ? (string) $value : null;
    }

    public function encodeDownlink(DeviceCommand $command): array
    {
        $fport = (int) config('centresidence.chirpstack.downlink_fport', 10);
        $units = (int) round((float) ($command->payload['units'] ?? 0));
        $units = max(0, min($units, 0xFFFFFFFF));

        // 4-byte big-endian whole units.
        $data = pack('N', $units);

        return ['f_port' => $fport, 'data' => base64_encode($data)];
    }
}
