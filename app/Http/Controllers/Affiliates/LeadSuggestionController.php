<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadSuggestion;

class LeadSuggestionController extends Controller
{
    /**
     * Fetch suggestions for a specific lead (used in lead page)
     */
    public function leadSuggestions($leadId)
    {
        $suggestions = LeadSuggestion::where('lead_id', $leadId)
            ->where('status', 'pending')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->latest()
            ->get();

        return response()->json($suggestions);
    }

    /**
     * Fetch suggestions for logged-in affiliate (dashboard)
     */
    public function mySuggestions()
    {
        $suggestions = LeadSuggestion::where('affiliate_id', auth()->user()->affiliate->id)
            ->where('status', 'pending')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->latest()
            ->get();

        return view('affiliate.suggestions.index', compact('suggestions'));
    }
    

    /**
     * Mark suggestion as completed manually
     */
    public function complete($id)
    {
        $suggestion = LeadSuggestion::findOrFail($id);

        $suggestion->update([
            'status' => 'completed',
            'executed_at' => now(),
            'execution_type' => 'manual',
            'executed_by' => auth()->id()
        ]);

        return back()->with('success', 'Suggestion marked as completed');
    }

    /**
     * Dismiss suggestion
     */
    public function dismiss($id)
    {
        $suggestion = LeadSuggestion::findOrFail($id);

        $suggestion->update([
            'status' => 'dismissed',
            'executed_by' => auth()->id()
        ]);

        return back()->with('success', 'Suggestion dismissed');
    }
}