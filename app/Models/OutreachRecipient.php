<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachRecipient extends Model
{
    protected $fillable = [
        'campaign_id', 'email', 'first_name', 'status',
        'sent_at', 'follow_up_count', 'last_follow_up_at', 'converted_at', 'error',
    ];

    protected $casts = [
        'sent_at'           => 'datetime',
        'last_follow_up_at' => 'datetime',
        'converted_at'      => 'datetime',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_SENT      = 'sent';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_FAILED    = 'failed';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(OutreachCampaign::class, 'campaign_id');
    }
}
