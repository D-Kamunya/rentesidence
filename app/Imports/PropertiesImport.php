<?php

namespace App\Imports;

use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PropertiesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        
            // Create or update Property
        $property = Property::updateOrCreate(
            ['name' => $row['property_name'],'owner_user_id' => auth()->id()],
            [
                'property_type' => $row['property_type'],
                'number_of_unit' => $row['number_of_units'],
                'description' => $row['description'],
                'owner_user_id' => auth()->id(),
            ]
        );
        return $property;
    }
}
