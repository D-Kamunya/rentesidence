<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class, 'id', 'listing_id');
    }

    /**
     * The user who owns this sale listing. Wired to `listings.owner_user_id`.
     * (The house-hunt index reads `$sale->owner->name`; without this relation it
     * silently resolved to null, so sale cards showed no owner.)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
