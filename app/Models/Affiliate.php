<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_code',
    ];
    protected $table = 'affiliates';

    public function owners(): HasMany
    {
        return $this->hasMany(Owner::class);
    }

    public function academyProgress()
    {
        return $this->hasMany(AffiliateAcademyProgress::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'affiliate_id');
    }
    
    public function withdrawals()
    {
        return $this->hasMany(AffiliateWithdrawal::class, 'affiliate_id');
    }
}
