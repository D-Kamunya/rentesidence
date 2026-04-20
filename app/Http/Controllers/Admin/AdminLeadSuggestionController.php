<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadSuggestion;
use App\Models\Lead;
use Illuminate\Http\Request;
use App\Services\LeadSuggestionService;

class AdminLeadSuggestionController extends Controller
{

    public function index()
    {
        $suggestions = LeadSuggestion::with([
            'lead.company',
            'lead.affiliate'
        ])
        ->whereHas('lead', function ($q) {
            $q->whereNotNull('affiliate_id');
        })
        ->latest()
        ->paginate(10);

        return view('admin.affiliates.suggestions.index', compact('suggestions'));
    }

    public function lead($leadId)
    {
        $relevantActivities = [
            'Sent WhatsApp message',
                    'Called lead',
                    'Sent email'
        ];

        $lead = Lead::with([
            'company',
            'affiliate',  
            'activities' => function ($query) use ($relevantActivities) {
                $query->whereIn('description', $relevantActivities)
                      ->latest();
            }
        ])->findOrFail($leadId);

        $suggestions = LeadSuggestion::with([
            'executor'
        ])
        ->where('lead_id', $leadId)
        ->latest()
        ->get();

        return view('admin.affiliates.suggestions.lead', compact('lead', 'suggestions'));
    }
    
    public function generate()
    {
        \Artisan::call('leads:generate-suggestions');

        return back()->with('success', 'Suggestions generated');
    }

    public function destroy($id)
    {
        LeadSuggestion::findOrFail($id)->delete();

        return back()->with('success', 'Suggestion deleted');
    }
}