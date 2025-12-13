<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['image_url', 'thumbnail_url', 'single_image'];

    public function file()
    {
        return $this->belongsTo(FileManager::class, 'file_id', 'id');
    }
    public function thumbnail()
    {
        return $this->belongsTo(FileManager::class, 'thumbnail_id', 'id');
    }
    public function getImageUrlAttribute()
    {
        if ($this->file && $this->file->file_url) {
            return $this->file->file_url;
        }

        return asset('assets/images/no-image.jpg');
    }
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail && $this->thumbnail->file_url) {
            return $this->thumbnail->file_url;
        }

        return $this->image_url; // Fallback to main image
    }
    public function getSingleImageAttribute()
    {
        return $this->image_url;
    }
}
