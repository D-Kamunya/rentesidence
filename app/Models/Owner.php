<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function package()
    {
        return $this->hasOne(OwnerPackage::class, 'user_id');
    }

    public function activePackage()
    {
        return $this->package()->where('status', 'active')->first();
    }

    /**
     * Get all commissions earned by affiliates from this owner.
     */
    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'owner_id');
    }
    
    public function products()
    {
        return $this->hasMany(Product::class, 'owner_user_id', 'id');
    }

}
