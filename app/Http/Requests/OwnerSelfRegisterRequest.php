<?php

namespace App\Http\Requests;

use App\Rules\ReCaptcha;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Public landlord self-signup (the ad-landing "Start free" path). Kept SEPARATE from the
 * shared OwnerRegisterRequest — that one is deliberately password-less (admin/affiliate/API
 * flows issue a temp password + forced reset), whereas a self-registering owner sets their
 * own password here. Anti-phantom guards live here too: unique email/phone, a honeypot, and
 * reCAPTCHA when the admin has it enabled. Rate-limiting is applied on the route.
 */
class OwnerSelfRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30', 'unique:users,contact_number'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
            // Honeypot — a hidden field a human never sees; a bot fills it and is rejected.
            'website'        => ['prohibited'],
        ];

        // Only enforce reCAPTCHA when the admin has it switched on (the form renders the
        // widget under the same flag) — so signup works whether or not it's configured.
        if (getOption('GOOGLE_RECAPTCHA_MAIL_STATUS', 0) == ACTIVE) {
            $rules['g-recaptcha-response'] = [new ReCaptcha];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'website.prohibited'     => __('Something went wrong. Please try again.'),
            'password.confirmed'     => __('The passwords do not match.'),
            'contact_number.unique'  => __('An account with this phone number already exists. Try signing in.'),
            'email.unique'           => __('An account with this email already exists. Try signing in.'),
        ];
    }
}
