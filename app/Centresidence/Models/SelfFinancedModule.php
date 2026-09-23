<?php

namespace App\Centresidence\Models;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An owner-funded module deployment (no partner, no facility).
 */
class SelfFinancedModule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'hardware_cost'     => 'decimal:2',
        'installation_cost' => 'decimal:2',
        'total_cost'        => 'decimal:2',
        'paid_at'           => 'datetime',
        'deployed_at'       => 'datetime',
    ];

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAID            = 'paid';
    public const STATUS_DEPLOYING       = 'deploying';
    public const STATUS_DEPLOYED        = 'deployed';
    public const STATUS_CANCELLED       = 'cancelled';

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
