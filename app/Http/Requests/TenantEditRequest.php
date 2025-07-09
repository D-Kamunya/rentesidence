<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantEditRequest extends FormRequest
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
            'step' => 'required'
        ];
        if ($this->step == FORM_STEP_ONE) {
            $userId = isset($this->user_id) ? $this->user_id : null;
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users,email,' . $userId,
                'contact_number' => 'required|unique:users,contact_number,' . $userId,
                'password' => (is_null($userId)) ? 'required' : 'nullable',
                'permanent_address' => 'required',
                'permanent_country_id' => 'required',
                'permanent_state_id' => 'required',
                'permanent_city_id' => 'required',
                'permanent_zip_code' => 'required',
                'family_member' => 'required|numeric',
                'age' => 'numeric',
                'job' => 'required',
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'permanent_address.required' => 'The address field is required.',
            'permanent_country_id.required' => 'The country field is required.',
            'permanent_state_id.required' => 'The state is required.',
            'permanent_city_id.required' => 'The city is required.',
            'permanent_zip_code.required' => 'The zip is required.',
        ];
    }
}
