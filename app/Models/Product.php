<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id', 'name', 'description', 'price', 'category', 'type', 'product_category_id', 'images',
    ];

    public function owner() {
        return $this->belongsTo(Owner::class);
    }
    
    public function orderItems()
    {
        return $this->hasMany(\App\Models\ProductOrderItem::class, 'product_id');
    }
    
    protected $casts = [
        'image' => 'array', 
    ];

    public function getFirstImageUrlAttribute()
    {
        if (!$this->images) {
            return null;
        }

        $images = json_decode($this->images, true);

        if (!empty($images) && is_array($images)) {
            $path = ltrim($images[0], '/'); // first image
            return asset('storage/' . $path);
        }

        return null;
    }


    public function productCategory()
    {
        return $this->belongsTo(\App\Models\ProductCategory::class, 'product_category_id');
    }
    
}
