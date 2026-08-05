<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LeadService;
use App\Services\SuggestionService;
use App\Services\SmsMail\MailService;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Affiliate;
use App\Models\LeadSuggestion;
use App\Models\LeadActivity;
use App\Models\ActionTemplate;
use App\Jobs\Mail\SendDemoScheduledMail;
use App\Jobs\Mail\SendDemoCompletedMail;
use App\Jobs\Mail\SendTrialRequestedMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{

    public function index(Request $request)
    {
        $affiliateId = auth()->id();

        // Start query first
        $query = Lead::with('company', 'activities')
            ->where('affiliate_id', $affiliateId);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('company', function($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%");
                });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Temperature Filter
        if ($request->filled('temperature')) {
            $query->where('temperature', $request->temperature);
        }

        // Now paginate AFTER filters
        $leads = $query->latest()
            ->paginate(10)
            ->withQueryString();

        // Lead Summary Cards (unchanged)
        $leadSummary = Lead::where('affiliate_id', $affiliateId)
            ->whereNotIn('status', ['converted', 'rejected', 'lost'])
            ->selectRaw("
                SUM(CASE WHEN temperature = 'hot' THEN 1 ELSE 0 END) as hot,
                SUM(CASE WHEN temperature = 'warm' THEN 1 ELSE 0 END) as warm,
                SUM(CASE WHEN temperature = 'cold' THEN 1 ELSE 0 END) as cold,
                SUM(CASE WHEN ownership_expires_at < NOW() THEN 1 ELSE 0 END) as expired
            ")
            ->first();

        // Suggestion Counts (unchanged)
        $leadIds = $leads->pluck('id');
        $suggestionCounts = LeadSuggestion::where('status', 'pending')
            ->whereIn('lead_id', $leadIds)
            ->select(
                'lead_id',
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN priority = "high" THEN 1 ELSE 0 END) as urgent_count')
            )
            ->groupBy('lead_id')
            ->get()
            ->keyBy('lead_id');

        $totalSuggestions = $suggestionCounts->sum('total');
        $urgentSuggestions = $suggestionCounts->sum('urgent_count');
        $leadsWithSuggestions = $suggestionCounts->count();

        return view('affiliate.leads.index', compact(
            'leads',
            'leadSummary',
            'suggestionCounts',
            'totalSuggestions',
            'urgentSuggestions',
            'leadsWithSuggestions'
        ));
    }

    public function create()
    {
        return view('affiliate.leads.create');
    }

    private function normalizeCompanyName($name)
    {
        $remove = [
            'limited',
            'ltd',
            'apartments',
            'apartment',
            'estate',
            'properties',
            'realestate'
        ];
    
        // Lowercase
        $name = strtolower($name);
    
        // Split into words
        $words = preg_split('/\s+/', $name);
    
        // Remove unwanted words
        $words = array_diff($words, $remove);
    
        // Join back
        $normalized = implode(' ', $words);
    
        // Remove non-alphanumeric except spaces
        $normalized = preg_replace('/[^a-z0-9 ]/', '', $normalized);
    
        return trim($normalized);
    }  

    public function store(Request $request, LeadService $leads)
    {
        $request->validate([
            'company_name'          => 'required|string|max:255',
            'country'               => 'required|string|max:100',
            'city'                  => 'nullable|string|max:100',
            'phone'                 => 'required|string|max:20',
            'contact_person_name'   => 'required|string|max:255',
            'contact_person_role'   => 'required|string|max:100',
            'email'                 => 'nullable|email',
            'website'               => 'nullable|url',
            'property_type'         => 'nullable',
        ]);

        try {
            $leads->createLead($request->all(), (int) auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }

        return redirect()->route('affiliate.leads')
            ->with('success', 'Lead submitted successfully.');
    }

    public function edit(Lead $lead)
    {
        $this->authorizeOwnership($lead);

        return view('affiliate.leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $request->validate([
            'contact_person_name' => 'required|string|max:255',
            'contact_person_role' => 'required',
            'estimated_units' => 'nullable|integer',
            'email' => 'nullable|email',
            'website'  => 'nullable|url',
            'property_type' => 'nullable',
        ]);

        $leads->updateLead(
            $lead,
            $request->only(['contact_person_name', 'contact_person_role', 'email', 'website', 'property_type']),
            $request->only(['estimated_units', 'website', 'email', 'property_type'])
        );

        return redirect()->route('affiliate.leads')
            ->with('success','Lead updated successfully.');
    }

    public function show($id, SuggestionService $suggestionService)
    {

        $lead = Lead::with([
            'company',
            'affiliate',
            'activities' => fn($q) => $q->orderBy('created_at', 'desc'),
            'suggestions' => fn($q) => $q->where('status', 'pending')
                                        ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
        ])->findOrFail($id);

        $this->authorizeOwnership($lead);

        // Pre-load all templates needed for this lead's suggestion categories,
        // keyed by category, then let the service pre-split them per suggestion
        // into whatsapp/email/call groups + the engine-recommended channel — so
        // the blade just renders (no per-suggestion collection filtering there).
        $suggestionCategories = $lead->suggestions->pluck('category')->unique();
        $templatesByCategory  = ActionTemplate::whereIn('category', $suggestionCategories)
            ->get()
            ->groupBy('category');

        $suggestions     = $lead->suggestions; // already loaded above (used for header counts)
        $suggestionData  = collect($suggestionService->channelsFor($suggestions, $templatesByCategory));
        $canActOnLead    = (int) $lead->affiliate_id === (int) auth()->id();
        $completeness    = $this->completenessScore($lead);

        return view('affiliate.leads.show', compact('lead', 'suggestions', 'suggestionData', 'canActOnLead', 'completeness'));
    }

    public function renew(Lead $lead)
    {
        $this->authorizeOwnership($lead);

        if (! $lead->renew()) {
            return back()->with('error', 'Lead cannot be renewed.');
        }
    
        return back()->with('success', 'Lead renewed successfully.');
    }

    public function addNote(request $request, Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $request->validate([
            'note' => 'required|string|max:2000'
        ]);

        $leads->addNote($lead, $request->note);

        return back()->with('success','Note added');
    }

    public function updateTemperature(Request $request, Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $request->validate([
            'temperature' => 'required|in:cold,warm,hot'
        ]);

        $leads->setTemperature($lead, $request->temperature);

        return back()->with('success', 'Temperature updated to ' . ucfirst($request->temperature) . '.');
    }

    public function scheduleDemo(Request $request, Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $request->validate([
            'demo_date'         => 'required|date',
            'demo_meeting_link' => 'nullable|url|max:2048',
        ]);

        $leads->scheduleDemo($lead, $request->demo_date, $request->demo_meeting_link);

        return back()->with('success', 'Demo scheduled and confirmation sent to ' . $lead->company->company_name . '.');
    }

    public function demoCompleted(Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $leads->markDemoCompleted($lead);

        return back()->with('success', 'Demo marked complete and follow-up email sent to ' . $lead->company->company_name . '.');
    }

    public function requestTrial(Request $request, Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $isExtension = $leads->isTrialExtension($lead);

        // Validate the reason only when this is an extension request.
        if ($isExtension) {
            $request->validate([
                'extension_reason' => 'required|string|max:500'
            ]);
        }

        $result = $leads->submitTrialRequest($lead, $isExtension, $request->extension_reason, auth()->user());

        return $result['success']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }

    public function reject(Request $request, Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $request->validate([
            'rejection_reason'      => 'required|in:too_expensive,using_other_system,not_interested,no_response,timing_not_right,other',
            'rejection_reason_text' => 'required_if:rejection_reason,other|nullable|string|max:500',
        ]);

        $reasonLabels = [
            'too_expensive'      => 'Too Expensive',
            'using_other_system' => 'Using Another System',
            'not_interested'     => 'Not Interested',
            'no_response'        => 'No Response',
            'timing_not_right'   => 'Timing Not Right',
            'other'              => $request->rejection_reason_text,
        ];

        $leads->reject($lead, ' Reason: ' . $reasonLabels[$request->rejection_reason]);

        return back()->with('success', 'Lead Marked as Rejected');
    }

    public function lost(Lead $lead, LeadService $leads)
    {
        $this->authorizeOwnership($lead);

        $leads->markLost($lead);

        return back()->with('success','Lead Marked as Lost');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Guard every lead-scoped action: an affiliate may only view or mutate the
     * leads they own. Prevents IDOR — passing another affiliate's lead id in the
     * URL to read or tamper with a competitor's lead. (leads.affiliate_id stores
     * the owning user's id — see store().)
     */
    private function authorizeOwnership(Lead $lead): void
    {
        if ((int) $lead->affiliate_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    /**
     * Compute the completeness score (0–100) for a single lead.
     * Mirrors the blade-side calculation so both stay in sync.
     */
    private function completenessScore(Lead $lead): int
    {
        $fields = [
            $lead->company->company_name    ?? null,
            $lead->company->country         ?? null,
            $lead->company->city            ?? null,
            $lead->company->phone           ?? null,
            $lead->company->email           ?? null,
            $lead->company->website         ?? null,
            $lead->company->property_type   ?? null,
            $lead->company->estimated_units ?? null,
            $lead->contact_person_name      ?? null,
            $lead->contact_person_role      ?? null,
        ];
 
        $filled = count(array_filter($fields, fn($v) => !is_null($v) && $v !== ''));
        return (int) round(($filled / count($fields)) * 100);
    }
}
