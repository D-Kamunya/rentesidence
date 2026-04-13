<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function store(Request $request)
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

        DB::beginTransaction();

        try {
            $normalized = $this->normalizeCompanyName($request->company_name);

            $company = Company::where(function($q) use ($normalized, $request) {
                $q->where(function($q2) use ($normalized, $request) {
                    $q2->where('normalized_name', $normalized)
                    ->where('city', $request->city)
                    ->where('country', $request->country);
                })
                ->orWhere('phone', $request->phone);
            })->first();

            if (!$company) {
                $company = Company::create([
                    'company_name'     => $request->company_name,
                    'normalized_name'  => $normalized,
                    'country'          => $request->country,
                    'city'             => $request->city,
                    'phone'            => $request->phone,
                    'estimated_units'  => $request->estimated_units,
                    'email'            => $request->email,
                    'website'          => $request->website,
                    'property_type'    => $request->property_type
                ]);
            }

            $existingLead = Lead::where('company_id', $company->id)
                ->whereIn('status', ['active', 'pending_conversion', 'trial'])
                ->where('ownership_expires_at', '>', now())
                ->first();

            if ($existingLead) {
                // Throwing an exception ensures rollback
                throw new \Exception('This company already has an active lead.');
            }

            Lead::create([
                'company_id'          => $company->id,
                'affiliate_id'        => auth()->id(),
                'contact_person_name' => $request->contact_person_name,
                'contact_person_role' => $request->contact_person_role,
                'temperature'         => $request->temperature ?? 'cold',
                'status'              => 'active',
            ]);

            DB::commit();

            return redirect()->route('affiliate.leads')
                ->with('success', 'Lead submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit(Lead $lead)
    {
        if($lead->affiliate_id !== auth()->id()){
            abort(403);
        }

        return view('affiliate.leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        if($lead->affiliate_id !== auth()->id()){
            abort(403);
        }

        $request->validate([
            'contact_person_name' => 'required|string|max:255',
            'contact_person_role' => 'required',
            'estimated_units' => 'nullable|integer',
            'email' => 'nullable|email',
            'website'  => 'nullable|url',
            'property_type' => 'nullable',
        ]);

        $lead->update([
            'contact_person_name' => $request->contact_person_name,
            'contact_person_role' => $request->contact_person_role,
            'email' => $request->email,
            'website' => $request->website,
            'property_type' => $request->property_type
        ]);

        $data = $request->only([
            'estimated_units',
            'website',
            'email',
            'property_type',
        ]);
        
        // Remove null/empty values so you don’t overwrite with blanks
        $data = array_filter($data, fn($value) => !is_null($value) && $value !== '');
        
        // Update once if there’s anything to update
        if (!empty($data)) {
            $lead->company->update($data);
        }
        
        return redirect()->route('affiliate.leads')
            ->with('success','Lead updated successfully.');
    }

    public function show($id)
    {

        $lead = Lead::with([
            'company',
            'affiliate',
            'activities' => fn($q) => $q->orderBy('created_at', 'desc'),
            'suggestions' => fn($q) => $q->where('status', 'pending')
                                        ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
        ])->findOrFail($id);

        // Pre-load all templates needed for this lead's suggestion categories
        // keyed by category so the blade can look them up without querying
        $suggestionCategories = $lead->suggestions->pluck('category')->unique();
        $templatesByCategory  = ActionTemplate::whereIn('category', $suggestionCategories)
            ->get()
            ->groupBy('category');

        $suggestions = $lead->suggestions; // already loaded above
        $completeness = $this->completenessScore($lead);

        return view('affiliate.leads.show', compact('lead', 'suggestions', 'templatesByCategory', 'completeness'));
    }

    public function renew(Lead $lead)
    {
        if ($lead->affiliate_id !== auth()->id()) {
            abort(403);
        }
    
        if (! $lead->renew()) {
            return back()->with('error', 'Lead cannot be renewed.');
        }
    
        return back()->with('success', 'Lead renewed successfully.');
    }

    public function addNote(request $request, Lead $lead)
    {
        $request->validate([
            'note' => 'required|string|max:2000'
        ]);

        $newNote = "[".now()->format('Y-m-d H:i')."] ".$request->note;

        $updatedNotes = $lead->notes
            ? $lead->notes."\n\n".$newNote
            : $newNote;

        $lead->update([
            'notes' => $updatedNotes,
            'last_activity_at' => now()
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note_added',
            'description' => $request->note
        ]);

        return back()->with('success','Note added');
    }

    public function updateTemperature(Request $request, Lead $lead)
    {
        $request->validate([
            'temperature' => 'required|in:cold,warm,hot'
        ]);
    
        $lead->update([
            'temperature' => $request->temperature,
            'last_activity_at' => now()
        ]);
    
        LeadActivity::create([
            'lead_id'       => $lead->id,
            'user_id'       => auth()->id(),
            'type' => 'temperature_update',
            'description'   => 'Temperature set to ' . $request->temperature
        ]);
    
        return back()->with('success', 'Temperature updated to ' . ucfirst($request->temperature) . '.');
    }

    public function scheduleDemo(Request $request, Lead $lead)
    {
        $request->validate([
            'demo_date' => 'required|date'
        ]);

        $lead->update([
            'status' => 'demo_scheduled',
            'demo_scheduled_at' => $request->demo_date,
            'last_activity_at' => now(),
        ]);

        $lead->company->update([
            'sales_status' => 'contacted',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'demo_scheduled',
            'description' => 'Demo scheduled for ' . $request->demo_date
        ]);

        SendDemoScheduledMail::dispatch(
            $lead->id,
            \Carbon\Carbon::parse($request->demo_date)->format('l, F j, Y \a\t g:i A')
        );

        return back()->with('success', 'Demo scheduled and confirmation sent to ' . $lead->company->company_name . '.');
    }

    public function demoCompleted(Lead $lead)
    {
        $lead->update([
            'status' => 'demo_completed',
            'last_activity_at' => now()
        ]);

        $lead->company->update([
            'sales_status' => 'demo_done',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'demo_completed',
            'description' => 'Demo completed'
        ]);

        SendDemoCompletedMail::dispatch($lead->id);

        return back()->with('success', 'Demo marked complete and follow-up email sent to ' . $lead->company->company_name . '.');
    }

    public function requestTrial(Request $request, Lead $lead)
    {
        // Get the latest trial-related activity
        $latestTrialActivity = $lead->activities()
            ->whereIn('type', ['trial_requested', 'trial_extension', 'trial_expired', 'conversion_rejected'])
            ->orderByDesc('created_at')
            ->first();

        $isTrialExtension = $latestTrialActivity && $latestTrialActivity->type === 'trial_expired';

        // Validate reason only if this is an extension
        if ($isTrialExtension) {
            $request->validate([
                'extension_reason' => 'required|string|max:500'
            ]);
        }

        // Only allow conversion if lead is in the right status
        if (!in_array($lead->status, ['demo_completed', 'pending_conversion'])) {
            return back()->with('error', 'This lead is not ready for conversion request.');
        }

        // Update lead status and last activity timestamp
        $lead->update([
            'status' => 'pending_conversion',
            'last_activity_at' => now(),
        ]);

        // Decide activity type, description, and message
        if ($isTrialExtension) {
            $activityType = 'trial_extension';
            $description  = 'Trial extension requested by affiliate. Reason: ' . $request->extension_reason;
            $message      = 'Trial extension request submitted successfully! Admin will review your request.';
        } else {
            $activityType = 'trial_requested';
            $description  = 'Trial approval requested by affiliate';
            $message      = 'Trial request submitted successfully!';
        }

        // Record activity
        LeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => auth()->id(),
            'type'        => $activityType,
            'description' => $description,
        ]);

        // Notify admin
        $affiliate = auth()->user();
        SendTrialRequestedMail::dispatch(
            $lead->id,
            $isTrialExtension,
            $request->extension_reason ?? '',
            $affiliate->first_name . ' ' . $affiliate->last_name,
            $affiliate->email,
        );
        return back()->with('success', $message);
    }

    public function reject(Request $request, Lead $lead)
    {
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

        $description = ' Reason: ' . $reasonLabels[$request->rejection_reason];

        $lead->update([
            'status' => 'rejected',
            'last_activity_at' => now(),
        ]);

        $lead->company->update([
            'sales_status' => 'inactive',
        ]);

        LeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => auth()->id(),
            'type'        => 'lead_rejected',
            'description' => 'Rejected - ' . $description,
        ]);

        return back()->with('success', 'Lead Marked as Rejected');
    }

    public function lost(Lead $lead)
    {
        $lead->update([
            'status'=>'lost',
            'last_activity_at'=>now()
        ]);

        $lead->company->update([
            'sales_status' => 'inactive',
        ]);

        LeadActivity::create([
            'lead_id'=>$lead->id,
            'user_id'=>auth()->id(),
            'type'=>'lead_lost',
            'description'=>'Lead marked lost'
        ]);

        return back()->with('success','Lead Marked as Lost');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────
 
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
