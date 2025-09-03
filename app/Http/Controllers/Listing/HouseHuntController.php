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
    use ResponseTrait;

    public function index(Request $request)
    {
        $rentals = app(PropertyService::class)->getEmptyUnitsGroupedByProperty();
        $sales   = app(ListingService::class)->getAllActive($request);

        $listings = collect();

        // Normalize Rentals
        foreach ($rentals as $property) {
            $listings->push((object)[
                'id'            => $property->property_id,
                'name'          => $property->property_name,
                'address'       => $property->city . ', ' . $property->state,
                'country'       => $property->country,
                'image'         => $property->thumbnail_url ?? asset('assets/images/property.png'),
                'price'         => null, // rentals don’t have single price, handled in view
                'units'         => $property->empty_units_count,
                'agent'         => $property->owner_first_name . ' ' . $property->owner_last_name,
                'slug'          => $property->property_id, // for route
                'type'          => 'rental'
            ]);
        }

        // Normalize Sales
        foreach ($sales as $sale) {
            $listings->push((object)[
                'id'            => $sale->id,
                'name'          => $sale->name,
                'address'       => $sale->address,
                'country'       => $sale->country,
                'state'         => $sale->state,
                'city'          => $sale->city,
                'image'         => assetUrl($sale->folder_name . '/' . $sale->file_name),
                'price'         => $sale->price,
                'units'         => null,
                'agent'         => $sale->owner->name ?? '',
                'slug'          => $sale->slug,
                'type'          => 'sale'
            ]);
        }

        return view('listing.frontend.house-hunt.househunt', compact('listings'));
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