<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LeadSuggestion extends Model
    {
        use HasFactory;

        protected $table = 'lead_suggestions';
        
        protected $fillable = [
            'lead_id',
            'affiliate_id',
            'message',
            'action_type',
            'category',
            'priority',
            'status',
            'executed_at',
            'execution_type',
            'expires_at'
        ];

        protected $casts = [
            'executed_at' => 'datetime',
        ];

        public function executor()
        {
            return $this->belongsTo(User::class, 'executed_by');
        }

        public function lead()
        {
            return $this->belongsTo(Lead::class);
        }

        public function activities()
        {
            return $this->hasMany(LeadActivity::class, 'suggestion_id');
        }
    }
