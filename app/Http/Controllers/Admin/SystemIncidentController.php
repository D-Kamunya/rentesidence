<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemIncident;
use Illuminate\Http\Request;

/** Admin surface for genuine platform failures captured by SystemIncidentService. */
class SystemIncidentController extends Controller
{
    /** Human labels for the capture types. */
    public const TYPE_LABELS = [
        SystemIncident::TYPE_PAYOUT_FAILURE     => 'Payout failure',
        SystemIncident::TYPE_CALLBACK_EXCEPTION => 'Callback processing',
        SystemIncident::TYPE_CALLBACK_REJECTED  => 'Forged callback',
        SystemIncident::TYPE_JOB_FAILED         => 'Background job',
        SystemIncident::TYPE_SCHEDULE_FAILED    => 'Scheduled task',
        SystemIncident::TYPE_COMMS_FAILURE      => 'Delivery failure',
    ];

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'open');

        $query = SystemIncident::query();
        if ($tab === 'open') {
            $query->unresolved();
        } elseif ($tab === 'resolved') {
            $query->where('status', SystemIncident::STATUS_RESOLVED);
        }

        // Critical + unresolved first, then most recently seen.
        $incidents = $query
            ->orderByRaw("CASE WHEN status = ? THEN 1 ELSE 0 END", [SystemIncident::STATUS_RESOLVED])
            ->orderByRaw("CASE WHEN severity = ? THEN 0 ELSE 1 END", [SystemIncident::SEVERITY_CRITICAL])
            ->orderByDesc('last_seen_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.incidents.index', [
            'incidents'  => $incidents,
            'tab'        => $tab,
            'typeLabels' => self::TYPE_LABELS,
            'openCount'  => SystemIncident::unresolved()->count(),
            'pageTitle'  => __('System Incidents'),
        ]);
    }

    /** Acknowledge — "I've seen it, working on it" (keeps it out of the critical-first sort's top). */
    public function acknowledge(SystemIncident $incident)
    {
        if (! $incident->isResolved()) {
            $incident->update([
                'status'          => SystemIncident::STATUS_ACKNOWLEDGED,
                'acknowledged_by' => auth()->id(),
            ]);
        }

        return back()->with('success', __('Incident acknowledged.'));
    }

    /** Resolve — clears it from the open list and the nav badge. A fresh occurrence re-opens it. */
    public function resolve(SystemIncident $incident)
    {
        $incident->update([
            'status'      => SystemIncident::STATUS_RESOLVED,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return back()->with('success', __('Incident resolved.'));
    }
}
