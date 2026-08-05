<?php

namespace App\Centresidence\Services\ChirpStack;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\Gateway;
use Illuminate\Support\Facades\Log;

/**
 * Live ChirpStack adapter (gRPC/REST). STUBBED until go-live: the integration
 * surface is fixed (register gateway + device, then wait for the join/uplink
 * webhook to activate) but the actual API calls are not wired yet. When the
 * 'live' driver is selected without this implemented, provisioning still
 * creates the logical records and leaves devices in `provisioning` until the
 * uplink webhook flips them active.
 */
class LiveChirpStackDriver implements ChirpStackDriver
{
    public function autoActivates(): bool
    {
        // Real devices activate only once ChirpStack confirms a join/uplink.
        return false;
    }

    public function registerGateway(Gateway $gateway): void
    {
        // TODO(go-live): POST gateway to ChirpStack
        //   {api_url}/api/gateways  with eui, name, location, tags.
        Log::info('ChirpStack(live) registerGateway pending integration', ['gateway_id' => $gateway->id, 'eui' => $gateway->eui]);
    }

    public function registerDevice(Device $device): void
    {
        // TODO(go-live): POST device to ChirpStack application
        //   {api_url}/api/devices  with devEUI, deviceProfileID, appKey, then
        //   the uplink webhook activates it (provisioning → active, last_seen_at).
        Log::info('ChirpStack(live) registerDevice pending integration', ['device_id' => $device->id, 'dev_eui' => $device->dev_eui]);
    }
}
