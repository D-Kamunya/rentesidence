<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantCloseRequest;
use App\Http\Requests\TenantDeleteRequest;
use App\Http\Requests\TenantRequest;
use App\Http\Requests\TenantEditRequest;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\InvoiceRecurringService;
use App\Services\InvoiceTypeService;
use App\Services\LocationService;
use App\Services\PropertyService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    use ResponseTrait;
    public $tenantService, $propertyService, $locationService, $invoiceTypeService;

    public function __construct()
    {
        $this->tenantService = new TenantService;
        $this->propertyService = new PropertyService;
        $this->invoiceTypeService = new InvoiceTypeService;
        $this->locationService = new LocationService;
    }

    public function index(Request $request)
    {
        $data['navTenantMMShowClass'] = 'mm-show';
        if ($request->type == 'history') {
            $data['pageTitle'] = __('Tenants History');
            $data['subNavTenantHistoryMMActiveClass'] = 'mm-active';
            $data['subNavTenantHistoryActiveClass'] = 'active';
            if ($request->ajax()) {
                return $data['tenants'] = $this->tenantService->getAllHistoryData();
            }
            return view('owner.tenants.history', $data);
        } else {
            $data['subNavAllTenantMMActiveClass'] = 'mm-active';
            $data['subNavAllTenantActiveClass'] = 'active';
            $data['pageTitle'] = __('Tenants');
            $data['properties'] = $this->propertyService->getAll();
            if (getOption('app_card_data_show', 1) == 1) {
            $data['tenants'] = $this->tenantService->getActiveAll($request); // pass $request
            }
            // "Needs attention" signals for the visible tenants — one batch (no N+1), for the cards.
            $data['attention'] = isset($data['tenants'])
                ? app(\App\Services\TenantAttentionService::class)->forTenants($data['tenants']->pluck('id')->all())
                : [];
            if ($request->ajax()) {
            return response()->json([
                    'cards'      => view('owner.tenants.partials.cards', $data)->render(),
                    'pagination' => (string) $data['tenants']->appends($request->query())->links(),
                ]);
            }
            return view('owner.tenants.index', $data);
        }
    }

    /**
     * Move-in "first invoice" modal — the amounts to offer for a just-assigned tenant's current
     * period (full / pro-rated / custom / skip). Owner-scoped; returns null context when move-in
     * invoicing doesn't apply (no monthly/yearly auto-setting), so the UI stays silent.
     */
    public function firstInvoicePreview(Request $request, $id)
    {
        $tenant = Tenant::with(['user', 'unit'])
            ->where('owner_user_id', auth()->id())
            ->find($id);
        if (!$tenant) {
            return $this->error([], __('Tenant not found.'));
        }

        $context = app(InvoiceRecurringService::class)->firstInvoiceContext($tenant);
        // Currency presentation so the modal renders amounts consistently with the rest of the app.
        $data = [
            'context'          => $context,        // null = not applicable → UI won't show the modal
            'currency_symbol'  => getCurrencySymbol(),
            'currency_placement' => getCurrencyPlacement(),
        ];
        return $this->success($data, '');
    }

    /**
     * Persist the owner's move-in invoice choice. Idempotent by billing period (never double-bills).
     * Interactive path only — bulk import keeps its own opening-balance flow.
     */
    public function firstInvoiceStore(Request $request, $id)
    {
        $request->validate([
            'mode'            => 'required|in:full,prorate,custom,skip',
            'custom_amount'   => 'nullable|numeric|min:1',
            'include_deposit' => 'nullable|boolean',
            'deposit_amount'  => 'nullable|numeric|min:1',
        ]);

        $tenant = Tenant::where('owner_user_id', auth()->id())->find($id);
        if (!$tenant) {
            return $this->error([], __('Tenant not found.'));
        }

        $depositAmount = $request->boolean('include_deposit') && $request->deposit_amount > 0
            ? (float) $request->deposit_amount
            : null;

        $res = app(InvoiceRecurringService::class)->generateFirstInvoice(
            $tenant,
            $request->mode,
            $request->mode === 'custom' ? (float) $request->custom_amount : null,
            $depositAmount
        );

        return $res['ok']
            ? $this->success(['invoice_id' => $res['invoice_id']], $res['message'])
            : $this->error([], $res['message']);
    }

    /**
     * Generate the FINAL pro-rated rent invoice at move-out, anchored to the tenant's active
     * notice-to-vacate date. Owner-scoped; the service guards against double-billing.
     */
    public function finalInvoiceStore(Request $request, $id)
    {
        $tenant = Tenant::where('owner_user_id', auth()->id())->find($id);
        if (!$tenant) {
            return $this->error([], __('Tenant not found.'));
        }
        $notice = app(\App\Services\VacationNoticeService::class)->activeNotice((int) $id);
        if (!$notice) {
            return $this->error([], __('This tenant has no active notice to vacate.'));
        }

        $request->validate([
            'mode'          => 'nullable|in:prorate,custom',
            'custom_amount' => 'nullable|numeric|min:1',
        ]);
        $custom = ($request->mode === 'custom' && $request->custom_amount > 0) ? (float) $request->custom_amount : null;

        $res = app(InvoiceRecurringService::class)->generateFinalInvoice($tenant, $notice->intended_move_out_date, $custom);

        return $res['ok']
            ? $this->success(['invoice_id' => $res['invoice_id'] ?? null], $res['message'])
            : $this->error([], $res['message']);
    }

    /** Reset one tenant's password and re-send their login details over email + SMS. */
    public function resendLogin(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $res = $this->tenantService->resendLogin($request->id);

        // DEV ONLY: reveal the generated password in the owner UI so the flow can be tested
        // locally before go-live. config('app.debug') is false in production, so this never leaks.
        $devSuffix = ($res['ok'] && config('app.debug') && ! empty($res['password']))
            ? ' — [DEV] ' . __('Password') . ': ' . $res['password']
            : '';

        if (! $res['ok']) {
            return back()->with('error', $res['message']);
        }

        // A "sent, but SMS couldn't go" outcome takes the warning channel, so it reads as
        // partially-done on the current page rather than a plain success.
        if (! empty($res['warning'])) {
            return back()->with('warning', $res['warning'] . $devSuffix);
        }

        return back()->with('success', $res['message'] . $devSuffix);
    }

    /** Send login details to every tenant who hasn't signed in yet (e.g. a bulk import that
     *  wasn't notified at import time). Regenerates each password since the original is hashed. */
    public function bulkResendLogins(Request $request)
    {
        $res = $this->tenantService->bulkResendLogins();

        if (($res['count'] ?? 0) === 0) {
            return back()->with('info', __('No tenants are waiting for login details — everyone has already signed in.'));
        }

        $message = trans_choice(
            '{1}Login details queued for 1 tenant.|[2,*]Login details queued for :count tenants.',
            $res['count'],
            ['count' => $res['count']]
        );

        // Surface the SMS-shortfall warning on this page instead of a clean success, so the
        // owner knows some texts were paused for lack of credits.
        if (! empty($res['warning'])) {
            return back()->with('warning', $message . ' ' . $res['warning']);
        }

        return back()->with('success', $message);
    }

    public function create()
    {
        if (getOwnerLimit(RULES_TENANT) < 1) {
            return back()->with('error', __('Your Tenant Limit is Finished. Choose or Renew Package Plan'));
        }
        $data['pageTitle'] = __('Add Tenant');
        $data['subNavAllTenantMMActiveClass'] = 'mm-active';
        $data['subNavAllTenantActiveClass'] = 'active';
        $data['countries'] = $this->locationService->getCountry()->getData()->data;
        $data['properties'] = Property::query()->with('propertyUnits')->where('owner_user_id', auth()->id())->get();
        return view('owner.tenants.add', $data);
    }

    public function edit($id)
    {
        $data['pageTitle'] = __('Edit Tenant');
        $data['subNavAllTenantMMActiveClass'] = 'mm-active';
        $data['subNavAllTenantActiveClass'] = 'active';
        $data['tenant'] = $this->tenantService->getDetailsById($id);
        $data['countries'] = $this->locationService->getCountry()->getData()->data;

        // $data['previousStates'] = $data['tenant']->previous_country_id
        //     ? $this->locationService->getStateByCountryId($data['tenant']->previous_country_id)->getData()->data->states
        //     : [];

        // $data['previousSities'] = $data['tenant']->previous_state_id
        //     ? $this->locationService->getCitiesByStateId($data['tenant']->previous_state_id)->getData()->data->cities
        //     : [];

        // $data['permanentStates'] = $data['tenant']->permanent_country_id
        //     ? $this->locationService->getStateByCountryId($data['tenant']->permanent_country_id)->getData()->data->states
        //     : [];

        // $data['permanentSities'] = $data['tenant']->permanent_state_id
        //     ? $this->locationService->getCitiesByStateId($data['tenant']->permanent_state_id)->getData()->data->cities
        //     : [];

        $data['properties'] = $this->propertyService->getAll();
        $data['units'] = $this->propertyService->getPropertyWithUnitsById($data['tenant']->property_id)->getData()->data->units ?? [];

        return view('owner.tenants.edit', $data);
    }

    public function store(Request $request)
    {
         // Determine which validation rules to apply
        if ($request->has('edit_form')) {
            // Editing an existing tenant
            $validated = app(TenantEditRequest::class)->validated();
        } else {
            // Creating a new tenant
            $validated = app(TenantRequest::class)->validated();
        }

        // Now replace original $request with validated data
        $request->merge($validated);

        if ($request->step == FORM_STEP_ONE) {
            return $this->tenantService->step1($request);
        } elseif ($request->step == FORM_STEP_TWO) {
            return $this->tenantService->step2($request);
        } elseif ($request->step == FORM_STEP_THREE) {
            return $this->tenantService->step3($request);
        }
        return $this->error();
    }

    public function details(Request $request, $id)
    {
        $data['navTenantMMShowClass'] = 'mm-show';
        $data['subNavAllTenantMMActiveClass'] = 'mm-active';
        $data['subNavAllTenantActiveClass'] = 'active';

        if ($request->tab == 'profile') {
            $data['pageTitle'] = __('Profile');
            $data['navTenantProfileActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getDetailsById($id);
            $data['paymentDueInvoiceCount'] = count($this->tenantService->paymentDue($id));
            // Pre-fill the Close-Tenant modal's refund/charge from a recorded deposit settlement (if any),
            // so closing reflects the real ledger-backed figures instead of hand-typed guesses.
            $data['depositSettlement'] = \App\Models\DepositSettlement::where('tenant_id', (int) $id)
                ->where('owner_user_id', auth()->id())->latest('id')->first();
            return view('owner.tenants.details.profile', $data);
        } elseif ($request->tab == 'home') {
            $data['pageTitle'] = __('Home Details');
            $data['navTenantHomeActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getDetailsById($id);
            return view('owner.tenants.details.home', $data);
        } elseif ($request->tab == 'payment') {
            $data['pageTitle'] = __('Payments & Deposit');
            $data['navTenantPaymentActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getById($id);
            $data['invoiceTypes'] = $this->invoiceTypeService->getAll();
            // Security deposit currently held for THIS tenancy (refundable — surfaced, not income).
            $data['depositHeld'] = app(\App\Services\DepositService::class)->totalHeldForTenant((int) $id);
            // The latest recorded deposit settlement (if any), so a settled deposit shows its outcome.
            $data['depositSettlement'] = \App\Models\DepositSettlement::with('items')
                ->where('tenant_id', (int) $id)->where('owner_user_id', auth()->id())
                ->latest('id')->first();
            // Any live notice-to-vacate the tenant has filed (surfaced for the owner to acknowledge).
            $data['activeNotice'] = app(\App\Services\VacationNoticeService::class)->activeNotice((int) $id);
            // Final-invoice preview (pro-rated to the notice's move-out date) — drives the modal + the
            // "generate final invoice first" nudge in the settle modal.
            $data['finalCtx'] = ($data['activeNotice'] && $data['tenant'])
                ? app(InvoiceRecurringService::class)->finalInvoiceContext($data['tenant'], $data['activeNotice']->intended_move_out_date)
                : null;
            if ($request->ajax()) {
                return $this->tenantService->payment($id);
            }
            return view('owner.tenants.details.payment', $data);
        } elseif ($request->tab == 'document') {
            $data['pageTitle'] = __('Document');
            $data['navTenantDocumentActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getById($id);
            $data['requests'] = app(\App\Services\KycConfigService::class)->getTenantRequests($id);
            return view('owner.tenants.details.document', $data);
        } elseif ($request->tab == 'closing-history') {
            $data['pageTitle'] = __('Closing History');
            $data['navTenantClosingHistoryActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->closingStatusHistory($id);
            return view('owner.tenants.details.closing-history', $data);
        }
    }

    public function closeHistoryStore(TenantCloseRequest $request, $id)
    {
        return $this->tenantService->closeHistoryStore($request, $id);
    }

    public function documentDestroy($id)
    {
        return $this->tenantService->documentDestroy($id);
    }

    public function delete(TenantDeleteRequest $request)
    {
        return $this->tenantService->delete($request);
    }

    /** Discard a half-built draft (owner decided not to proceed, e.g. after screening). */
    public function discardDraft(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $this->tenantService->discardDraft($request->id);

        return redirect()->route('owner.tenant.index', ['type' => 'all'])
            ->with('success', __('Tenant discarded. You can start again whenever you\'re ready.'));
    }
}
