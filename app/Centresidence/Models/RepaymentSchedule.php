<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One repayment period of a facility's amortisation schedule (handbook §9.4.3).
 */
class RepaymentSchedule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'due_date'        => 'date',
        'opening_balance' => 'decimal:2',
        'principal_due'   => 'decimal:2',
        'interest_due'    => 'decimal:2',
        'total_due'       => 'decimal:2',
        'principal_paid'  => 'decimal:2',
        'interest_paid'   => 'decimal:2',
        'penalty_paid'    => 'decimal:2',
        'total_paid'      => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID    = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_WAIVED  = 'waived';

    public function facility(): BelongsTo
    {
        return $this->belongsTo(FinanceFacility::class, 'finance_facility_id');
    }
}
