<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceRecurringSetting extends Model
{
    use HasFactory, SoftDeletes;

    public function propertyUnit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceRecurringSettingItem::class, 'invoice_recurring_setting_id', 'id');
    }
}
