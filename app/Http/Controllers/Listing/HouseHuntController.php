<?php
namespace App\Http\Controllers\Listing;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use App\Services\Listing\ListingService;
use App\Models\Property;
use App\Models\HouseHuntApplication;
use App\Http\Requests\HouseApplicationRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ResponseTrait;
use Exception;

class HouseHuntController extends Controller
{


    public function index(Request $request)
    {
        $rentals = app(PropertyService::class)->getEmptyUnitsGroupedByProperty();
        $sales   = app(ListingService::class)->getAllActive($request);

        $listings = collect();

        // Rentals
        foreach ($rentals as $property) {
            $units = app(PropertyService::class)->getEmptyUnitsByProperty($property->property_id);

            $rentRange = null;
            if ($units->isNotEmpty()) {
                $minRent = (float) $units->min('general_rent');
                $maxRent = (float) $units->max('general_rent');

                $rentRange = $minRent == $maxRent
                    ? currencyPrice($minRent) . ' / month'
                    : currencyPrice($minRent) . ' - ' . currencyPrice($maxRent) . ' / month';
            }

            $listings->push((object)[
                'id'         => $property->property_id,
                'name'       => $property->property_name,
                'address'    => $property->city . ', ' . $property->state,
                'city'       => ucfirst(strtolower($property->city)),
                'state'      => ucfirst(strtolower($property->state)),
                'country'    => $property->country,
                'image'      => $property->thumbnail_url ?? asset('assets/images/property.png'),
                'price'      => $rentRange, // Rentals keep formatted string
                'units'      => $property->empty_units_count,
                'agent'      => $property->owner_first_name . ' ' . $property->owner_last_name,
                'slug'       => $property->property_id,
                'type'       => 'rental'
            ]);
        }

        // Sales
        foreach ($sales as $sale) {
            $listings->push((object)[
                'id'      => $sale->id,
                'name'    => $sale->name,
                'address' => $sale->address,
                'city'    => ucfirst(strtolower($sale->city)),
                'state'   => ucfirst(strtolower($sale->state)),
                'country' => $sale->country,
                'image'   => assetUrl($sale->folder_name . '/' . $sale->file_name),
                'price'   => (float) $sale->price, // Store raw number for filtering
                'units'   => null,
                'agent'   => $sale->owner->name ?? '',
                'slug'    => $sale->slug,
                'type'    => 'sale'
            ]);
        }

        // --- Filters ---
        if ($request->filled('type')) {
            $listings = $listings->where('type', $request->type);
        }
        if ($request->filled('city')) {
            $listings = $listings->where('city', ucfirst(strtolower($request->city)));
        }
        if ($request->filled('state')) {
            $listings = $listings->where('state', ucfirst(strtolower($request->state)));
        }
        // Price filter (only for sale)
        if ($request->input('type') === 'sale' && ($request->filled('min_price') || $request->filled('max_price'))) {
            $min = (float) $request->input('min_price', 0);
            $max = $request->filled('max_price') ? (float) $request->input('max_price') : INF;

            $listings = $listings->filter(function ($item) use ($min, $max) {
                return (float) $item->price >= $min && (float) $item->price <= $max;
            });
        }

        // --- Conditional Dropdown Logic ---
        $states = collect();
        $cities = collect();

        if ($request->filled('type')) {
            $filtered = $listings->where('type', $request->type);
            $states   = $filtered->pluck('state')->filter()->unique()->values();
            $cities   = $filtered->pluck('city')->filter()->unique()->values();
        } else {
            // Default: show all unique states and cities
            $states = $listings->pluck('state')->filter()->unique()->values();
            $cities = $listings->pluck('city')->filter()->unique()->values();
        }

        return view('listing.frontend.house-hunt.househunt', [
            'listings' => $listings,
            'states'   => $states,
            'cities'   => $cities,
        ]);
    }


