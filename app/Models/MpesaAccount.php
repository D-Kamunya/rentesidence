<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MpesaAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_type',
        'gateway_id',
        'owner_user_id',
        'paybill',
        'till_number',
        'status',
        'account_name',
        'passkey',
    ];
}
