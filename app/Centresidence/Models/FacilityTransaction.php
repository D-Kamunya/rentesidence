<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single financial movement on a facility (handbook §9.4.4). Append-only.
 */
class FacilityTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public const TYPE_DISBURSEMENT        = 'disbursement';
    public const TYPE_DOWN_PAYMENT        = 'down_payment';
    public const TYPE_REPAYMENT_PRINCIPAL = 'repayment_principal';
    public const TYPE_REPAYMENT_INTEREST  = 'repayment_interest';
    public const TYPE_REPAYMENT_PENALTY   = 'repayment_penalty';

    public function facility(): BelongsTo
    {
        return $this->belongsTo(FinanceFacility::class, 'finance_facility_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(RepaymentSchedule::class, 'repayment_schedule_id');
    }
}
