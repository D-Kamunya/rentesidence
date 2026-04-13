<?php

namespace App\Services;

use App\Models\LeadSuggestion;

class LeadSuggestionService
{
    public function createSuggestion($lead, $message, $actionType, $category, $priority, $expiresDays)
    {
        $exists = LeadSuggestion::where('lead_id', $lead->id)
            ->where('message', $message)
            ->where('status', 'pending')
            ->exists();

        if ($exists) return;

        LeadSuggestion::create([
            'lead_id' => $lead->id,
            'affiliate_id' => $lead->affiliate_id,
            'message' => $message,
            'action_type' => $actionType,
            'category' => $category,
            'priority' => $priority,
            'status' => 'pending',
            'expires_at' => now()->addDays($expiresDays)
        ]);
    }
}