<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A tenant's single stable, shareable invite code — the /invite/{code} link they hand
 * their landlord. One row per referring tenant; created on first use by
 * LandlordReferralService::codeForTenant().
 */
class LandlordReferralCode extends Model
{
    protected $fillable = ['user_id', 'code'];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
