<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payment_check';

    protected $fillable = [
        'subscription_payment_id',
        'invoice_payment_id',
        'check_count',
        'last_check_at',
    ];

    protected $casts = [
        'last_check_at' => 'datetime',
    ];

    // Define any relationships if needed
}
