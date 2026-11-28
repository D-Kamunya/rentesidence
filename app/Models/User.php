<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    public function owner()
    {
        return $this->hasOne(Owner::class, 'user_id');
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'user_id', 'id');
    }

    /**
     * Is this a tenant with NO active tenancy — i.e. the standalone "Tenant Helper" state?
     * owner_user_id persists after a tenancy is closed, so the CLOSED status is the signal (not the
     * owner id). When ownerless, the tenant app hides every owner-bound surface (rent, tickets,
     * notices, marketplace, maintenance, documents, agreement…) and shows only the tenant's own,
     * portable things (rental score, invoice history, profile). Data is preserved, just not linked
     * back to the former owner. Self-registered "Tenant Helper" users are created with this SAME
     * shape (a CLOSE tenancy row, owner_user_id null) so they flow through the identical ownerless
     * machinery — no separate predicate. Distinguish "never had a landlord" from "tenancy ended"
     * via owner_user_id (see isHelperTenant()).
     */
    public function isOwnerlessTenant(): bool
    {
        $tenant = $this->tenant;
        return $tenant && (int) $tenant->status === TENANT_STATUS_CLOSE;
    }

    /**
     * A self-registered Tenant Helper: ownerless AND never had a landlord (vs a moved-out tenant,
     * who is ownerless but retains owner_user_id). Drives "welcome" vs "tenancy ended" messaging.
     */
    public function isHelperTenant(): bool
    {
        return $this->isOwnerlessTenant() && is_null($this->owner_user_id);
    }

    public function maintainer(): HasOne
    {
        return $this->hasOne(Maintainer::class, 'user_id', 'id');
    }

    /**
     * The owner's permanent Vacancy Storefront share token (lazily minted on first use, like
     * Tenant::ensurePayToken). The URL /vacancies/o/{token} is stable forever — post the QR once
     * and it always renders the owner's LIVE vacancy state. Degrades gracefully if the column
     * hasn't been migrated yet (returns null; the caller shows an "apply the migration" notice).
     */
    public function ensureStorefrontToken(): ?string
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'storefront_token')) {
            return null;
        }

        if (empty($this->storefront_token)) {
            // Loop-guarded against the (astronomically unlikely) unique collision.
            do {
                $candidate = \Illuminate\Support\Str::random(40);
            } while (self::where('storefront_token', $candidate)->exists());

            $this->storefront_token = $candidate;
            $this->save();
        }

        return $this->storefront_token;
    }


    public function affiliate()
    {
        return $this->hasOne(Affiliate::class, 'user_id', 'id');
    }
 
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getImageAttribute(): string
    {
        if ($this->fileAttach) {
            return $this->fileAttach->FileUrl;
        }
        return asset('assets/images/no-image.jpg');
    }

    public function fileAttach()
    {
        return $this->morphOne(FileManager::class, 'origin');
    }

    public function ownerWallet()
    {
        return $this->hasOne(\App\Models\OwnerWallet::class, 'user_id');
    }
}
