<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A document a partner requires for a product (handbook §9.3.3).
 */
class ApplicationDocumentRequirement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function partnerModule(): BelongsTo
    {
        return $this->belongsTo(FinancePartnerModule::class, 'finance_partner_module_id');
    }
}
