<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Which products an affiliate works (Affiliate OS WP-A). Keyed on affiliates.id.
 * See docs/affiliate-os-design.md.
 */
class AffiliateProduct extends Model
{
    protected $fillable = ['affiliate_id', 'product', 'joined_at'];

    protected $casts = ['joined_at' => 'datetime'];
}
