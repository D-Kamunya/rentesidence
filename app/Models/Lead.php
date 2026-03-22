<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public const OWNERSHIP_DURATION_DAYS = 60;

    protected $fillable = [
        'company_id',
        'affiliate_id',
        'contact_person_name',
        'contact_person_role',
        'temperature',
        'status',
        'notes',
        'last_activity_at',
        'demo_scheduled_at',
        'converted_at'
    ];

    protected $casts = [
        'ownership_expires_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'demo_scheduled_at'    => 'datetime',
    ];
    

    // soon to replace this with cron job
    protected static function booted()
    {
        static::creating(function ($lead) {
            if (!$lead->ownership_expires_at) {
                $lead->ownership_expires_at = now()->addDays(self::OWNERSHIP_DURATION_DAYS);
            }
        });
        static::retrieved(function ($lead) {

            if (
                !$lead->isClosed() &&
                $lead->status !== 'expired' &&
                $lead->ownership_expires_at &&
                $lead->ownership_expires_at->isPast()
            ) {

                $lead->update([
                    'status' => 'expired'
                ]);

            }

        });
    }

    public function isClosed()
    {
        return in_array($this->status, [
            'converted',
            'rejected',
            'lost'
        ]);
    }

    // defines the activities to take note of so that activites like notes and temp changes dont break functionality
    public function latestTrialActivity()
    {
        return $this->activities
            ->whereIn('type', ['trial_request', 'trial_extention', 'trial_expired', 'conversion_rejected'])
            ->sortByDesc('created_at')
            ->first();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function renew(): bool
    {
        // Guard against closed leads
        if ($this->isClosed()) {
            return false;
        }
    
        // Guard against non-expired leads
        if ($this->ownership_expires_at > now()) {
            return false;
        }
    
        // Direct assignment instead of mass update
        $this->ownership_expires_at = now()->addDays(self::OWNERSHIP_DURATION_DAYS);
        $this->status = 'active';
    
        return $this->save();
    }

}
