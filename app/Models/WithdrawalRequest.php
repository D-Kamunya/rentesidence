<?php
// =====================================================================
// MODEL: App\Models\WithdrawalRequest
// =====================================================================
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class WithdrawalRequest extends Model
{
    protected $fillable = [
        'owner_wallet_id',
        'amount',
        'phone',
        'status',        // pending | approved | rejected
        'processed_at',
        'mpesa_reference', // populated after B2C success
    ];
 
    protected $casts = [
        'amount'       => 'float',
        'processed_at' => 'datetime',
    ];
 
    public function wallet()
    {
        return $this->belongsTo(OwnerWallet::class, 'owner_wallet_id');
    }
}