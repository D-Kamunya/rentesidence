<?php

namespace App\Http\Requests;

use App\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class InstantCheckoutRequest extends FormRequest
{
    use ResponseTrait;
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
    /**
     * Strip spaces/dashes before validating so "0712 345 678" is accepted and a
     * clean MSISDN flows downstream to the STK push (phoneValidator normalises the
     * prefix but not internal whitespace).
     */
    protected function prepareForValidation()
    {
        if ($this->has('mpesa_number')) {
            $this->merge([
                'mpesa_number' => preg_replace('/[\s-]/', '', (string) $this->input('mpesa_number')),
            ]);
        }
    }

    public function rules()
    {
        return [
            // Kenyan MSISDN only: 07/01, 7…, 254…, +254… — rejects arbitrary numbers,
            // which (together with the route throttle) stops this unauthenticated
            // endpoint being used to spam STK prompts at an arbitrary victim's phone.
            'mpesa_number' => ['required', 'regex:/^(?:\+?254|0)?[17]\d{8}$/'],
        ];
    }

    public function messages()
    {
        return [
            'mpesa_number.required' => 'The Mpesa number field is required.',
            'mpesa_number.regex'    => 'Enter a valid Safaricom M-Pesa number (e.g. 0712345678).',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        if ($this->header('accept') == "application/json") {
            $error = '';
            if ($validator->fails()) {
                $error = $validator->errors()->first();
            }
            return $this->validationErrorApi($validator, $error);
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
    }
}
