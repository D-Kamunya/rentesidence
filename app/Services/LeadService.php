<?php

namespace App\Services;

use App\Jobs\Mail\SendDemoCompletedMail;
use App\Jobs\Mail\SendDemoScheduledMail;
use App\Jobs\Mail\SendTrialRequestedMail;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Services\AffiliateOs\ProductRegistry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Business logic for the affiliate lead lifecycle. Extracted from the (formerly
 * 537-line) LeadController so the controller only authorizes, validates, and
 * delegates. Every mutation records a LeadActivity for the audit trail.
 */
class LeadService
{
    /** Record an activity against a lead by the acting user. */
    public function logActivity(Lead $lead, string $type, string $description, ?int $userId = null): LeadActivity
    {
        return LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'description' => $description,
        ]);
    }

    /**
     * Create a lead for the affiliate, reusing or creating its company and
     * refusing a duplicate active lead on the same company. Atomic.
     *
     * @throws RuntimeException when the company already has an active lead
     */
    public function createLead(array $data, int $affiliateUserId): Lead
    {
        return DB::transaction(function () use ($data, $affiliateUserId) {
            $normalized = $this->normalizeCompanyName($data['company_name']);

            $company = Company::where(function ($q) use ($normalized, $data) {
                $q->where(function ($q2) use ($normalized, $data) {
                    $q2->where('normalized_name', $normalized)
                        ->where('city', $data['city'] ?? null)
                        ->where('country', $data['country'] ?? null);
                })->orWhere('phone', $data['phone'] ?? null);
            })->first();

            if (! $company) {
                $company = Company::create([
                    'company_name'    => $data['company_name'],
                    'normalized_name' => $normalized,
                    'country'         => $data['country'] ?? null,
                    'city'            => $data['city'] ?? null,
                    'phone'           => $data['phone'] ?? null,
                    'estimated_units' => $data['estimated_units'] ?? null,
                    'email'           => $data['email'] ?? null,
                    'website'         => $data['website'] ?? null,
                    'property_type'   => $data['property_type'] ?? null,
                ]);
            }

            $existingLead = Lead::where('company_id', $company->id)
                ->whereIn('status', ['active', 'pending_conversion', 'trial'])
                ->where('ownership_expires_at', '>', now())
                ->first();

            if ($existingLead) {
                throw new RuntimeException('This company already has an active lead.');
            }

            return Lead::create([
                'company_id'          => $company->id,
                'affiliate_id'        => $affiliateUserId,
                'product'             => $data['product'] ?? ProductRegistry::default(),
                'contact_person_name' => $data['contact_person_name'],
                'contact_person_role' => $data['contact_person_role'],
                'temperature'         => $data['temperature'] ?? 'cold',
                'status'              => 'active',
            ]);
        });
    }

    /** Update a lead's contact details + any non-empty company fields. */
    public function updateLead(Lead $lead, array $leadData, array $companyData): void
    {
        $lead->update($leadData);

        $companyData = array_filter($companyData, fn ($v) => ! is_null($v) && $v !== '');
        if (! empty($companyData) && $lead->company) {
            $lead->company->update($companyData);
        }
    }

    /** Append a timestamped note and log it. */
    public function addNote(Lead $lead, string $note): void
    {
        $entry = '[' . now()->format('Y-m-d H:i') . '] ' . $note;
        $lead->update([
            'notes' => $lead->notes ? $lead->notes . "\n\n" . $entry : $entry,
            'last_activity_at' => now(),
        ]);
        $this->logActivity($lead, 'note_added', $note);
    }

    public function setTemperature(Lead $lead, string $temperature): void
    {
        $lead->update(['temperature' => $temperature, 'last_activity_at' => now()]);
        $this->logActivity($lead, 'temperature_update', 'Temperature set to ' . $temperature);
    }

    public function scheduleDemo(Lead $lead, string $demoDate, ?string $meetingLink = null): void
    {
        $meetingLink = $meetingLink ?: null; // normalise '' → null
        $lead->update([
            'status' => 'demo_scheduled',
            'demo_scheduled_at' => $demoDate,
            'demo_meeting_link' => $meetingLink,
            'last_activity_at' => now(),
        ]);
        optional($lead->company)->update(['sales_status' => 'contacted']);
        $this->logActivity($lead, 'demo_scheduled', 'Demo scheduled for ' . $demoDate);
        SendDemoScheduledMail::dispatch(
            $lead->id,
            Carbon::parse($demoDate)->format('l, F j, Y \a\t g:i A'),
            $meetingLink,
        );
    }

    public function markDemoCompleted(Lead $lead): void
    {
        $lead->update(['status' => 'demo_completed', 'last_activity_at' => now()]);
        optional($lead->company)->update(['sales_status' => 'demo_done']);
        $this->logActivity($lead, 'demo_completed', 'Demo completed');
        SendDemoCompletedMail::dispatch($lead->id);
    }

    public function reject(Lead $lead, string $reasonDescription): void
    {
        $lead->update(['status' => 'rejected', 'last_activity_at' => now()]);
        optional($lead->company)->update(['sales_status' => 'inactive']);
        $this->logActivity($lead, 'lead_rejected', 'Rejected - ' . $reasonDescription);
    }

    public function markLost(Lead $lead): void
    {
        $lead->update(['status' => 'lost', 'last_activity_at' => now()]);
        optional($lead->company)->update(['sales_status' => 'inactive']);
        $this->logActivity($lead, 'lead_lost', 'Lead marked lost');
    }

    /** Whether the next trial action is an EXTENSION (last trial activity was an expiry). */
    public function isTrialExtension(Lead $lead): bool
    {
        $latest = $lead->activities()
            ->whereIn('type', ['trial_requested', 'trial_extension', 'trial_expired', 'conversion_rejected'])
            ->orderByDesc('created_at')
            ->first();

        return $latest && $latest->type === 'trial_expired';
    }

    /**
     * Submit a trial (or extension) request: move to pending_conversion, log it,
     * and notify admin.
     *
     * @return array{success: bool, message: string}
     */
    public function submitTrialRequest(Lead $lead, bool $isExtension, ?string $reason, $affiliateUser): array
    {
        if (! in_array($lead->status, ['demo_completed', 'pending_conversion'])) {
            return ['success' => false, 'message' => 'This lead is not ready for conversion request.'];
        }

        $lead->update(['status' => 'pending_conversion', 'last_activity_at' => now()]);

        if ($isExtension) {
            $type = 'trial_extension';
            $description = 'Trial extension requested by affiliate. Reason: ' . $reason;
            $message = 'Trial extension request submitted successfully! Admin will review your request.';
        } else {
            $type = 'trial_requested';
            $description = 'Trial approval requested by affiliate';
            $message = 'Trial request submitted successfully!';
        }

        $this->logActivity($lead, $type, $description);

        SendTrialRequestedMail::dispatch(
            $lead->id,
            $isExtension,
            $reason ?? '',
            $affiliateUser->first_name . ' ' . $affiliateUser->last_name,
            $affiliateUser->email,
        );

        return ['success' => true, 'message' => $message];
    }

    /** Canonicalise a company name for dedup (drop suffixes, punctuation, case). */
    private function normalizeCompanyName(string $name): string
    {
        $remove = ['limited', 'ltd', 'apartments', 'apartment', 'estate', 'properties', 'realestate'];

        $words = preg_split('/\s+/', strtolower($name));
        $words = array_diff($words, $remove);
        $normalized = preg_replace('/[^a-z0-9 ]/', '', implode(' ', $words));

        return trim($normalized);
    }
}
