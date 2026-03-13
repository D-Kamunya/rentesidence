<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateAcademyProgress extends Model
{
    protected $fillable = ['affiliate_id','module_id','attempts','score','needs_review','completed_at'];

    public function module()
    {
        return $this->belongsTo(AcademyModule::class, 'module_id');
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}