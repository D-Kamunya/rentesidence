<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyQuestion extends Model
{
    protected $fillable = ['module_id','question','question_order'];

    public function module()
    {
        return $this->belongsTo(AcademyModule::class, 'module_id');
    }
    
    public function options()
    {
        return $this->hasMany(AcademyOption::class, 'question_id');
    }
}