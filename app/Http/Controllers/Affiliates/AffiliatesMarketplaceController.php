<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadActivity;

class AffiliatesMarketplaceController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /affiliate/marketplace
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Lead::with('company')
            ->where('marketplace_status', 'marketplace')
            ->whereNull('affiliate_id');

        // Filter by temperature
        if ($request->filled('temperature')) {
            $query->where('temperature', $request->temperature);
        }

        // Filter by property type (on the related company)
        if ($request->filled('property_type')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('property_type', $request->property_type);
            });
        }

        // Search by country or city
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Ordering: hot first, then warm, then cold; within each group oldest first
        // (oldest = waiting longest = should be claimed first)
        $leads = $query->orderByRaw("FIELD(temperature, 'hot', 'warm', 'cold')")
            ->orderBy('marketplace_at', 'asc')
            ->paginate(12)
            ->withQueryString();

        // ── First-lead flag ───────────────────────────────────────────────────
        // True when this affiliate has never had any lead at all
            $user = auth()->user();
            $isFirstLead = $user->affiliate && $user->affiliate->leads()->doesntExist();

        return view('affiliate.leads.marketplace', compact('leads', 'isFirstLead'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /affiliate/marketplace/{lead}/claim
    // ─────────────────────────────────────────────────────────────────────────
    public function claim(Lead $lead)
    {
        // ── Guard 1: lead must still be available ─────────────────────────────
        if ($lead->marketplace_status !== 'marketplace') {
            return back()->with('error', 'This lead is no longer available — it may have just been claimed by another affiliate.');
        }

        // ── Guard 2: must not already own an active lead for this company ─────
        $existing = Lead::where('company_id', $lead->company_id)
            ->where('affiliate_id', auth()->id())
            ->whereIn('status', ['active', 'demo_scheduled', 'demo_completed', 'pending_conversion', 'trial'])
            ->where('ownership_expires_at', '>', now())
            ->first();

        if ($existing) {
            return back()->with('error', 'You already have an active lead for this company.');
        }

        // ── Claim ─────────────────────────────────────────────────────────────
        // History (activities + notes) is intentionally PRESERVED.
        // Activities were anonymized (user_id = null) when the lead was recycled,
        // so no previous affiliate's identity is exposed.
        // Temperature is also preserved — it is a useful signal from prior work.

        $lead->update([
            'affiliate_id'         => auth()->id(),
            'marketplace_status'   => 'claimed',
            'claimed_at'           => now(),
            'status'               => 'active',
            'ownership_expires_at' => now()->addDays(Lead::OWNERSHIP_DURATION_DAYS),
            'marketplace_cycles'   => ($lead->marketplace_cycles ?? 0) + 1,
        ]);

        // Log the claim under the new affiliate's name
        LeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => auth()->id(),
            'type'        => 'lead_claimed',
            'description' => 'Lead claimed from marketplace'
                . ($lead->marketplace_cycles > 1
                    ? ' (cycle ' . $lead->marketplace_cycles . ')'
                    : ''),
        ]);

        return redirect()->route('affiliate.leads.show', $lead->id)
            ->with('success', 'Lead claimed! You have 60 days to work it.');
    }

}