<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A tenant's dispute against what their credit profile shows — the fairness path for the
 * Global Tenant ID. Reviewed by admin; the profile itself is never silently overwritten.
 */
class TenantCreditDispute extends Model
{
    protected $fillable = [
        'tenant_credit_profile_id', 'user_id', 'message', 'status', 'admin_note', 'resolution', 'resolved_at',
        'tenant_reply', 'tenant_ack_at', 'owner_notified_at',
    ];

    protected $casts = [
        'resolved_at'       => 'datetime',
        'tenant_ack_at'     => 'datetime',
        'owner_notified_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(TenantCreditProfile::class, 'tenant_credit_profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
