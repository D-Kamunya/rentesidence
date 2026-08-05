<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Exceptions\FacilityActiveModeLockException;
use App\Centresidence\Exceptions\OwnerNotInTransactionModeException;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Services\FinanceApplicationService;
use App\Centresidence\Services\PaymentModeService;
use Illuminate\Support\Facades\DB;

/**
 * Financing ⇄ transaction-mode rules: an owner must be on transaction mode to
 * begin a financing application, and cannot leave it while a facility is active.
 */
class PaymentModeTest extends CentresidenceDatabaseTestCase
{
    private function financeDraftData(int $ownerId): array
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_financeable' => true]);
        $catalogue = $module->pricingCatalogueItems()->create(['item_name' => 'Meter', 'unit_price' => 3500]);
        $module->platformFeeConfigs()->create(['fee_percentage' => 10, 'is_active' => true]);
        $partner = FinancePartner::create(['company_name' => 'Acme', 'status' => FinancePartner::STATUS_ACTIVE]);
        $partnerModule = FinancePartnerModule::create([
            'finance_partner_id' => $partner->id, 'module_id' => $module->id,
            'product_name' => 'Loan', 'interest_rate' => 18, 'min_repayment_months' => 12,
        ]);

        return [
            'owner_id' => $ownerId, 'property_id' => 1, 'module_id' => $module->id,
            'finance_partner_id' => $partner->id, 'finance_partner_module_id' => $partnerModule->id,
            'catalogue_item_id' => $catalogue->id, 'quantity' => 10,
        ];
    }

    public function test_transaction_mode_owner_can_apply(): void
    {
        // Owner 1 is seeded as transaction mode.
        $app = app(FinanceApplicationService::class)->createDraft($this->financeDraftData(1));
        $this->assertSame(FinanceApplication::STATUS_DRAFT, $app->status);
    }

    public function test_non_transaction_owner_is_blocked_from_applying(): void
    {
        // Flip owner 1 to subscription mode.
        DB::table('owner_packages')->where('user_id', 1)->update(['pricing_model' => 'subscription']);

        $this->expectException(OwnerNotInTransactionModeException::class);
        app(FinanceApplicationService::class)->createDraft($this->financeDraftData(1));
    }

    public function test_switching_mode_retags_existing_modules(): void
    {
        $service = app(PaymentModeService::class);
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter']);

        // Owner 1 starts on transaction mode → a deployed module is transaction-billed.
        $pm = \App\Centresidence\Models\PropertyModule::create([
            'property_id' => 1, 'owner_id' => 1, 'module_id' => $module->id,
            'active_meter_count' => 4, 'status' => 'active',
            'billing_model' => \App\Centresidence\Models\PropertyModule::BILLING_TRANSACTION,
        ]);

        // Switch the owner to subscription → the module must follow.
        $service->switchTo(1, 'subscription');

        $this->assertSame(
            \App\Centresidence\Models\PropertyModule::BILLING_SUBSCRIPTION,
            $pm->fresh()->billing_model,
            'Existing modules must re-tag to the new pricing mode.'
        );

        // And back to transaction.
        $service->switchTo(1, PaymentModeService::MODE_TRANSACTION);
        $this->assertSame(
            \App\Centresidence\Models\PropertyModule::BILLING_TRANSACTION,
            $pm->fresh()->billing_model
        );
    }

    public function test_sync_modules_to_owner_mode_fixes_drift(): void
    {
        // Simulates a package activation that changed the mode WITHOUT switchTo
        // (the setUserPackage path): the module is left tagged 'subscription'
        // while owner 1 is transaction mode → drift. syncModulesToOwnerMode fixes it.
        $service = app(PaymentModeService::class);
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter']);
        $pm = \App\Centresidence\Models\PropertyModule::create([
            'property_id' => 1, 'owner_id' => 1, 'module_id' => $module->id,
            'active_meter_count' => 4, 'status' => 'active',
            'billing_model' => \App\Centresidence\Models\PropertyModule::BILLING_SUBSCRIPTION,
        ]);

        $this->assertTrue($service->isTransactionMode(1));
        $service->syncModulesToOwnerMode(1);

        $this->assertSame(
            \App\Centresidence\Models\PropertyModule::BILLING_TRANSACTION,
            $pm->fresh()->billing_model
        );
    }

    public function test_cannot_leave_transaction_mode_with_active_facility(): void
    {
        $service = app(PaymentModeService::class);
        $this->assertTrue($service->isTransactionMode(1));

        // No facility yet → may switch.
        $service->assertCanSwitchTo(1, 'subscription');

        // Create an active facility for owner 1.
        $data = $this->financeDraftData(1);
        $partnerModuleId = $data['finance_partner_module_id'];
        $appId = FinanceApplication::create([
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $data['module_id'],
            'finance_partner_id' => $data['finance_partner_id'], 'finance_partner_module_id' => $partnerModuleId,
            'quantity' => 1, 'requested_amount' => 50000, 'status' => FinanceApplication::STATUS_APPROVED,
        ])->id;
        FinanceFacility::create([
            'finance_application_id' => $appId, 'finance_partner_id' => $data['finance_partner_id'],
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $data['module_id'],
            'outstanding_principal' => 50000, 'status' => FinanceFacility::STATUS_ACTIVE,
        ]);

        // Now locked.
        $this->assertTrue($service->hasActiveFacility(1));
        $this->expectException(FacilityActiveModeLockException::class);
        $service->assertCanSwitchTo(1, 'subscription');
    }
}
