<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A configurable underwriting condition for a partner product (handbook §9.2.3).
 */
class UnderwritingRule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_hard_rule' => 'boolean',
    ];

    public const OP_GTE      = 'gte';
    public const OP_LTE      = 'lte';
    public const OP_EQ       = 'eq';
    public const OP_BETWEEN  = 'between';
    public const OP_REQUIRED = 'required';

    public function partnerModule(): BelongsTo
    {
        return $this->belongsTo(FinancePartnerModule::class, 'finance_partner_module_id');
    }
}
