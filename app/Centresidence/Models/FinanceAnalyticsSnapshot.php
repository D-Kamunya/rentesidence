<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Daily finance portfolio snapshot (handbook §9.9). */
class FinanceAnalyticsSnapshot extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'snapshot_date'                => 'date',
        'total_outstanding_principal'  => 'decimal:2',
        'total_outstanding_interest'   => 'decimal:2',
        'total_outstanding_penalty'    => 'decimal:2',
        'total_expected_monthly'       => 'decimal:2',
        'total_collected_month'        => 'decimal:2',
        'collection_rate'              => 'decimal:2',
        'default_rate'                 => 'decimal:2',
        'total_platform_fees_month'    => 'decimal:2',
        'total_platform_fees_ytd'      => 'decimal:2',
        'average_interest_rate'        => 'decimal:2',
    ];
}
