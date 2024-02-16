<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
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
            'name' => 'required|unique:packages,name,' . $this->id,
            'pricing_type' => 'required|in:1,2,3',
            'per_monthly_price' => 'required|numeric',
            'per_yearly_price' => 'required|numeric',
            'status' => 'required|integer',
            'monthly_price' => 'required|numeric',
            'yearly_price' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'property_price' => 'The property field is required',
            'unit_price' => 'The unit field is required',
            'tenant_price' => 'The tenant field is required',
        ];
    }
}
