<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawal extends Model
{
    use HasFactory;
    protected $fillable = [
        'affiliate_id', 'amount', 'status', 'phone',
        'mpesa_reference', 'transaction_id', 'processed_at',
        'settlement_method', 'notes',
    ];

    protected $casts = [
        'processed_at' => 'datetime', 
    ];
    
    public function affiliate()
    {
        return $this->belongsTo(\App\Models\Affiliate::class, 'affiliate_id');
    }
}
