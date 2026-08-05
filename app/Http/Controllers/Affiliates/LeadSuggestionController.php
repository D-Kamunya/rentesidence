<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadSuggestion;
use App\Services\SuggestionService;

class LeadSuggestionController extends Controller
{
    public function __construct(protected SuggestionService $suggestions) {}

    /**
     * Fetch suggestions for a specific lead (used in lead page)
     */
    public function leadSuggestions($leadId)
    {
        $suggestions = LeadSuggestion::where('lead_id', $leadId)
            ->where('affiliate_id', auth()->id()) // scope to the caller's own leads (no cross-affiliate leakage)
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
        // suggestions.affiliate_id holds the owning USER id (see claim() + every
        // other read here), NOT the Affiliate row PK — scope by auth()->id() so an
        // affiliate sees their OWN pending suggestions (previously keyed on the
        // Affiliate PK, which showed another user's suggestions / none).
        $suggestions = LeadSuggestion::where('affiliate_id', auth()->id())
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
        $this->authorizeSuggestion($suggestion);

        $this->suggestions->completeManually($suggestion);

        return back()->with('success', 'Suggestion marked as completed');
    }

    /**
     * Dismiss suggestion
     */
    public function dismiss($id)
    {
        $suggestion = LeadSuggestion::findOrFail($id);
        $this->authorizeSuggestion($suggestion);

        $this->suggestions->dismiss($suggestion);

        return back()->with('success', 'Suggestion dismissed');
    }

    /** An affiliate may only act on suggestions for their own leads (prevents IDOR). */
    private function authorizeSuggestion(LeadSuggestion $suggestion): void
    {
        if ((int) $suggestion->affiliate_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}