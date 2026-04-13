<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MarketingMaterial extends Model
{
    use HasFactory;

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
    
    protected $fillable = [
        'title',
        'type',
        'content',
        'category',
        'priority',
        'usage_count',
        'is_active',
        'file_path',
        'file_name'
    ];

    public function templates()
    {
        return $this->belongsToMany(ActionTemplate::class);
    }

}