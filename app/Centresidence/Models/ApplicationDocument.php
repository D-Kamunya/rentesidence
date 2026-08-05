<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A document uploaded for a specific application (handbook §9.3.3).
 */
class ApplicationDocument extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'verified'    => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FinanceApplication::class, 'finance_application_id');
    }
}
