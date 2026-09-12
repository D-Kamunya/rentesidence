<?php

namespace App\Services;

use App\Models\Lead;

class TemplateSubstitutionService
{
    public function substitute(string $template, Lead $lead, bool $hasBrochure = false): string
    {
        $affiliate = $lead->affiliate;
        $company   = $lead->company;
        $contact   = $lead->contact_person_name ?? $company->company_name;

        $vars = [
            // Stays correct whether a material is attached or not, without losing the
            // convincing tone: strong ("attached") when one is linked, inviting otherwise.
            '{{brochure_line}}'   => $hasBrochure
                ? 'I\'ve attached a brochure with more details.'
                : 'I\'d be glad to send over a brochure with the full details — just let me know.',

            // Contact / company
            '{{contact_name}}'    => $contact,
            '{{company_name}}'    => $company->company_name ?? '',
            '{{company_phone}}'   => $company->phone ?? '',
            '{{company_email}}'   => $company->email ?? '',
            '{{company_city}}'    => $company->city ?? '',
            '{{company_country}}' => $company->country ?? '',
            '{{estimated_units}}' => $company->estimated_units ?? '',
            '{{property_type}}'   => $company->property_type 
                                        ? ucfirst(str_replace('_', ' ', $company->property_type)) 
                                        : '',

            // Affiliate
            '{{affiliate_name}}'  => $affiliate 
                                        ? trim($affiliate->first_name . ' ' . $affiliate->last_name) 
                                        : '',
            '{{affiliate_email}}' => $affiliate->email ?? '',
            '{{affiliate_phone}}' => $affiliate->phone ?? '',

            // Lead context
            '{{lead_status}}'     => ucfirst(str_replace('_', ' ', $lead->status)),
            '{{lead_temperature}}'=> ucfirst($lead->temperature),
            '{{demo_date}}'       => $lead->demo_scheduled_at 
                                        ? $lead->demo_scheduled_at->format('l, d M Y \a\t H:i') 
                                        : '',
        ];

        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    /**
     * Returns a list of all supported placeholders with descriptions.
     * Used to render the helper UI in the admin template form.
     */
    public function availablePlaceholders(): array
    {
        return [
            'Contact & Company' => [
                '{{contact_name}}'    => 'Primary contact person at the company',
                '{{company_name}}'    => 'Company / property name',
                '{{company_phone}}'   => 'Company phone number',
                '{{company_email}}'   => 'Company email address',
                '{{company_city}}'    => 'City',
                '{{company_country}}' => 'Country',
                '{{estimated_units}}' => 'Number of property units',
                '{{property_type}}'   => 'Type of property (residential, commercial…)',
            ],
            'Affiliate' => [
                '{{affiliate_name}}'  => 'Full name of the affiliate',
                '{{affiliate_email}}' => 'Affiliate email address',
                '{{affiliate_phone}}' => 'Affiliate phone number',
            ],
            'Lead Context' => [
                '{{lead_status}}'      => 'Current lead status',
                '{{lead_temperature}}' => 'Lead temperature (Hot / Warm / Cold)',
                '{{demo_date}}'        => 'Scheduled demo date and time',
            ],
            'Smart' => [
                '{{brochure_line}}'    => 'Auto-phrases a brochure mention — "attached" when a material is linked to this template, "happy to send" when not',
            ],
        ];
    }
}