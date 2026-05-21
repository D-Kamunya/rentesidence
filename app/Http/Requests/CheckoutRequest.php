<?php

namespace App\Http\Requests;

use App\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CheckoutRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules that support two distinct checkout flows:
     *
     * 1. INVOICE PAYMENT  — identified by presence of `invoice_id`
     *    Fields: invoice_id, gateway, currency, cart_total (nullable for
     *    transaction-model tenants), mpesa_account_id, bank_id, bank_slip.
     *
     * 2. PRODUCT/CART CHECKOUT — identified by presence of `products`
     *    Fields: cartTotal, products, products.*.id, products.*.quantity,
     *    plus optional gateway/currency/mpesa fields.
     *
     * Each flow only validates its own fields — the other flow's fields are
     * treated as optional/ignored so neither breaks the other.
     */

    public function rules(): array
    {
        if ($this->isInvoiceFlow()) {
            return $this->invoiceRules();
        }

        if ($this->isSubscriptionFlow()) {
            return $this->subscriptionRules();
        }

        return $this->productRules();
    }

    // ── Invoice payment rules ────────────────────────────────────────────────

    private function invoiceRules(): array
    {
        $isTransactionModel = $this->isTransactionModelRequest();

        return [
            'invoice_id'       => ['required', 'integer', 'exists:invoices,id'],
            'gateway'          => ['required', 'string'],
            'currency'         => ['required', 'string'],

            // Not required for transaction-model tenants — server resolves
            // amount directly from the invoice record.
            'cart_total'       => $isTransactionModel ? ['nullable'] : ['required'],

            // Gateway-specific — only validated when present
            'mpesa_account_id' => ['sometimes', 'nullable', 'integer', 'exists:mpesa_accounts,id'],
            'bank_id'          => ['sometimes', 'nullable', 'required_with:bank_slip', 'integer'],
            'bank_slip'        => [
                'sometimes', 'nullable', 'required_with:bank_id',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
        ];
    }

    // ── Product / cart checkout rules ────────────────────────────────────────

    private function productRules(): array
    {
        return [
            // Cart total — required and numeric for product purchases
            'cartTotal'           => ['required', 'numeric', 'min:1'],

            // Products array
            'products'            => ['required', 'array', 'min:1'],
            'products.*.id'       => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],

            // Gateway fields — optional since server may resolve these
            // server-side for transaction-model owners (same pattern as invoices)
            'gateway'             => ['sometimes', 'string'],
            'currency'            => ['sometimes', 'string'],
            'mpesa_account_id'    => ['sometimes', 'nullable', 'integer', 'exists:mpesa_accounts,id'],
            'bank_id'             => ['sometimes', 'nullable', 'required_with:bank_slip', 'integer'],
            'bank_slip'           => [
                'sometimes', 'nullable', 'required_with:bank_id',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
        ];
    }

    private function isSubscriptionFlow(): bool
    {
        return $this->has('package_id');
    }

    private function subscriptionRules(): array
    {
        $rules = [
            'package_id'    => ['required', 'integer', 'exists:packages,id'],
            'gateway'       => ['required', 'string'],
            'currency'      => ['required', 'string'],
            'duration_type' => ['required', 'integer'],
            'quantity'      => ['nullable', 'integer', 'min:1'],
        ];

        if ($this->input('gateway') === 'bank') {
            $rules['bank_id']   = ['required', 'integer', 'exists:banks,id'];
            $rules['bank_slip'] = ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'];
        }

        if ($this->input('gateway') === 'mpesa' && $this->has('mpesa_transaction_code')) {
            $rules['mpesa_transaction_code'] = ['required', 'string'];
        }

        return $rules;
    }

    // ── Validation messages ──────────────────────────────────────────────────

    public function messages(): array
    {
        return [
            // Invoice flow
            'invoice_id.required'      => 'Invoice is required.',
            'invoice_id.exists'        => 'Invoice not found.',
            'gateway.required'         => 'Please select a payment gateway.',
            'currency.required'        => 'Please select a currency.',
            'cart_total.required'      => 'The cart total field is required.',
            'bank_id.required_with'    => 'The bank field is required.',
            'bank_slip.required_with'  => 'The bank slip field is required.',
            'mpesa_account_id.exists'  => 'Mpesa Account is required.',

            // Product flow
            'cartTotal.required'           => 'The cart total field is required.',
            'cartTotal.numeric'            => 'The cart total must be a number.',
            'cartTotal.min'                => 'The cart total must be at least 1.',
            'products.required'            => 'No products were provided.',
            'products.*.id.required'       => 'A product ID is required.',
            'products.*.id.exists'         => 'One or more products could not be found.',
            'products.*.quantity.required' => 'A product quantity is required.',
            'products.*.quantity.min'      => 'Product quantity must be at least 1.',

            // Subscription flow
            'package_id.required'            => 'Package is required.',
            'package_id.exists'              => 'Selected package does not exist.',
            'duration_type.required'         => 'Duration type is required.',
            'quantity.min'                   => 'Quantity must be at least 1.',
            'mpesa_transaction_code.required'=> 'Please enter your Mpesa transaction code.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        // Covers fetch() calls from mpesa STK and any other JSON consumers
        if ($this->header('accept') == 'application/json' || 
            $this->header('Accept') == 'application/json') {
            $error = '';
            if ($validator->fails()) {
                $error = $validator->errors()->first();
            }
            return $this->validationErrorApi($validator, $error);
        }

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Invoice flow is identified by the presence of invoice_id in the request.
     * Falls back to product flow if not present (safe default).
     */
    private function isInvoiceFlow(): bool
    {
        return $this->has('invoice_id');
    }

    /**
     * Check if the authenticated tenant's owner is on a transaction pricing
     * model. When true, cart_total is not required for invoice payments because
     * the server resolves the amount directly from the invoice record.
     */
    private function isTransactionModelRequest(): bool
    {
        if (!auth()->check() || !auth()->user()->owner_user_id) {
            return false;
        }

        $subscription = DB::table('owner_packages')
            ->where('user_id', auth()->user()->owner_user_id)
            ->where('status', 1)
            ->latest()
            ->first();

        return ($subscription?->pricing_model ?? 'free') === 'transaction';
    }
}