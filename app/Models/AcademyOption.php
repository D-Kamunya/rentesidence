<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyOption extends Model
{
    protected $fillable = ['question_id','option_text','is_correct'];

    public function questions()
    {
        return $this->belongsTo(AcademyQuestion::class, 'question_id');
    }
}