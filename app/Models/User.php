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
     * back to the former owner. NOTE: future self-registered Helper users (no tenancy row at all)
     * will also be ownerless — extend this to a role check when self-registration lands.
     */
    public function isOwnerlessTenant(): bool
    {
        $tenant = $this->tenant;
        return $tenant && (int) $tenant->status === TENANT_STATUS_CLOSE;
    }

    public function maintainer(): HasOne
    {
        return $this->hasOne(Maintainer::class, 'user_id', 'id');
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
