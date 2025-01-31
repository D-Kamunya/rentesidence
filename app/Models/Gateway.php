<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gateway extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'slug', 'status', 'mode', 'url', 'key', 'secret','image'];

    public function getIconAttribute(): string
    {
        return asset($this->image);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'owner_user_id');
    }
}
