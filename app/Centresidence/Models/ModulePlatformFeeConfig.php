<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Admin-set platform fee % charged on financing applications for a module
 * (handbook §9.3). Backed by `module_platform_fee_config`.
 */
class ModulePlatformFeeConfig extends Model
{
    use HasFactory;

    protected $table = 'module_platform_fee_config';

    protected $guarded = [];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'is_active'      => 'boolean',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
