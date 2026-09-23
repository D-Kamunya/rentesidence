<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One record of an owner screening a person (Step 4). Doubles as the tenant-facing access
 * log (transparency) and the free-allowance meter (billed_as='free' rows this month). The
 * score fields are a SNAPSHOT frozen at lookup time, so a later recompute never rewrites
 * what the owner was shown.
 */
class TenantScreeningLookup extends Model
{
    protected $fillable = [
        'owner_user_id', 'identity_key', 'phone', 'tenant_credit_profile_id',
        'score', 'score_band', 'score_grade', 'was_thin_file', 'was_activated', 'billed_as',
    ];

    protected $casts = [
        'score'         => 'decimal:2',
        'was_thin_file' => 'boolean',
        'was_activated' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function profile()
    {
        return $this->belongsTo(TenantCreditProfile::class, 'tenant_credit_profile_id');
    }
}
