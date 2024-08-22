<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrders extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'unit_id', 'status'];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
}
