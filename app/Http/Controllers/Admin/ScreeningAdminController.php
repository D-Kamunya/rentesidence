<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantCreditDispute;
use App\Models\TenantCreditProfile;
use App\Models\TenantScreeningLookup;
use App\Services\Screening\TenantCreditProfileService;
use Illuminate\Http\Request;

/**
 * Admin surface for tenant screening (Step 4): tune the monetization (per-lookup price + the
 * free-plan monthly allowance) and review the fairness backstop — tenant disputes against what
 * their credit profile shows. Both settings read via getOption with sane defaults so screening
 * works before anything is set here.
 */
class ScreeningAdminController extends Controller
{
    public function index(Request $request)
    {
        $data['pageTitle'] = __('Tenant Screening');
        $data['price']     = (float) getOption('screening_price', 30);
        $data['freeQuota'] = (int) getOption('screening_free_quota', 3);

        $data['stats'] = [
            'lookups_total' => TenantScreeningLookup::where('billed_as', '!=', 'none')->count(),
            'lookups_month' => TenantScreeningLookup::where('billed_as', '!=', 'none')
                ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
            'disputes_open' => TenantCreditDispute::where('status', 'open')->count(),
        ];

        $data['disputes'] = TenantCreditDispute::with(['profile', 'user'])
            ->orderByRaw("FIELD(status, 'open', 'reviewing', 'resolved', 'rejected')")
            ->latest()
            ->paginate(15);

        return view('admin.screening.index', $data);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'screening_price'      => 'required|numeric|min:0',
            'screening_free_quota' => 'required|integer|min:0|max:1000',
        ]);

        setOption('screening_price', (float) $request->screening_price);
        setOption('screening_free_quota', (int) $request->screening_free_quota);

        return back()->with('success', __('Screening settings saved.'));
    }

    public function updateDispute(Request $request, TenantCreditDispute $dispute)
    {
        $request->validate([
            'status'     => 'required|in:open,reviewing,resolved,rejected',
            'admin_note' => 'nullable|string|max:2000',   // internal
            'resolution' => 'nullable|string|max:2000',   // shown to the tenant
        ]);

        $dispute->update([
            'status'      => $request->status,
            'admin_note'  => $request->admin_note,
            'resolution'  => $request->resolution,
            'resolved_at' => in_array($request->status, ['resolved', 'rejected'], true) ? now() : null,
        ]);

        return back()->with('success', __('Dispute updated — the tenant will see your reply.'));
    }

    /**
     * Recompute a disputed profile from source. The correct remedy for "unfairly graded" (e.g.
     * off-system/cash payments the system never saw) is to fix the underlying record — the owner
     * marks those invoices paid — then refresh the score here. We never hand-edit the score
     * itself: that would break the objective, hard-to-game basis the whole ID rests on.
     */
    public function recompute(Request $request, TenantCreditProfile $profile)
    {
        app(TenantCreditProfileService::class)->computeForIdentity($profile->identity_key);

        return back()->with('success', __('Score recomputed from the latest records.'));
    }

    /**
     * Route the dispute to the party who can actually fix an off-system-payment gap: notify each
     * owner the tenant has a tenancy with, asking them to record any cash/other payments so the
     * score reflects them. The owner then reconciles + we recompute.
     */
    public function notifyOwners(Request $request, TenantCreditDispute $dispute)
    {
        $profile = $dispute->profile;
        if (! $profile) {
            return back()->with('error', __('No rental profile is linked to this dispute.'));
        }

        $ownerIds = app(TenantCreditProfileService::class)->ownerUserIdsFor($profile);
        if (empty($ownerIds)) {
            return back()->with('error', __('No owners are on record for this tenant.'));
        }

        $name = $profile->display_name ?: __('a tenant');
        foreach ($ownerIds as $ownerId) {
            addNotification(
                __('Tenant record dispute — please review'),
                __(':name has disputed their rental record. If they made any payments off-system (cash or other), please record them on the relevant invoices so their rental score reflects it.', ['name' => $name]),
                route('owner.tenant.index', ['type' => 'all']),
                null,
                $ownerId,
                auth()->id()
            );
        }

        $dispute->update(['owner_notified_at' => now()]);

        return back()->with('success', trans_choice(
            '{1}Notified 1 owner to reconcile.|[2,*]Notified :count owners to reconcile.',
            count($ownerIds),
            ['count' => count($ownerIds)]
        ));
    }
}
