<?php

namespace App\Http\Controllers\Owner;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Models\DeviceTelemetry;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\TokenPurchase;
use App\Centresidence\Models\UtilityConsumption;
use App\Centresidence\Models\UtilityWallet;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Owner-facing, READ-ONLY view of the smart infrastructure they own/finance:
 * installed devices (meters/locks), their online status, and the token
 * economics behind each metered module (prepaid balances in circulation +
 * the owner's token revenue).
 *
 * Deliberately read-only: there is NO device control here. Prepaid supply
 * self-regulates (no tokens → no supply), and interrupting a tenant's
 * already-paid-for utility to coerce rent is unlawful — so this surface
 * observes, it never disconnects.
 */
class DeviceController extends Controller
{
    /** A device is considered "online" if it has reported within this window. */
    private const ONLINE_WINDOW_MINUTES = 60;

    private function migrated(): bool
    {
        return Schema::hasTable('devices') && Schema::hasTable('property_modules');
    }

    public function index()
    {
        $ownerId = (int) auth()->id();

        $modules = collect();
        $stats = [
            'modules_active'  => 0,
            'devices_total'   => 0,
            'devices_online'  => 0,
            'balance_units'   => 0.0,
            'token_revenue'   => 0.0,
        ];
        $onlineSince = Carbon::now()->subMinutes(self::ONLINE_WINDOW_MINUTES);

        if ($this->migrated()) {
            $modules = PropertyModule::query()
                ->where('owner_id', $ownerId)
                ->with([
                    'module',
                    'property',
                    'propertyUnit',
                    'tokenConfig',
                    'devices' => fn ($q) => $q->with('propertyUnit')->orderBy('name'),
                ])
                ->orderBy('status')          // active, inactive, suspended
                ->orderByDesc('id')
                ->get();

            $moduleIds = $modules->pluck('id');

            // Tenants' prepaid wallets, aggregated per module (a module can span
            // several units, each with its own tenant wallet).
            $wallets = UtilityWallet::query()
                ->whereIn('property_module_id', $moduleIds)
                ->selectRaw('property_module_id,
                    SUM(balance_units)         as balance_units,
                    SUM(total_purchased_units) as purchased_units,
                    SUM(total_consumed_units)  as consumed_units')
                ->groupBy('property_module_id')
                ->get()
                ->keyBy('property_module_id');

            // The owner's net token revenue per module (completed purchases only).
            $revenue = TokenPurchase::query()
                ->whereIn('property_module_id', $moduleIds)
                ->where('status', TokenPurchase::STATUS_COMPLETED)
                ->selectRaw('property_module_id,
                    SUM(owner_revenue_net) as net,
                    COUNT(*)               as purchases')
                ->groupBy('property_module_id')
                ->get()
                ->keyBy('property_module_id');

            foreach ($modules as $module) {
                $w = $wallets->get($module->id);
                $r = $revenue->get($module->id);

                $online = $module->devices->filter(
                    fn (Device $d) => $d->status === Device::STATUS_ACTIVE
                        && $d->last_seen_at
                        && $d->last_seen_at->greaterThanOrEqualTo($onlineSince)
                )->count();

                $module->view_balance_units   = (float) ($w->balance_units ?? 0);
                $module->view_purchased_units = (float) ($w->purchased_units ?? 0);
                $module->view_consumed_units  = (float) ($w->consumed_units ?? 0);
                $module->view_revenue_net     = (float) ($r->net ?? 0);
                $module->view_purchase_count  = (int) ($r->purchases ?? 0);
                $module->view_devices_online  = $online;

                if ($module->status === PropertyModule::STATUS_ACTIVE) {
                    $stats['modules_active']++;
                }
                $stats['devices_total']  += $module->devices->count();
                $stats['devices_online'] += $online;
                $stats['balance_units']  += $module->view_balance_units;
                $stats['token_revenue']  += $module->view_revenue_net;
            }
        }

        return view('owner.devices.index', [
            'pageTitle'    => __('My Devices'),
            'modules'      => $modules,
            'stats'        => $stats,
            'onlineWindow' => self::ONLINE_WINDOW_MINUTES,
            // sidebar active-state (open the Financing group, highlight My devices)
            'navFinancingMMShowClass'       => 'mm-show',
            'subNavMyDevicesMMActiveClass'  => 'mm-active',
            'subNavMyDevicesActiveClass'    => 'active',
        ]);
    }

    /**
     * Per-device detail — one meter's activity: token loads pushed to it,
     * consumption events, and raw telemetry, merged into one timeline.
     * Ownership-guarded: the device must belong to a module this owner owns.
     */
    public function show($deviceId)
    {
        abort_unless($this->migrated(), 404);

        $ownerId = (int) auth()->id();

        $device = Device::query()
            ->with(['propertyUnit', 'propertyModule.module', 'propertyModule.property', 'propertyModule.propertyUnit', 'propertyModule.tokenConfig'])
            ->findOrFail($deviceId);

        // IDOR guard — a device is only viewable by the owner of its module.
        abort_unless((int) optional($device->propertyModule)->owner_id === $ownerId, 404);

        $onlineSince = Carbon::now()->subMinutes(self::ONLINE_WINDOW_MINUTES);
        $isOnline = $device->status === Device::STATUS_ACTIVE
            && $device->last_seen_at
            && $device->last_seen_at->greaterThanOrEqualTo($onlineSince);

        // ── Activity sources (recent slice of each) ───────────────────────
        $loads = DeviceCommand::query()
            ->where('device_id', $device->id)
            ->orderByDesc('issued_at')->orderByDesc('id')
            ->limit(60)->get();

        $consumption = UtilityConsumption::query()
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')->orderByDesc('id')
            ->limit(60)->get();

        $telemetry = DeviceTelemetry::query()
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')->orderByDesc('id')
            ->limit(60)->get();

        // ── Merge into one reverse-chronological timeline ─────────────────
        $timeline = collect();

        foreach ($loads as $c) {
            $units = data_get($c->payload, 'units');
            $label = data_get($c->payload, 'unit_label', __('units'));
            $timeline->push([
                'at'     => $c->issued_at ?? $c->created_at,
                'kind'   => 'load',
                'sign'   => '+',
                'title'  => $units !== null
                    ? __(':units :label loaded', ['units' => rtrim(rtrim(number_format((float) $units, 2), '0'), '.'), 'label' => $label])
                    : __('Token load'),
                'sub'    => __('Command') . ': ' . $c->command,
                'status' => $c->status,
            ]);
        }

        foreach ($consumption as $u) {
            $timeline->push([
                'at'     => $u->recorded_at ?? $u->created_at,
                'kind'   => 'use',
                'sign'   => '−',
                'title'  => __(':units consumed', ['units' => rtrim(rtrim(number_format((float) $u->units_consumed, 2), '0'), '.')]),
                'sub'    => __('Balance after') . ': ' . rtrim(rtrim(number_format((float) $u->balance_after, 2), '0'), '.') . ' · ' . $u->source,
                'status' => null,
            ]);
        }

        foreach ($telemetry as $t) {
            $timeline->push([
                'at'     => $t->recorded_at ?? $t->created_at,
                'kind'   => 'reading',
                'sign'   => '',
                'title'  => trim($t->metric . ': ' . rtrim(rtrim(number_format((float) $t->value, 2), '0'), '.') . ' ' . $t->unit),
                'sub'    => __('Meter reading'),
                'status' => null,
            ]);
        }

        $timeline = $timeline
            ->sortByDesc(fn ($e) => optional($e['at'])->timestamp ?? 0)
            ->take(60)
            ->values();

        // ── Per-device summary ────────────────────────────────────────────
        $totalConsumed = (float) UtilityConsumption::where('device_id', $device->id)->sum('units_consumed');
        $unitsLoaded = (float) $loads->sum(fn ($c) => (float) data_get($c->payload, 'units', 0));
        $loadCount = DeviceCommand::where('device_id', $device->id)->where('command', 'credit_tokens')->count();

        return view('owner.devices.show', [
            'pageTitle'   => $device->name ?: __('Device'),
            'device'      => $device,
            'isOnline'    => $isOnline,
            'onlineWindow'=> self::ONLINE_WINDOW_MINUTES,
            'timeline'    => $timeline,
            'summary'     => [
                'consumed'     => $totalConsumed,
                'units_loaded' => $unitsLoaded,
                'load_count'   => $loadCount,
            ],
            'unitLabel'   => optional(optional($device->propertyModule)->tokenConfig)->token_unit_label
                ?? data_get(optional($loads->first())->payload ?? [], 'unit_label', __('units')),
            // sidebar active-state
            'navFinancingMMShowClass'      => 'mm-show',
            'subNavMyDevicesMMActiveClass' => 'mm-active',
            'subNavMyDevicesActiveClass'   => 'active',
        ]);
    }
}
