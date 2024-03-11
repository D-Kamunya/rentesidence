<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GatewayRequest extends FormRequest
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
            
            'key' => 'required_unless:slug,bank,cash,mpesa',
            'url' => 'required_if:slug,flutterwave',
            'secret' => 'required_unless:slug,bank,cash,mollie,paystack,stripe,coinbase,mpesa',
            'currency.*' => 'required',
            'conversion_rate.*' => 'required',
        ];
        if (is_array($this->get('bank'))) {
            $rules = array_merge($rules, [
                'bank.name.0' => 'required_if:slug,bank|max:255',
                'bank.name.*' => 'required_if:slug,bank|max:255',
                'bank.account_name.*' => 'required_if:slug,bank|max:255',
                'bank.account_number.*' => 'required_if:slug,bank|max:255',
                'bank.status.*' => 'required_if:slug,bank',
                'bank.bank_details.*' => 'required_if:slug,bank',
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'required_if' => 'This field is required.',
            'required_unless' => 'This field is required.'
        ];
    }
}
