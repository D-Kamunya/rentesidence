<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Maintainer;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\Owner;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Ticket;
use App\Models\HouseHuntApplication;
use App\Services\OwnerService;
use App\Services\PropertyService;
use App\Services\Sms\SmsCreditsService;
use App\Services\TicketService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public $propertyService;
    public $ticketService;

    public function __construct()
    {
        $this->propertyService = new PropertyService;
        $this->ticketService   = new TicketService;
    }

    public function dashboard()
    {
        
        $owner   = Owner::where('user_id', Auth::id())->firstOrFail();
        $ownerId = auth()->id();
    
        $data['pageTitle']        = __('Dashboard');
        $data['totalProperties']  = Property::where('owner_user_id', auth()->id())->count();
        $data['totalUnitsdash']   = $this->propertyService->allUnit(false)->count();
        $data['totalTenants']     = Tenant::where('owner_user_id', auth()->id())
                                          ->where('status', TENANT_STATUS_ACTIVE)
                                          ->count();
        $data['properties']       = $this->propertyService->getAllCount()->take(3);
        $data['tickets']          = $this->ticketService->getAll();
        $data['totalMaintainers'] = Maintainer::where('owner_user_id', auth()->id())->count();
        $data['pendingTickets']   = Ticket::where('owner_user_id', auth()->id())
                                          ->whereIn('status', [1, 2])
                                          ->count();
    
        // Chart Rent overview
        $data['months'] = array_values(month());
        $invoices = Invoice::query()
            ->select(
                DB::raw('sum(amount) as `total`'),
                DB::raw('month'),
                DB::raw('max(created_at) as createdAt')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->where('owner_user_id', auth()->id())
            ->where('status', INVOICE_STATUS_PAID)
            ->get();
        $data['yearlyTotalAmount'] = $invoices->sum('total');
    
        $invoiceMonthlyAmount = [];
        foreach ($data['months'] as $month) {
            $valueMonth = $invoices->where('month', $month)->first();
            $invoiceMonthlyAmount[] = $valueMonth ? $valueMonth->total : 0;
        }
        $data['invoiceMonthlyAmount'] = $invoiceMonthlyAmount;
    
        // Marketplace prompt
        $hasProducts                   = Product::where('owner_user_id', $owner->id)->exists();
        $data['showMarketplacePrompt'] = !$hasProducts && !session()->has('marketplace_prompt_shown');
        $data['listedProductsCount']   = Product::where('owner_user_id', $owner->id)->count();
        if ($data['showMarketplacePrompt']) {
            session()->put('marketplace_prompt_shown', true);
        }
    
        // ── SMS Credits ───────────────────────────────────────────
        $data['smsCredits']       = (int) $owner->sms_credits;
        $data['smsLowThreshold']  = (int) getOption('sms_low_credit_threshold', 50);
        $data['smsPricePerCredit'] = (float) getOption('sms_credit_price', 1.00);
    
        $data['smsFailedCount'] = \App\Models\SmsHistory::where('owner_user_id', auth()->id())
            ->where('status', SMS_STATUS_FAILED)
            ->where('error', 'Insufficient SMS credits')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    
        // ── Pending Product Orders ────────────────────────────────
        $data['pendingProductOrdersCount'] = ProductOrder::whereHas('orderItems.product', function ($q) use ($owner) {
                $q->where('owner_user_id', $owner->id);
            })
            ->orderPending() // scope using order_status
            ->count();

        // ── Pending Applications ────────────────────────────────
        $data['pendingApplicationsCount'] = HouseHuntApplication::with(['propertyUnit.property'])
            ->whereHas('propertyUnit.property', function ($q) use ($ownerId) {
                $q->where('owner_user_id', $ownerId);
            })
            ->where('status', HOUSE_HUNT_APPLICATION_PENDING)
            ->count();

        return view('owner.dashboard')->with($data);
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
        return view('owner.notification')->with($data);
    }

    public function topSearch(Request $request)
    {
        $data['status'] = false;
        if ($request->keyword) {
            $ownerService  = new OwnerService;
            $searchContent = $ownerService->topSearch($request);
            $data['data']   = view('owner.top-search-append', $searchContent)->render();
            $data['status'] = $searchContent['status'];
        }
        return response()->json($data);
    }
}