<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Company;
use App\Models\LeadActivity;
use Illuminate\Support\Facades\DB;

class AdminMarketplaceController extends Controller
{
    /**
     * Normalize a company name for duplicate detection.
     * Mirrors the same logic in the affiliate LeadController.
     */
    private function normalizeCompanyName(string $name): string
    {
        $remove = ['limited', 'ltd', 'apartments', 'apartment', 'estate', 'properties', 'realestate'];

        $name  = strtolower($name);
        $words = preg_split('/\s+/', $name);
        $words = array_diff($words, $remove);
        $normalized = implode(' ', $words);
        $normalized = preg_replace('/[^a-z0-9 ]/', '', $normalized);

        return trim($normalized);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/marketplace
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Lead::with(['company', 'affiliate'])
            ->whereNotNull('marketplace_status'); // only marketplace-sourced leads

        // Search by company name or country
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter by marketplace status (available / claimed)
        if ($request->filled('status')) {
            $query->where('marketplace_status', $request->status);
        }

        // Filter by temperature
        if ($request->filled('temperature')) {
            $query->where('temperature', $request->temperature);
        }

        $leads = $query->latest('marketplace_at')->paginate(15)->withQueryString();

        // ── Summary stats ─────────────────────────────────────────────────────
        $totalMarketplace = Lead::where('marketplace_status', 'marketplace')->count();
        $totalClaimed     = Lead::where('marketplace_status', 'claimed')->count();
        $claimedThisMonth = Lead::where('marketplace_status', 'claimed')
            ->whereMonth('claimed_at', now()->month)
            ->whereYear('claimed_at', now()->year)
            ->count();

        // Average completeness across all marketplace leads (available only)
        $avgCompleteness = $this->averageCompleteness();
        return view('admin.affiliates.leads.marketplace.index', compact(
            'leads',
            'totalMarketplace',
            'totalClaimed',
            'claimedThisMonth',
            'avgCompleteness'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/marketplace/create
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        return view('admin.affiliates.leads.marketplace.create');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/marketplace
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'company_name'        => 'required|string|max:255',
            'country'             => 'required|string|max:100',
            'city'                => 'nullable|string|max:100',
            'phone'               => 'required|string|max:20',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_role' => 'required|string|max:100',
            'email'               => 'nullable|email',
            'website'             => 'nullable|url',
            'property_type'       => 'nullable|string',
            'estimated_units'     => 'nullable|integer|min:1',
            'temperature'         => 'nullable|in:cold,warm,hot',
            'admin_notes'         => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $normalized = $this->normalizeCompanyName($request->company_name);

            // Find or create company — same deduplication logic as affiliate LeadController
            $company = Company::where(function ($q) use ($normalized, $request) {
                $q->where(function ($q2) use ($normalized, $request) {
                    $q2->where('normalized_name', $normalized)
                       ->where('city', $request->city)
                       ->where('country', $request->country);
                })->orWhere('phone', $request->phone);
            })->first();

            if (!$company) {
                $company = Company::create([
                    'company_name'    => $request->company_name,
                    'normalized_name' => $normalized,
                    'country'         => $request->country,
                    'city'            => $request->city,
                    'phone'           => $request->phone,
                    'email'           => $request->email,
                    'website'         => $request->website,
                    'property_type'   => $request->property_type,
                    'estimated_units' => $request->estimated_units,
                ]);
            } else {
                // Update any newly provided fields on the existing company
                $updates = array_filter([
                    'email'           => $request->email,
                    'website'         => $request->website,
                    'property_type'   => $request->property_type,
                    'estimated_units' => $request->estimated_units,
                ], fn($v) => !is_null($v) && $v !== '');

                if (!empty($updates)) {
                    $company->update($updates);
                }
            }

            // Guard: don't create a duplicate marketplace lead for the same company
            $existingMarketplace = Lead::where('company_id', $company->id)
                ->where('marketplace_status', 'marketplace')
                ->first();

            if ($existingMarketplace) {
                DB::rollBack();
                return back()->with('error', 'This company already has an active listing in the marketplace.');
            }

            // Create the marketplace lead — no affiliate, no expiry clock yet
            $lead = Lead::create([
                'company_id'           => $company->id,
                'affiliate_id'         => null,
                'contact_person_name'  => $request->contact_person_name,
                'contact_person_role'  => $request->contact_person_role,
                'temperature'          => $request->temperature ?? 'cold',
                'status'               => 'active',
                'source'               => 'admin',
                'marketplace_status'   => 'marketplace',
                'marketplace_at'       => now(),
                'ownership_expires_at' => null, // set only when claimed
                'notes'                => $request->admin_notes,
            ]);

            // Log the creation as an admin activity (user_id = admin's id)
            LeadActivity::create([
                'lead_id'     => $lead->id,
                'user_id'     => auth()->id(),
                'type'        => 'lead_created',
                'description' => 'Lead added to marketplace by admin.',
            ]);

            DB::commit();

            return redirect()->route('admin.marketplace.index')
                ->with('success', 'Lead published to the marketplace successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/marketplace/{lead}
    // ─────────────────────────────────────────────────────────────────────────
    public function show(Lead $lead)
    {
        $lead->load([
            'company',
            'affiliate',
            'activities' => fn($q) => $q->latest(),
        ]);

        // Completeness score for this lead
        $completeness = $this->completenessScore($lead);

        return view('admin.affiliates.leads.marketplace.show', compact('lead', 'completeness'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /admin/marketplace/{lead}
    // Pull a lead back out of the marketplace (only while unclaimed)
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(Lead $lead)
    {
        if ($lead->marketplace_status !== 'marketplace') {
            return back()->with('error', 'This lead has already been claimed and cannot be pulled.');
        }

        // Soft-remove from marketplace — nullify marketplace columns but keep the record
        $lead->update([
            'marketplace_status' => null,
            'marketplace_at'     => null,
            'status'             => 'active', // neutral state
        ]);

        LeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => auth()->id(),
            'type'        => 'lead_pulled',
            'description' => 'Lead removed from marketplace by admin.',
        ]);

        return back()->with('success', 'Lead removed from the marketplace.');
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
 
    /**
     * Average completeness score across all currently available marketplace leads.
     */
    private function averageCompleteness(): int
    {
        $leads = Lead::with('company')
            ->where('marketplace_status', 'marketplace')
            ->get();
 
        if ($leads->isEmpty()) {
            return 0;
        }
 
        $total = $leads->sum(fn($lead) => $this->completenessScore($lead));
 
        return (int) round($total / $leads->count());
    }
}