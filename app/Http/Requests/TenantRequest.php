<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantRequest extends FormRequest
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
            // Email/phone identity is resolved in TenantService::step1, NOT by a unique: rule here.
            // A match may be the owner's OWN returning tenant (closed or soft-deleted) that should be
            // RECONNECTED rather than blocked (detect-and-link, Phase 1). A hard unique: here would
            // reject that before the service runs — and because unique: ignores the soft-delete scope,
            // it also let a deleted tenant permanently burn an email/phone. The service blocks anything
            // that belongs elsewhere; the DB keeps its unique index as the final guard. See
            // TenantService::resolveTenantIdentity().
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'contact_number' => 'required',
                'password' => 'nullable', // auto-generated for new tenants; sent + changed on first login
                // Address is OPTIONAL: an owner often can't reliably know a tenant's permanent
                // address (and there's no way to validate it) — forcing it just yields fabricated
                // data. Capture it when known; never block onboarding on it. Contact + unit are
                // what matter operationally and stay required.
                'permanent_address' => 'nullable',
                'permanent_country_id' => 'nullable',
                'permanent_state_id' => 'nullable',
                'permanent_city_id' => 'nullable',
                'permanent_zip_code' => 'nullable',
                // Job / age / household are OPTIONAL at owner-onboarding. They're PII best known by
                // the person themselves — an owner forced to fill them just fabricates data, which
                // POLLUTES the Global Tenant ID (whose value is objective, person-owned data). They
                // stay on the form (captured when the owner genuinely knows them) and are authored
                // for real by the tenant on their own profile (ProfileController / my-profile).
                // Self-register already blanks these, and the profile view null-guards with '—'.
                'family_member' => 'nullable|numeric',
                'age' => 'nullable|numeric',
                'job' => 'nullable',
            ];
        }
        if ($this->step == FORM_STEP_TWO) {
            $rules = [
                'property_id' => 'required',
                'unit_id' => 'required',
                'lease_start_date' => 'required',
                'general_rent' => 'required|min:1|numeric',
                'due_date' => 'required',
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
