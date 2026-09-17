<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A batched payout of a tenant's confirmed invite-a-landlord rewards to their M-Pesa.
 * Mirrors the affiliate/owner withdrawal lifecycle and shares the B2C callback rail.
 */
class ReferralPayout extends Model
{
    public const STATUS_PENDING    = 'pending';     // created, B2C not yet initiated
    public const STATUS_PROCESSING = 'processing';  // B2C accepted, awaiting the ResultURL
    public const STATUS_PAID       = 'paid';        // money confirmed delivered
    public const STATUS_FAILED     = 'failed';      // B2C failed → covered referrals released
    public const STATUS_CANCELLED  = 'cancelled';   // abandoned before send → referrals released

    protected $fillable = [
        'referrer_user_id', 'amount', 'currency', 'phone',
        'status', 'settlement_method', 'mpesa_reference', 'transaction_id',
        'processed_at', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referrals()
    {
        return $this->hasMany(LandlordReferral::class, 'payout_id');
    }
}
