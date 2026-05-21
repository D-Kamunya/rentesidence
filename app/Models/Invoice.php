<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    // ── Scopes ────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->whereStatus(INVOICE_STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->whereStatus(INVOICE_STATUS_PAID);
    }

    public function scopeOverDue($query)
    {
        return $query->where('invoices.status', INVOICE_STATUS_PENDING)
                ->whereDate('invoices.due_date', '<', now());
    }

    public function getItemTypesLabelAttribute()
    {
        if (!$this->relationLoaded('invoiceItems')) {
            $this->load('invoiceItems.invoiceType');
        }
        
        if ($this->invoiceItems->isEmpty()) {
            return null;
        }
        
        return $this->invoiceItems
            ->pluck('invoiceType.name')
            ->filter()
            ->unique()
            ->implode(', ');
    }

    // ── Relationships ─────────────────────────────────────────

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function propertyUnit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * All invoice types attached to this invoice through its items.
     * Used for checking e.g. whether a rent type exists on this invoice.
     */
    public function invoiceTypes(): HasManyThrough
    {
        return $this->hasManyThrough(
            InvoiceType::class,
            InvoiceItem::class,
            'invoice_id',       // FK on invoice_items
            'id',               // FK on invoice_types
            'id',               // local key on invoices
            'invoice_type_id'   // local key on invoice_items
        );
    }

    /**
     * The order associated with this invoice (any payment status).
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Only the successfully paid order for this invoice.
     */
    public function paidOrder(): HasOne
    {
        return $this->hasOne(Order::class)
            ->where('payment_status', INVOICE_STATUS_PAID)
            ->latest();
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Check whether this invoice has a Rent item (case-insensitive).
     */
    public function hasRentItem(): bool
    {
        return $this->invoiceItems()
            ->whereHas('invoiceType', fn($q) => $q->whereRaw('LOWER(name) = ?', ['rent']))
            ->exists();
    }

    // ── Boot ──────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            $model->invoice_no = $model->name . '-' . sprintf("%'.08d", $model->id);
            $model->save();
        });
    }
}