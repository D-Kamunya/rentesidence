<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUnitTransfer extends Model
{
    protected $fillable = [
        'tenant_id', 'property_id', 'from_unit_id', 'to_unit_id',
        'outstanding_snapshot', 'note', 'transferred_by',
    ];

    public function fromUnit()
    {
        return $this->belongsTo(PropertyUnit::class, 'from_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(PropertyUnit::class, 'to_unit_id');
    }
}
