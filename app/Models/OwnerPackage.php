<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class OwnerPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'package_id',
        'name',
        'max_maintainer',
        'max_property',
        'max_unit',
        'max_tenant',
        'max_invoice',
        'max_auto_invoice',
        'ticket_support',
        'notice_support',
        'monthly_price',
        'yearly_price',
        'order_id',
        'is_trail',
        'start_date',
        'end_date',
        'status',
        'package_type',
        'quantity',
        'per_monthly_price',
        'per_yearly_price',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
