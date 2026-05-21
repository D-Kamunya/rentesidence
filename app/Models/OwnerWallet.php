<?php
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class OwnerWallet extends Model
{
    protected $fillable = ['user_id', 'balance'];
 
    protected $casts = [
        'balance' => 'float',
    ];
 
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'owner_wallet_id');
    }
 
    /**
     * Get or create wallet for a given user_id.
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0.00]
        );
    }
}