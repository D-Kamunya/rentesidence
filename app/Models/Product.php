<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id', 'name', 'description', 'price', 'category', 'type', 'image',
    ];

    public function owner() {
        return $this->belongsTo(Owner::class);
    }
}
