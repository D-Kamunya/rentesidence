<?php
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class ProductCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'base_commission',
        'affiliate_commission',
        'status',
    ];
 
    protected $casts = [
        'base_commission' => 'float',
        'status'          => 'integer',
    ];
 
    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }
 
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
 
    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }
 
    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }
}