<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'normalized_name',
        'country',
        'city',
        'phone',
        'email',
        'website',
        'estimated_units',
        'property_type',
        'sales_status'
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
