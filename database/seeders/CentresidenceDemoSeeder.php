<?php

namespace Database\Seeders;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModulePricingCatalogueItem;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Services\CashflowService;
use App\Centresidence\Services\FinanceApplicationService;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a realistic end-to-end Centresidence demo for clicking through all three
 * portals. Idempotent (re-runnable) and defensive — each section is guarded so a
 * failure in one part won't abort the rest. Reuses the existing demo owner/admin
 * logins; adds a finance-partner login.
 *
 *   php artisan db:seed --class=CentresidenceDemoSeeder
 */
class CentresidenceDemoSeeder extends Seeder
{
    public function run(): void
    {
        [$water, $gas, $lock] = $this->seedModules();
        $gateway = Gateway::firstOrCreate(['name' => 'Demo Gateway A'], ['status' => 'active', 'is_simulated' => true]);
        $partner = $this->seedPartner([$water, $gas]);

        $owner = User::where('role', USER_ROLE_OWNER)->orderBy('id')->first();
        if (! $owner) {
            $this->command->warn('No owner (role 1) found — skipping owner-side demo. Create an owner, then re-run.');
            $this->summary($partner, null);

            return;
        }

        $this->setTransactionMode($owner->id);
        $property = $this->resolveProperty($owner);
        $unit = $this->resolveUnit($property);
        $this->seedCashflow($owner, $property, $unit);
        $pm = $this->seedPropertyModule($owner, $property, $water, $gateway);
        $this->seedSampleApplication($owner, $property, $partner, $water);

        $this->summary($partner, $owner);
    }

    /** @return Module[] */
    private function seedModules(): array
    {
        // Production module catalog (modules, copy, pricing, cost components,
        // platform fee) lives in the shippable CentresidenceCatalogSeeder so the
        // exact environment can be seeded on the live server too.
        $this->call(CentresidenceCatalogSeeder::class);

        return [
            Module::where('key', 'water_meter')->firstOrFail(),
            Module::where('key', 'gas_meter')->firstOrFail(),
            Module::where('key', 'smart_lock')->firstOrFail(),
        ];
    }

    private function seedPartner(array $modules): FinancePartner
    {
        $user = User::firstOrCreate(['email' => 'partner@centresidence.test'], [
            'first_name' => 'Bridgewater', 'last_name' => 'Capital', 'password' => Hash::make('123456'),
            'role' => USER_ROLE_FINANCE_PARTNER, 'status' => ACTIVE, 'contact_number' => '254700111222',
            'email_verified_at' => now(),
        ]);
        $partner = FinancePartner::firstOrCreate(['user_id' => $user->id], [
            'company_name' => 'Bridgewater Capital', 'trading_name' => 'Bridgewater', 'status' => FinancePartner::STATUS_ACTIVE,
            'settlement_account_details' => ['type' => 'bank', 'label' => 'Equity Bank', 'paybill' => '247247', 'account' => 'BWC-001'],
        ]);

        foreach ($modules as $m) {
            $product = FinancePartnerModule::firstOrCreate(
                ['finance_partner_id' => $partner->id, 'module_id' => $m->id],
                [
                    'product_name' => $m->name . ' Infrastructure Loan', 'interest_rate_type' => 'reducing_balance',
                    'interest_rate' => 18, 'interest_calculation_method' => 'monthly_rest',
                    'min_amount' => 5000, 'max_amount' => 1000000, 'min_repayment_months' => 12, 'max_repayment_months' => 36,
                    'repayment_frequency' => 'monthly', 'max_rent_deduction_percentage' => 30,
                    'required_cashflow_months' => 3, 'min_occupancy_rate' => 70,
                    'grace_period_days' => 5, 'default_threshold_days' => 30, 'early_repayment_allowed' => true,
                    'early_repayment_penalty_percentage' => 2, 'monthly_settlement_enabled' => true, 'settlement_day' => 1,
                    'status' => 'active',
                ]
            );
            if ($product->underwritingRules()->count() === 0) {
                $product->underwritingRules()->createMany([
                    ['rule_name' => 'min_occupancy', 'rule_type' => 'threshold', 'parameter' => 'occupancy_rate', 'operator' => 'gte', 'value' => '70', 'is_hard_rule' => true, 'error_message' => 'Property occupancy below 70%'],
                    ['rule_name' => 'cashflow_history', 'rule_type' => 'threshold', 'parameter' => 'cashflow_history_months', 'operator' => 'gte', 'value' => '3', 'is_hard_rule' => true, 'error_message' => 'Insufficient cashflow history'],
                ]);
            }
        }

        return $partner;
    }