    public function getFiltersByType(Request $request)
    {
        $type = $request->input('type'); // sale, rental, or empty

        $rentals = app(PropertyService::class)->getEmptyUnitsGroupedByProperty();
        $sales   = app(ListingService::class)->getAllActive($request);

        $listings = collect();

        foreach ($rentals as $property) {
            $listings->push((object)[
                'type'  => 'rental',
                'state' => ucfirst(strtolower($property->state)),
                'city'  => ucfirst(strtolower($property->city)),
            ]);
        }

        foreach ($sales as $sale) {
            $listings->push((object)[
                'type'  => 'sale',
                'state' => ucfirst(strtolower($sale->state)),
                'city'  => ucfirst(strtolower($sale->city)),
            ]);
        }

        // Filter by type if selected
        if ($type) {
            $listings = $listings->where('type', $type);
        }

        $states = $listings->pluck('state')->filter()->unique()->values();

        return response()->json([
            'states' => $states,
            'cities' => [], // cities will be populated once a state is selected
        ]);
    }

    public function getCitiesByState(Request $request)
    {
        $state = $request->input('state');
        $type  = $request->input('type');

        $rentals = app(PropertyService::class)->getEmptyUnitsGroupedByProperty();
        $sales   = app(ListingService::class)->getAllActive($request);

        $listings = collect();

        foreach ($rentals as $property) {
            $listings->push((object)[
                'type'  => 'rental',
                'state' => ucfirst(strtolower($property->state)),
                'city'  => ucfirst(strtolower($property->city)),
            ]);
        }

        foreach ($sales as $sale) {
            $listings->push((object)[
                'type'  => 'sale',
                'state' => ucfirst(strtolower($sale->state)),
                'city'  => ucfirst(strtolower($sale->city)),
            ]);
        }

        if ($type) {
            $listings = $listings->where('type', $type);
        }

        if ($state) {
            $listings = $listings->where('state', ucfirst(strtolower($state)));
        }

        $cities = $listings->pluck('city')->filter()->unique()->values();

        return response()->json($cities);
    }


    public function viewProperty($propertyId)
    {
        $units = app(PropertyService::class)->getEmptyUnitsByProperty($propertyId);

        if ($units->isEmpty()) {
            abort(404, 'Property not found or has no vacant units.');
        }

        $property = app(PropertyService::class)->getDetailsById($propertyId);

        return view('listing.frontend.house-hunt.view', [
            'property' => $property,
            'emptyUnits' => $units,
        ]);
    }

    public function applicationStore(HouseApplicationRequest $request)
    {

        DB::beginTransaction();
        try {
            $application = new HouseHuntApplication();

            $existing = HouseHuntApplication::where('property_unit_id', $request->property_unit_id)
                ->where(function ($q) use ($request) {
                    $q->where('email', $request->email);

                    if (auth()->check()) {
                        $q->orWhere('user_id', auth()->id());
                    }
                })
                ->where('status', HOUSE_HUNT_APPLICATION_PENDING)
                ->first();
            

            if ($existing) {
                $message = 'You have already applied for this unit.';
                return $this->error([],  $message);
            }

            $application->property_unit_id = $request->property_unit_id;
            $application->user_id = auth()->id() ?? null;
            $application->first_name = $request->first_name;
            $application->last_name = $request->last_name;
            $application->email = $request->email;
            $application->job = $request->job;
            $application->age = $request->age;
            $application->contact_number = $request->contact_number;
            $application->family_member = $request->family_member;
            $application->permanent_address = $request->permanent_address;
            $application->permanent_country_id = $request->permanent_country_id;
            $application->permanent_state_id = $request->permanent_state_id;
            $application->permanent_city_id = $request->permanent_city_id;
            $application->permanent_zip_code = $request->permanent_zip_code;
            
            $application->save();

            DB::commit();

            $data = $application;
            $message =  __(APPLICATION_SUCCESSFULLY);
            return $this->success($data, $message);

        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }
}