<?php

namespace App\Services\OwnerAdvisor;

use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

/**
 * The owner "get set up" checklist — a live, real-progress teacher that lets a new owner self-onboard
 * to collecting rent, so our on-site help becomes a choice, not a requirement. Each step's done/not-done
 * is computed from the owner's ACTUAL data (never a manual tick), with a button to the action. Fully
 * guarded so a bare install degrades to "no checklist", never an error.
 */
class OwnerSetupService
{
    /**
     * @return array{steps:array<int,array{key:string,label:string,done:bool,cta_label:string,cta_url:string}>,done:int,total:int,complete:bool}
     */
    public function checklist(int $userId): array
    {
        $steps = [];

        try {
            $propertyIds = Property::where('owner_user_id', $userId)->pluck('id');
            $hasProperty = $propertyIds->isNotEmpty();
            $hasUnit     = $hasProperty && PropertyUnit::whereIn('property_id', $propertyIds)->exists();
            $hasTenant   = Tenant::where('owner_user_id', $userId)->exists();
            $hasRecurring = Schema::hasTable('invoice_recurring_settings')
                && \App\Models\InvoiceRecurringSetting::where('owner_user_id', $userId)->exists();
            // Loaded SMS credits — a value step (SMS is the earliest/most reliable usage rail).
            $hasSms = Schema::hasTable('owner_credit_transactions')
                && \App\Models\OwnerCreditTransaction::where('owner_user_id', $userId)->where('bucket', 'sms')->exists();

            $steps = [
                $this->step('property', __('Add your first property'), $hasProperty, __('Add property'), $this->url('owner.property.allProperty')),
                $this->step('units', __('Add units to your property'), $hasUnit, __('Add units'), $this->url('owner.property.allUnit')),
                $this->step('tenant', __('Add your first tenant'), $hasTenant, __('Add tenant'), $this->url('owner.tenant.index')),
                // Recurring turns on automatically with the first tenant, so this ticks itself — kept
                // as a visible milestone. Completion (auto-hide) is gated on the core steps below.
                $this->step('recurring', __('Turn on automatic rent invoices'), (bool) $hasRecurring, __('Set up rent'), $this->url('owner.invoice.recurring-setting.index')),
                $this->step('sms', __('Load SMS credits to reach your tenants'), (bool) $hasSms, __('Load SMS'), $this->url('owner.sms.credits.index')),
            ];
        } catch (\Throwable $e) {
            $steps = [];
        }

        // The checklist auto-hides once the CORE setup is done (property → units → tenant →
        // recurring). SMS is a promoted bonus step shown during setup, but it never blocks
        // completion — ongoing SMS adoption is the Upgrade Advisor's job, not a permanent nag.
        $coreKeys  = ['property', 'units', 'tenant', 'recurring'];
        $coreSteps = array_filter($steps, fn ($s) => in_array($s['key'], $coreKeys, true));
        $coreDone  = count(array_filter($coreSteps, fn ($s) => $s['done']));

        $done  = count(array_filter($steps, fn ($s) => $s['done']));
        $total = count($steps);

        return [
            'steps'    => $steps,
            'done'     => $done,
            'total'    => $total,
            'complete' => count($coreSteps) > 0 && $coreDone === count($coreSteps),
        ];
    }

    private function step(string $key, string $label, bool $done, string $ctaLabel, string $ctaUrl): array
    {
        return ['key' => $key, 'label' => $label, 'done' => $done, 'cta_label' => $ctaLabel, 'cta_url' => $ctaUrl];
    }

    private function url(string $name): string
    {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : url('/');
    }
}
