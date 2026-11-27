<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A prospect captured off the Vacancy Storefront's "notify me" funnel — the empty-state
 * capture and the opt-in House Watch area alert. Degrades gracefully if the table hasn't
 * been migrated yet (see the controller's hasTable guard).
 */
class VacancyNotifyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'contact_number',
        'scope_type',
        'scope_owner_user_id',
        'scope_property_id',
        'area_city',
        'area_state',
        'converted_user_id',
        'converted_at',
        'notified_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
        'notified_at'  => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'scope_property_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'scope_owner_user_id');
    }
}
