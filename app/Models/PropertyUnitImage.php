<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PropertyUnitImage extends Model
{
    use HasFactory;

    protected $fillable = ['unit_id', 'folder_name', 'file_name'];

    // Delete image from storage when record is deleted
    protected static function booted()
    {
        static::deleting(function ($image) {
            $path = "public/{$image->folder_name}/{$image->file_name}";
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        });
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->folder_name . '/' . $this->file_name);
    }

    public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }
}
