<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyUnit extends Model
{
    use HasFactory, SoftDeletes;

    // public function propertyUnits(): HasMany
    // {
    //     return $this->hasMany(PropertyUnit::class);
    // }

    /**
     * Get the tenant that owns the PropertyUnit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activeTenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'unit_id', 'id')->where('status', TENANT_STATUS_ACTIVE);
    }

    public function images()
    {
        return $this->hasMany(PropertyUnitImage::class, 'unit_id');
    }

    public function getFirstImageUrlAttribute()
    {
        $firstImage = $this->images()->oldest()->first(); // get the first uploaded image
        return $firstImage
            ? asset('storage/' . $firstImage->folder_name . '/' . $firstImage->file_name)
            : asset('assets/images/default-unit.png'); // fallback if none
    }    

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    protected static function booted()
    {
        static::deleting(function ($unit) {
            // Force delete images when unit is deleted (soft or hard)
            foreach ($unit->images as $image) {
                $path = 'public/' . $image->folder_name . '/' . $image->file_name;
                if (\Storage::exists($path)) {
                    \Storage::delete($path);
                }
                $image->forceDelete(); // Force delete DB record
            }
        });
    }
}
