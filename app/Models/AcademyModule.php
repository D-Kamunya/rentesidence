<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyModule extends Model
{
    protected $fillable = ['title', 'youtube_url', 'duration_minutes', 'content','module_order','is_active'];

    public function questions()
    {
        return $this->hasMany(AcademyQuestion::class, 'module_id')->orderBy('question_order');
    }

}