    private function setTransactionMode(int $ownerId): void
    {
        try {
            $affected = DB::table('owner_packages')->where('user_id', $ownerId)->update(['pricing_model' => 'transaction']);
            if ($affected === 0 && \Illuminate\Support\Facades\Schema::hasTable('packages')) {
                DB::table('owner_packages')->insert([
                    'user_id' => $ownerId, 'package_id' => DB::table('packages')->value('id') ?? 1, 'name' => 'Demo Transaction',
                    'start_date' => now(), 'end_date' => now()->addYear(), 'status' => 1, 'pricing_model' => 'transaction',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            $this->command->warn('Could not set transaction mode: ' . $e->getMessage());
        }
    }

    private function resolveProperty(User $owner): Property
    {
        return Property::where('owner_user_id', $owner->id)->first()
            ?? Property::forceCreate(['owner_user_id' => $owner->id, 'property_type' => 1, 'name' => 'Demo Apartments', 'number_of_unit' => 4, 'status' => ACTIVE]);
    }

    private function resolveUnit(Property $property): PropertyUnit
    {
        return PropertyUnit::where('property_id', $property->id)->first()
            ?? PropertyUnit::forceCreate(['property_id' => $property->id, 'unit_name' => 'Unit A1', 'bedroom' => 2, 'bath' => 1, 'kitchen' => 1, 'general_rent' => 100000]);
    }

    /** Active tenant + 3 paid rent invoices so occupancy/cashflow underwriting has real data. */
    private function seedCashflow(User $owner, Property $property, PropertyUnit $unit): void
    {
        try {
            $tenantUser = User::firstOrCreate(['email' => 'tenant@centresidence.test'], [
                'first_name' => 'Demo', 'last_name' => 'Tenant', 'password' => Hash::make('123456'),
                'role' => USER_ROLE_TENANT, 'status' => ACTIVE, 'contact_number' => '254700333444',
                'owner_user_id' => $owner->id, 'email_verified_at' => now(),
            ]);
            $tenant = Tenant::where('user_id', $tenantUser->id)->first()
                ?? Tenant::forceCreate([
                    'user_id' => $tenantUser->id, 'job' => 'Engineer', 'family_member' => 2,
                    'property_id' => $property->id, 'unit_id' => $unit->id, 'status' => TENANT_STATUS_ACTIVE, 'general_rent' => 100000,
                ]);

            for ($i = 0; $i < 3; $i++) {
                $month = Carbon::now()->subMonths($i);
                $no = 'DEMO-RENT-' . $property->id . '-' . $month->format('Ym');
                if (DB::table('invoices')->where('invoice_no', $no)->exists()) {
                    continue;
                }
                DB::table('invoices')->insert([
                    'tenant_id' => $tenant->id, 'property_id' => $property->id, 'property_unit_id' => $unit->id,
                    'owner_user_id' => $owner->id, 'name' => 'Rent', 'invoice_no' => $no, 'month' => $month->format('F'),
                    'due_date' => $month->copy()->day(5)->toDateString(), 'amount' => 100000, 'status' => INVOICE_STATUS_PAID,
                    'created_at' => $month, 'updated_at' => $month,
                ]);
            }
        } catch (\Throwable $e) {
            $this->command->warn('Could not seed cashflow data: ' . $e->getMessage());
        }
    }

    private function seedPropertyModule(User $owner, Property $property, Module $water, Gateway $gateway): PropertyModule
    {
        $pm = PropertyModule::firstOrCreate(
            ['property_id' => $property->id, 'module_id' => $water->id],
            ['owner_id' => $owner->id, 'active_meter_count' => 4, 'billing_model' => 'subscription', 'status' => 'active', 'activated_at' => now()->subMonths(2)]
        );
        if (! $pm->tokenConfig) {
            $pm->tokenConfig()->create(['token_unit_label' => 'Litres', 'units_per_kes' => 5, 'centresidence_commission_per_token_unit' => 0.02]);
        }
        if ($pm->devices()->count() === 0) {
            foreach (range(1, 4) as $i) {
                Device::create(['dev_eui' => 'DEMO-WM-' . $property->id . '-' . $i, 'property_module_id' => $pm->id, 'gateway_id' => $gateway->id, 'status' => 'active', 'is_simulated' => true, 'activated_at' => now()->subMonths(2)]);
            }
        }
        InfrastructureTopology::firstOrCreate(
            ['infrastructure_type' => 'gateway', 'infrastructure_id' => $gateway->id, 'owner_id' => $owner->id, 'property_id' => $property->id],
            ['allocation_percentage' => 100, 'billing_model' => 'per_active_device_uncapped', 'monthly_base_cost' => 5000, 'status' => 'active', 'effective_from' => now()->subMonths(2)->toDateString()]
        );

        return $pm;
    }

    private function seedSampleApplication(User $owner, Property $property, FinancePartner $partner, Module $water): void
    {
        if (FinanceApplication::where('owner_id', $owner->id)->where('finance_partner_id', $partner->id)->exists()) {
            return;
        }
        try {
            $product = FinancePartnerModule::where('finance_partner_id', $partner->id)->where('module_id', $water->id)->first();
            $catalogue = ModulePricingCatalogueItem::where('module_id', $water->id)->first();
            if (! $product || ! $catalogue) {
                return;
            }

            $service = app(FinanceApplicationService::class);
            $app = $service->createDraft([
                'owner_id' => $owner->id, 'property_id' => $property->id, 'module_id' => $water->id,
                'finance_partner_id' => $partner->id, 'finance_partner_module_id' => $product->id,
                'catalogue_item_id' => $catalogue->id, 'quantity' => 6, 'repayment_months' => 24,
            ]);

            try {
                $service->submit($app, app(CashflowService::class)->underwritingContext($app), $owner->id);
            } catch (\Throwable $e) {
                // Underwriting blocked (thin cashflow on the live DB) — leave it
                // reviewable anyway so the partner portal has a sample.
                $app->forceFill(['status' => 'submitted', 'submitted_at' => now()])->save();
            }
        } catch (\Throwable $e) {
            $this->command->warn('Could not seed sample application: ' . $e->getMessage());
        }
    }

    private function summary(FinancePartner $partner, ?User $owner): void
    {
        $this->command->info('── Centresidence demo seeded ──');
        $this->command->info('Finance partner login: partner@centresidence.test / 123456');
        if ($owner) {
            $this->command->info('Owner login (existing): ' . $owner->email . ' / 123456  → Financing in the sidebar');
        }
        $this->command->info('Admin login (existing): admin@gmail.com / 123456  → Centresidence in the sidebar');
    }
}
