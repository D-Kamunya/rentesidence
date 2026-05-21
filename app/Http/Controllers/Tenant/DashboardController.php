<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\NoticeBoard;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Ticket;
use App\Models\Owner;
use App\Models\Product;



class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['pageTitle'] = __('Dashboard');
        $tenantUser = auth()->user()->tenant;
        $data['property'] = Property::findOrFail($tenantUser->property_id);
        $data['unit'] = PropertyUnit::findOrFail($tenantUser->unit_id);
        $data['tenant'] = $tenantUser;
        $data['invoices'] = Invoice::where('tenant_id', $tenantUser->id)
            ->with(['invoiceItems.invoiceType'])
            ->latest()
            ->get()
            ->each(function ($invoice) {
                $invoice->item_types_label = $invoice->invoiceItems
                    ->map(fn($item) => optional($item->invoiceType)->name)
                    ->filter()
                    ->unique()
                    ->implode(' · ');
            });
        $data['totalTickets'] = Ticket::query()->where('unit_id', $tenantUser->unit_id)->count();
        $data['today'] = date('Y-m-d');
        $data['notices'] = NoticeBoard::with('userNotices')
            ->where(function ($q) use ($tenantUser) {
                $q->where('unit_id', $tenantUser->unit_id)
                    ->orWhere('unit_all', ACTIVE);
            })
            ->where('start_date', '<=', $data['today'])
            ->where('end_date', '>=', $data['today'])
            ->where('owner_user_id', auth()->user()->owner_user_id)
            ->latest()
            ->limit(10)
            ->get();
            
        $owner = Owner::where('user_id', auth()->user()->owner_user_id)->first();
        $data['featuredProducts'] = $owner
            ? Product::where('owner_user_id', $owner->id)
                ->latest()
                ->take(4)
                ->get()
            : collect(); 

        return view('tenant.dashboard')->with($data);
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
        return view('tenant.notification')->with($data);
    }

    public function notices()
    {
        $data['pageTitle'] = __('Notices List');
        $data['today'] = date('Y-m-d');
        $tenantUser = auth()->user()->tenant;
        $data['notices'] = NoticeBoard::with('userNotices')
            ->where(function ($q) use ($tenantUser) {
                $q->where('unit_id', $tenantUser->unit_id)
                    ->orWhere('unit_all', ACTIVE);
            })
            ->where('start_date', '<=', $data['today'])
            ->where('owner_user_id', auth()->user()->owner_user_id)
            ->latest()
            ->get();
        return view('tenant.notice')->with($data);
    }
}
