<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'action_type',
        'category',
        'message_template',
        'material_ids'
    ];

    protected $casts = [
        'material_ids' => 'array',
    ];

    public function materials()
    {
        return $this->belongsToMany(MarketingMaterial::class);
    }
}


