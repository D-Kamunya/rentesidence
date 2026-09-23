<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An owner's reusable agreement template. Seeded with a plug-and-play default; the owner
 * edits or uploads their own and keeps reusing it until they change it.
 */
class AgreementTemplate extends Model
{
    use HasFactory, SoftDeletes;

    public const SOURCE_TEMPLATE = 'template';
    public const SOURCE_UPLOAD   = 'upload';

    protected $fillable = [
        'owner_user_id', 'name', 'source', 'body', 'original_file_id', 'is_default', 'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function originalFile()
    {
        return $this->belongsTo(FileManager::class, 'original_file_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
