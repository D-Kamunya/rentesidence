<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HouseApplicationRequest extends FormRequest
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
        return [
            'property_unit_id' => 'required',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'contact_number' => 'required|string',
            'permanent_address' => 'required|string',
            'permanent_country_id' => 'required|string',
            'permanent_state_id' => 'required|string',
            'permanent_city_id' => 'required|string',
            'permanent_zip_code' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'property_unit_id.required' => 'Please select unit.',
            'first_name.required' => 'First name field is required.',
            'last_name.required' => 'Last name field is required.',
            'email.required' => 'Email is required.',
            'contact_number.required' => 'Contact Number field is required.',
            'permanent_address.required' => 'Address field is required.',
            'permanent_country_id.required' => 'Country field is required.',
            'permanent_state_id.required' => 'State field is required.',
            'permanent_city_id.required' => 'City field is required.',
            'permanent_zip_code.required' => 'Zip Code is required.',
        ];
    }
}

