<?php
namespace App\Http\Controllers\Listing;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use App\Models\Property;
use Illuminate\Support\Facades\Storage;


class HouseHuntController extends Controller
{
    public function index()
    {
        $properties = app(PropertyService::class)->getEmptyUnitsGroupedByProperty();
        return view('listing.frontend.house-hunt.househunt', compact('properties'));
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
 
}