<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Verification document for a finance partner (handbook §9.2.1).
 */
class FinancePartnerDocument extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'verified'    => 'boolean',
        'verified_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FinancePartner::class, 'finance_partner_id');
    }
}
