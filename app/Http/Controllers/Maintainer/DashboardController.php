<?php

namespace App\Http\Controllers\Maintainer;

use App\Http\Controllers\Controller;
use App\Models\NoticeBoard;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['pageTitle'] = __('Dashboard');
        $authUser = auth()->user();
        // Scope EVERYTHING to the maintainer's ASSIGNED properties (properties.maintainer_id),
        // not the owner's whole portfolio — the property count/list was leaking owner-wide.
        $propertyIds = $authUser->maintainer ? $authUser->maintainer->properties->pluck('id')->toArray() : [];
        $data['properties'] = Property::whereIn('id', $propertyIds)->get();
        $data['totalOpenTickets'] = Ticket::whereIn('property_id', $propertyIds)->where('status', TICKET_STATUS_OPEN)->count();
        $data['totalResolvedTickets'] = Ticket::whereIn('property_id', $propertyIds)->where('status', TICKET_STATUS_RESOLVED)->count();
        $data['totalCloseTickets'] = Ticket::whereIn('property_id', $propertyIds)->where('status', TICKET_STATUS_CLOSE)->count();
        $data['today'] = date('Y-m-d');
        $data['notices'] = NoticeBoard::whereIn('property_id', $propertyIds)->where('start_date', '<=', $data['today'])->where('end_date', '>=', $data['today'])->limit(10)->get();
        $data['tickets'] = Ticket::with(['property', 'unit', 'user', 'topic'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->limit(10)
            ->get();
        return view('maintainer.dashboard')->with($data);
    }

    public function notification()
    {
        $data['pageTitle'] = __('Notification');
        Notification::query()
            ->where(function ($q) {
                $q->where('notifications.user_id', auth()->id())
                    ->orWhere('notifications.user_id', null);
            })
            ->update(['is_seen' => ACTIVE]);
        return view('maintainer.notification')->with($data);
    }
}
