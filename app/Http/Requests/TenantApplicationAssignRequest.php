<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantApplicationAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the assign-from-application flow.
     *
     * We only validate the lease/unit fields the owner supplies in the modal.
     * Personal & address data come straight from the HouseHuntApplication record
     * and are already validated at submission time, so we don't re-validate them.
     */
    public function rules(): array
    {
        return [
            'application_id'        => 'required|integer|exists:house_hunt_applications,id',
            'property_id'           => 'required|integer|exists:properties,id',
            'unit_id'               => 'required|integer|exists:property_units,id',
            'lease_start_date'      => 'required|date',
            'lease_end_date'        => 'nullable|date|after:lease_start_date',
            'general_rent'          => 'required|numeric|min:1',
            'due_date'              => 'required|integer|min:1|max:31',
            'security_deposit_type' => 'nullable|string',
            'security_deposit'      => 'nullable|numeric|min:0',
            'late_fee_type'         => 'nullable|string',
            'late_fee'              => 'nullable|numeric|min:0',
            'incident_receipt'      => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required'          => 'Please select a unit.',
            'property_id.required'      => 'Property is required.',
            'lease_start_date.required' => 'Lease start date is required.',
            'general_rent.required'     => 'Rent amount is required.',
            'general_rent.min'          => 'Rent must be at least 1.',
            'due_date.required'         => 'Rent due date is required.',
            'lease_end_date.after'      => 'Lease end date must be after the start date.',
        ];
    }
}