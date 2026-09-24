<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A user's completion of a one-time onboarding tour (keyed by tour id). */
class UserTour extends Model
{
    protected $fillable = ['user_id', 'tour_key', 'completed_at'];

    protected $casts = ['completed_at' => 'datetime'];
}
