<?php

namespace App\Http\Requests\Owner\Property;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
//            'country' => 'required',
//            'city' => 'required',
//            'state' => 'required',
            // Zip is rarely used/known in Kenya; the map link can't be produced manually when the
            // location search finds nothing — so both are OPTIONAL (a missing search result must
            // still let an owner save with a typed address). The address itself stays required —
            // a property's location is essential — and is auto-filled by the search when it hits.
            'zip_code' => 'nullable',
            'address' => 'required',
            'map_link' => 'nullable|url',
        ];
        return $rules;
    }
}
