<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseHuntApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_unit_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'job',
        'age',
        'contact_number',
        'family_member',
        'permanent_address',
        'permanent_country_id',
        'permanent_state_id',
        'permanent_city_id',
        'permanent_zip_code',
        'status',
    ];

     public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'status' => 'integer',
        'age' => 'integer',
        'family_member' => 'integer',
    ];
}
