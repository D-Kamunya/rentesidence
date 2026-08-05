<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A module *type* the platform can deploy and bill for (Water Meter, Gas
 * Meter, Smart Lock, …). Behaviour is config/row-driven, never hardcoded.
 *
 * @property string $key
 * @property bool   $is_metered
 * @property bool   $requires_gateway
 */
class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_metered'       => 'boolean',
        'requires_gateway' => 'boolean',
        'is_financeable'   => 'boolean',
        'is_active'        => 'boolean',
        'config'           => 'array',
        'benefits'         => 'array',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMetered($query)
    {
        return $query->where('is_metered', true);
    }

    public function scopeNonMetered($query)
    {
        return $query->where('is_metered', false);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function costComponents(): HasMany
    {
        return $this->hasMany(ModuleCostComponent::class);
    }

    /** Only active components, in display order — what the billing engine sums. */
    public function activeCostComponents(): HasMany
    {
        return $this->costComponents()
            ->where('status', 'active')
            ->orderBy('display_order');
    }

    public function pricingCatalogueItems(): HasMany
    {
        return $this->hasMany(ModulePricingCatalogueItem::class);
    }

    public function platformFeeConfigs(): HasMany
    {
        return $this->hasMany(ModulePlatformFeeConfig::class);
    }

    public function propertyModules(): HasMany
    {
        return $this->hasMany(PropertyModule::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Non-metered modules (locks, parking) can never recover cost from token
     * revenue, regardless of component flags — the tenant-continuity guard.
     */
    public function isFallbackCapable(): bool
    {
        return $this->is_metered;
    }

    // ── Presentation fallbacks (so cards/detail always render nicely) ──────

    public function displayIcon(): string
    {
        return $this->icon ?: ($this->is_metered ? 'ri-dashboard-3-line' : 'ri-shield-keyhole-line');
    }

    public function displayColor(): string
    {
        return $this->accent_color ?: '#185FA5';
    }
}
