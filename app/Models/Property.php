<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    public function scopeActive($query)
    {
        return $query->whereStatus(ACTIVE);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'property_id', 'id');
    }

    public function maintainers()
    {
        return $this->hasMany(Maintainer::class, 'property_id', 'id');
    }

    public function propertyDetail(): HasOne
    {
        return $this->hasOne(PropertyDetail::class);
    }

    public function propertyImages(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function propertyUnits(): HasMany
    {
        return $this->hasMany(PropertyUnit::class);
    }

    public function getThumbnailImageAttribute()
    {
        if ($this->fileAttachThumbnail) {
            return $this->fileAttachThumbnail->FileUrl;
        }
        return asset('assets/images/no-image.jpg');
    }

    public function fileAttachThumbnail()
    {
        return $this->hasOne(FileManager::class, 'id', 'thumbnail_image_id')->select('id', 'folder_name', 'file_name', 'origin_type', 'origin_id');
    }

    protected static function booted()
    {
        static::deleting(function ($property) {
            // Load units including trashed ones, and eager load images
            $units = $property->propertyUnits()->withTrashed()->with('images')->get();

            foreach ($units as $unit) {
                // Delete all images for the unit (file + DB record)
                if ($unit->images && $unit->images->count()) {
                    foreach ($unit->images as $image) {
                        // delete file (safe delete)
                        $path = $image->folder_name . '/' . $image->file_name;
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }

                        // force delete the image record (permanent)
                        if (method_exists($image, 'forceDelete')) {
                            $image->forceDelete();
                        } else {
                            // fallback to delete if model doesn't support forceDelete
                            $image->delete();
                        }
                    }
                }

                // Keep the units soft-deleted (if not already trashed)
                if (! $unit->trashed()) {
                    $unit->delete(); // soft delete unit (preserves your current behavior)
                }
            }
        });
    }
}
