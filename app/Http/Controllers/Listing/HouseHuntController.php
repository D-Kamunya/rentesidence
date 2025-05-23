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

    // Extract shared property info from the first unit
    $firstUnit = $units->first();

    $property = [
        'id' => $firstUnit->property_id,
        'name' => $firstUnit->property_name,
        'owner_name' => trim("{$firstUnit->owner_first_name} {$firstUnit->owner_last_name}"),
        'thumbnail_url' => $firstUnit->thumbnail_url ?? asset('images/placeholder.png'),
        'country' => $firstUnit->country ?? '',
        'city' => $firstUnit->city ?? '',
        'state' => $firstUnit->state ?? '',
    ];

    return view('listing.frontend.house-hunt.view', [
        'property' => $property,
        'emptyUnits' => $units,
    ]);
}
 
}