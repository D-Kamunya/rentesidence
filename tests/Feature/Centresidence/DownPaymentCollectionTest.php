<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Events\DownPaymentCollected;
use App\Centresidence\Models\FacilityTransaction;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Services\DownPaymentCollectionService;
use App\Centresidence\Services\FinanceApplicationService;
use App\Centresidence\Services\FinanceFacilityService;
use Illuminate\Support\Facades\Event;

/**
 * Owner down-payment collection for partially-financed facilities. Centresidence
 * (installer/payee) collects the contribution at disbursement; the 'log' driver
 * settles it inline so nothing is left open in dev/simulation.
 */
class DownPaymentCollectionTest extends CentresidenceDatabaseTestCase
{
    private function facilityWithContribution(float $contribution): FinanceFacility
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'is_financeable' => true]);
        $module->pricingCatalogueItems()->create(['item_name' => 'Meter', 'unit_price' => 3500.00, 'installation_cost' => 0]);
        $module->platformFeeConfigs()->create(['fee_percentage' => 10.00, 'is_active' => true]);

        $partner = FinancePartner::create(['company_name' => 'Acme Capital', 'status' => FinancePartner::STATUS_ACTIVE]);
        $partnerModule = FinancePartnerModule::create([
            'finance_partner_id' => $partner->id, 'module_id' => $module->id,
            'product_name' => 'Loan', 'interest_rate_type' => 'reducing_balance', 'interest_rate' => 18.00,
            'interest_calculation_method' => 'monthly_rest',
            'min_repayment_months' => 12, 'max_repayment_months' => 36, 'max_rent_deduction_percentage' => 30.00,
        ]);

        $catalogue = $module->pricingCatalogueItems()->first();
        $application = app(FinanceApplicationService::class)->createDraft([
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $module->id,
            'finance_partner_id' => $partner->id, 'finance_partner_module_id' => $partnerModule->id,
            'catalogue_item_id' => $catalogue->id, 'quantity' => 10, 'owner_contribution' => $contribution,
        ]);

        return app(FinanceFacilityService::class)->createFromApplication($application);
    }

    public function test_no_contribution_is_marked_not_required(): void
    {
        $facility = $this->facilityWithContribution(0);

        app(DownPaymentCollectionService::class)->collect($facility);

        $this->assertSame('not_required', $facility->fresh()->down_payment_status);
        $this->assertSame(0, FacilityTransaction::where('finance_facility_id', $facility->id)
            ->where('transaction_type', FacilityTransaction::TYPE_DOWN_PAYMENT)->count());
    }

    public function test_log_driver_collects_and_writes_ledger(): void
    {
        config(['centresidence.collections.driver' => 'log']);
        Event::fake([DownPaymentCollected::class]);

        // total 38,500, contribution 10,000 → financed 28,500.
        $facility = $this->facilityWithContribution(10000);
        $this->assertSame('28500.00', $facility->principal_amount);
        $this->assertSame('10000.00', $facility->owner_contribution);

        app(DownPaymentCollectionService::class)->collect($facility);

        $facility->refresh();
        $this->assertSame('collected', $facility->down_payment_status);
        $this->assertNotNull($facility->down_payment_collected_at);

        $txn = FacilityTransaction::where('finance_facility_id', $facility->id)
            ->where('transaction_type', FacilityTransaction::TYPE_DOWN_PAYMENT)->first();
        $this->assertNotNull($txn);
        $this->assertSame('10000.00', $txn->amount);
        $this->assertSame('credit', $txn->direction);
        $this->assertSame('owner_payment', $txn->source);
        Event::assertDispatched(DownPaymentCollected::class);
    }

    public function test_collection_is_idempotent(): void
    {
        $facility = $this->facilityWithContribution(10000);
        $service = app(DownPaymentCollectionService::class);

        $service->collect($facility);
        $service->collect($facility->fresh());
        $service->markCollected($facility->fresh(), 'AGAIN');

        $this->assertSame(1, FacilityTransaction::where('finance_facility_id', $facility->id)
            ->where('transaction_type', FacilityTransaction::TYPE_DOWN_PAYMENT)->count());
    }

    public function test_disbursement_triggers_collection_via_listener(): void
    {
        $facility = $this->facilityWithContribution(10000);

        // Real event (not faked) so the CollectDownPaymentOnDisbursement listener runs.
        app(FinanceFacilityService::class)->disburse($facility, 'ESCROW-1');

        $this->assertSame('collected', $facility->fresh()->down_payment_status);
    }
}
