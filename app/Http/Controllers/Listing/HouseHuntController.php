<?php
namespace App\Http\Controllers\Listing;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use App\Services\Listing\ListingService;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\PropertyDetail;
use App\Models\HouseHuntApplication;
use App\Http\Requests\HouseApplicationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\ResponseTrait;
use Exception;

class HouseHuntController extends Controller
{
    use ResponseTrait;

    public function index(Request $request)
    {
        // ── 1. Build listings collection ──────────────────────────────────────

        $rentals = app(PropertyService::class)->getEmptyUnitsGroupedByProperty();
        $sales   = app(ListingService::class)->getAllActive($request);

        $listings = collect();

        foreach ($rentals as $property) {
            // Rent range is already available from the grouped query via
            // getEmptyUnitsGroupedByProperty — no second per-property query needed.
            // We store the raw min/max so we can format here without extra DB hits.
            $minRent = (float) ($property->min_rent ?? 0);
            $maxRent = (float) ($property->max_rent ?? 0);

            $rentRange = null;
            if ($minRent > 0 || $maxRent > 0) {
                $rentRange = $minRent == $maxRent
                    ? currencyPrice($minRent) . ' / month'
                    : currencyPrice($minRent) . ' – ' . currencyPrice($maxRent) . ' / month';
            }

            $listings->push((object) [
                'id'      => $property->property_id,
                'name'    => $property->property_name,
                'address' => $property->city . ', ' . $property->state,
                'city'    => ucfirst(strtolower($property->city)),
                'state'   => ucfirst(strtolower($property->state)),
                'country' => $property->country,
                'image'   => $property->thumbnail_url ?? asset('assets/images/property.png'),
                'price'   => $rentRange,
                'units'   => $property->empty_units_count,
                'agent'   => $property->owner_first_name . ' ' . $property->owner_last_name,
                'slug'    => $property->property_id,
                'type'    => 'rental',
            ]);
        }

        foreach ($sales as $sale) {
            $listings->push((object) [
                'id'      => $sale->id,
                'name'    => $sale->name,
                'address' => $sale->address,
                'city'    => ucfirst(strtolower($sale->city)),
                'state'   => ucfirst(strtolower($sale->state)),
                'country' => $sale->country,
                'image'   => assetUrl($sale->folder_name . '/' . $sale->file_name),
                'price'   => (float) $sale->price,
                'units'   => null,
                'agent'   => $sale->owner->name ?? '',
                'slug'    => $sale->slug,
                'type'    => 'sale',
            ]);
        }

        // ── 2. Filter in-memory (only the final $listings collection) ─────────
        if ($request->filled('type'))  $listings = $listings->where('type', $request->type);
        if ($request->filled('city'))  $listings = $listings->where('city',  ucfirst(strtolower($request->city)));
        if ($request->filled('state')) $listings = $listings->where('state', ucfirst(strtolower($request->state)));

        if ($request->input('type') === 'sale' && ($request->filled('min_price') || $request->filled('max_price'))) {
            $min = (float) $request->input('min_price', 0);
            $max = $request->filled('max_price') ? (float) $request->input('max_price') : INF;
            $listings = $listings->filter(fn($item) => (float) $item->price >= $min && (float) $item->price <= $max);
        }

        // ── 3. Dropdown options ────────────────────────────────────────────────
        $base   = $request->filled('type') ? $listings->where('type', $request->type) : $listings;
        $states = $base->pluck('state')->filter()->unique()->values();
        $cities = $base->pluck('city')->filter()->unique()->values();

        // ── 4. Paginate the in-memory collection ───────────────────────────────
        $perPage     = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems   = $listings->values()->slice(($currentPage - 1) * $perPage, $perPage);

        $listings = new LengthAwarePaginator(
            $pageItems,
            $listings->count(),   // total before slicing
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('listing.frontend.house-hunt.househunt', compact('listings', 'states', 'cities'));
    }

    // ── Dropdown AJAX endpoints ────────────────────────────────────────────────
    // These now hit the DB directly instead of rebuilding the full listings collection.

    public function getFiltersByType(Request $request)
    {
        $type = $request->input('type');

        // Rentals: query property_details joined to properties with vacant units
        $rentalStates = DB::table('property_details')
            ->join('properties', 'property_details.property_id', '=', 'properties.id')
            ->join('property_units', 'properties.id', '=', 'property_units.property_id')
            ->leftJoin('tenants', fn($j) =>
                $j->on('property_units.id', '=', 'tenants.unit_id')
                  ->where('tenants.status', DB::raw(TENANT_STATUS_ACTIVE))
            )
            ->whereNull('tenants.id')
            ->whereNotNull('property_details.state_id')
            ->distinct()
            ->pluck('property_details.state_id')
            ->map(fn($s) => ucfirst(strtolower($s)))
            ->unique()
            ->values();

        $saleStates = DB::table('listings')
            ->where('status', 1)
            ->whereNotNull('state')
            ->distinct()
            ->pluck('state')
            ->map(fn($s) => ucfirst(strtolower($s)))
            ->unique()
            ->values();

        $states = match ($type) {
            'rental' => $rentalStates,
            'sale'   => $saleStates,
            default  => $rentalStates->merge($saleStates)->unique()->values(),
        };

        return response()->json(['states' => $states->values(), 'cities' => []]);
    }

    public function getCitiesByState(Request $request)
    {
        $state = $request->input('state');
        $type  = $request->input('type');

        $rentalCities = collect();
        $saleCities   = collect();

        if (!$type || $type === 'rental') {
            $rentalCities = DB::table('property_details')
                ->join('properties', 'property_details.property_id', '=', 'properties.id')
                ->join('property_units', 'properties.id', '=', 'property_units.property_id')
                ->leftJoin('tenants', fn($j) =>
                    $j->on('property_units.id', '=', 'tenants.unit_id')
                      ->where('tenants.status', DB::raw(TENANT_STATUS_ACTIVE))
                )
                ->whereNull('tenants.id')
                ->when($state, fn($q) =>
                    $q->whereRaw('LOWER(property_details.state_id) = ?', [strtolower($state)])
                )
                ->whereNotNull('property_details.city_id')
                ->distinct()
                ->pluck('property_details.city_id')
                ->map(fn($c) => ucfirst(strtolower($c)))
                ->unique()
                ->values();
        }

        if (!$type || $type === 'sale') {
            $saleCities = DB::table('listings')
                ->where('status', 1)
                ->when($state, fn($q) =>
                    $q->whereRaw('LOWER(state) = ?', [strtolower($state)])
                )
                ->whereNotNull('city')
                ->distinct()
                ->pluck('city')
                ->map(fn($c) => ucfirst(strtolower($c)))
                ->unique()
                ->values();
        }

        $cities = $rentalCities->merge($saleCities)->unique()->values();

        return response()->json($cities);
    }

    // ── View property ──────────────────────────────────────────────────────────

    public function viewProperty($propertyId)
    {
        // Eager-load images so the blade never triggers N+1
        $units = PropertyUnit::query()
            ->with('images') // ← eager load; eliminates N+1 per unit
            ->where('property_id', $propertyId)
            ->whereDoesntHave('activeTenant')
            ->orderBy('id')
            ->get();

        if ($units->isEmpty()) {
            abort(404, 'Property not found or has no vacant units.');
        }

        $property = app(PropertyService::class)->getDetailsById($propertyId);

        return view('listing.frontend.house-hunt.view', compact('property', 'units'));
    }

    // ── Application store ─────────────────────────────────────────

    public function applicationStore(HouseApplicationRequest $request)
    {
        DB::beginTransaction();
        try {
            $existing = HouseHuntApplication::where('property_unit_id', $request->property_unit_id)
                ->where(function ($q) use ($request) {
                    $q->where('email', $request->email);
                    if (auth()->check()) $q->orWhere('user_id', auth()->id());
                })
                ->where('status', HOUSE_HUNT_APPLICATION_PENDING)
                ->first();

            if ($existing) {
                return $this->error([], 'You have already applied for this unit.');
            }

            $application = HouseHuntApplication::create([
                'property_unit_id'     => $request->property_unit_id,
                'user_id'              => auth()->id() ?? null,
                'first_name'           => $request->first_name,
                'last_name'            => $request->last_name,
                'email'                => $request->email,
                'job'                  => $request->job,
                'age'                  => $request->age,
                'contact_number'       => $request->contact_number,
                'family_member'        => $request->family_member,
                'permanent_address'    => $request->permanent_address,
                'permanent_country_id' => $request->permanent_country_id,
                'permanent_state_id'   => $request->permanent_state_id,
                'permanent_city_id'    => $request->permanent_city_id,
                'permanent_zip_code'   => $request->permanent_zip_code,
            ]);

            DB::commit();
            return $this->success($application, __(APPLICATION_SENT_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }
}