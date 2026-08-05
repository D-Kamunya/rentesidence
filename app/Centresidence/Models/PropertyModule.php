<?php

namespace App\Centresidence\Models;

use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A module activated on a property (optionally a single unit). The billing
 * engines iterate over these; `active_meter_count` drives per-device cost
 * components and commission.
 *
 * Relationships reach into the legacy app (Property/PropertyUnit/User) but the
 * module never mutates those tables.
 *
 * @property int    $active_meter_count
 * @property string $billing_model
 */
class PropertyModule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'active_meter_count' => 'integer',
        'config'             => 'array',
        'activated_at'       => 'datetime',
        'deactivated_at'     => 'datetime',
    ];

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const BILLING_SUBSCRIPTION = 'subscription';
    public const BILLING_TRANSACTION  = 'transaction';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ── Module-side relationships ─────────────────────────────────────────

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function tokenConfig(): HasOne
    {
        return $this->hasOne(ModuleTokenConfig::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function activeDevices(): HasMany
    {
        return $this->devices()->where('status', Device::STATUS_ACTIVE);
    }

    // ── Legacy-app relationships (read-only references) ───────────────────

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function propertyUnit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isMetered(): bool
    {
        return (bool) optional($this->module)->is_metered;
    }

    public function isSubscriptionBilled(): bool
    {
        return $this->billing_model === self::BILLING_SUBSCRIPTION;
    }

    /**
     * Days this module was active within the given billing month — for
     * partial-month proration. A module activated/deactivated mid-cycle is
     * billed only for the overlapping days. Defaults to the full period when no
     * activation/deactivation dates are set.
     */
    public function activeDaysInMonth(\Illuminate\Support\Carbon $month, int $periodDays): int
    {
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $start = $this->activated_at ? $this->activated_at->copy()->max($monthStart) : $monthStart;
        $end = $this->deactivated_at ? $this->deactivated_at->copy()->min($monthEnd) : $monthEnd;

        if ($start->gt($monthEnd) || $end->lt($monthStart)) {
            return 0;
        }

        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;

        return max(0, min($days, $periodDays));
    }

    /**
     * Recompute active_meter_count from live active devices and persist it.
     * Called by Device activation/deactivation events (WP3/WP4); exposed here
     * so the count always has a single, authoritative derivation.
     */
    public function syncActiveMeterCount(): int
    {
        $count = $this->activeDevices()->count();

        if ($count !== $this->active_meter_count) {
            $this->forceFill(['active_meter_count' => $count])->save();
        }

        return $count;
    }
}
