<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditUnitRequest extends FormRequest
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
            'property_id' => 'required',
            'unit_name' => 'required',
            'bedroom' => 'required',
            'bath' => 'required',
            'kitchen' => 'required',
        ];
    }
}
