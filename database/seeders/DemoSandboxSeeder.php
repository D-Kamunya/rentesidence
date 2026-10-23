<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Package;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OwnerOnboardingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The read-only DEMO SANDBOX owner — a realistic, self-healing demo account the guided demo runs
 * against so every prospect sees the product at its best (rent collected over M-Pesa + receipt,
 * occupied/vacant units, billing, reports). Marketplace + financing (our differentiators) get the
 * heaviest SELL in the guided tour itself; here we seed the core so the dashboard is alive.
 *
 * Idempotent + only-once: keyed on the demo owner's fixed email and a sentinel. Tenants are
 * DIRECT-INSERTED (no credential SMS/email side-effects); every contact is a .demo address /
 * sentinel phone so it can never collide with or message a real person. The demo owner's user id
 * is stored in `demo_sandbox_owner_user_id` for the read-only demo-mode entry to resolve.
 *
 *   php artisan db:seed --class=Database\\Seeders\\DemoSandboxSeeder
 */
class DemoSandboxSeeder extends Seeder
{
    private const SENTINEL     = 'demo_sandbox_v1';
    private const OWNER_EMAIL  = 'demo.owner@centresidence.demo';

    public function run(): void
    {
        // Already built? Make sure the pointer option is set, then stop.
        $existing = User::where('email', self::OWNER_EMAIL)->first();
        if ($existing) {
            setOption('demo_sandbox_owner_user_id', (string) $existing->id);
            setOption(self::SENTINEL, '1');
            return;
        }

        DB::transaction(function () {
            // ---- Owner (reuse the canonical onboarding SSOT) ----
            $created = app(OwnerOnboardingService::class)->create([
                'first_name' => 'James',
                'last_name'  => 'Mwangi',
                'email'      => self::OWNER_EMAIL,
                'phone'      => '+254700000100',
            ]);
            /** @var User $owner */
            $owner   = $created['user'];
            $ownerId = (int) $owner->id;

            // Demo account: no forced password reset, and a long-lived full-feature window.
            $owner->must_change_password = 0;
            $owner->save();
            $trial = Package::where(['is_trail' => ACTIVE])->first();
            if ($trial) {
                setUserPackage($ownerId, $trial, 1200, 1); // ~century: demo never expires
            }

            // ---- Property + units ----
            $property = new Property();
            $property->owner_user_id  = $ownerId;
            $property->property_type  = PROPERTY_TYPE_OWN;
            $property->name           = 'Garden View Apartments';
            $property->number_of_unit = 3;
            $property->description    = 'A well-kept 3-unit block in Kilimani managed end-to-end on Centresidence.';
            $property->unit_type      = PROPERTY_UNIT_TYPE_MULTIPLE;
            $property->status         = PROPERTY_STATUS_ACTIVE;
            $property->save();

            $unitDefs = [
                ['name' => 'Unit A1', 'rent' => 25000, 'bed' => 2, 'bath' => 1],
                ['name' => 'Unit A2', 'rent' => 25000, 'bed' => 2, 'bath' => 1],
                ['name' => 'Unit B1', 'rent' => 30000, 'bed' => 3, 'bath' => 2], // left vacant (shows listing/vacancy)
            ];
            $units = [];
            foreach ($unitDefs as $u) {
                $unit = new PropertyUnit();
                $unit->property_id           = $property->id;
                $unit->unit_name             = $u['name'];
                $unit->bedroom               = $u['bed'];
                $unit->bath                  = $u['bath'];
                $unit->kitchen               = 1;
                $unit->general_rent          = $u['rent'];
                $unit->security_deposit      = $u['rent'];
                $unit->security_deposit_type = TYPE_FIXED;
                $unit->late_fee              = 500;
                $unit->late_fee_type         = TYPE_FIXED;
                $unit->incident_receipt      = 0;
                $unit->rent_type             = PROPERTY_UNIT_RENT_TYPE_MONTHLY;
                $unit->monthly_due_day       = 5;
                $unit->lease_start_date      = Carbon::now()->subMonths(6)->toDateString();
                $unit->save();
                $units[] = $unit;
            }

            // ---- Tenants (A1, A2 occupied; B1 vacant) — direct insert, no credential comms ----
            $tenantDefs = [
                ['first' => 'Aisha',  'last' => 'Hassan', 'email' => 'demo.tenant.aisha@centresidence.demo',  'phone' => '+254700000101', 'unit' => 0],
                ['first' => 'Peter',  'last' => 'Otieno', 'email' => 'demo.tenant.peter@centresidence.demo',  'phone' => '+254700000102', 'unit' => 1],
            ];
            $tenants = [];
            foreach ($tenantDefs as $t) {
                $tUser = new User();
                $tUser->first_name          = $t['first'];
                $tUser->last_name           = $t['last'];
                $tUser->email               = $t['email'];
                $tUser->contact_number      = $t['phone'];
                $tUser->password            = bcrypt(Str::random(16));
                $tUser->status              = USER_STATUS_ACTIVE;
                $tUser->role                = USER_ROLE_TENANT;
                $tUser->owner_user_id       = $ownerId;
                $tUser->email_verified_at   = now();
                $tUser->must_change_password = 0;
                $tUser->save();

                $unit = $units[$t['unit']];
                $tenant = new Tenant();
                $tenant->user_id               = $tUser->id;
                $tenant->owner_user_id         = $ownerId;
                $tenant->property_id           = $property->id;
                $tenant->unit_id               = $unit->id;
                $tenant->rent_type             = RENT_TYPE_MONTHLY;
                $tenant->due_date              = 5;
                $tenant->lease_start_date      = Carbon::now()->subMonths(5)->toDateString();
                $tenant->general_rent          = $unit->general_rent;
                $tenant->security_deposit      = $unit->security_deposit;
                $tenant->security_deposit_type = TYPE_FIXED;
                $tenant->late_fee              = $unit->late_fee;
                $tenant->late_fee_type         = TYPE_FIXED;
                $tenant->incident_receipt      = 0;
                $tenant->status                = TENANT_STATUS_ACTIVE;
                $tenant->close_refund_amount   = 0;
                $tenant->close_charge          = 0;
                $tenant->save();

                $tenants[] = ['tenant' => $tenant, 'user' => $tUser, 'unit' => $unit];
            }

            // ---- Invoices: Aisha has a PAID last-month invoice (+ receipt) + a current pending;
            //      Peter has a current pending. Populates billing + reports + the receipt moment. ----
            $seq = 1;
            foreach ($tenants as $i => $row) {
                $tenant = $row['tenant'];
                $unit   = $row['unit'];
                $rent   = (float) $unit->general_rent;

                if ($i === 0) {
                    // Paid last month — status=PAID is the source of truth for the receipt AND for
                    // owner earnings/reports (ReportService reads invoices.status = PAID, not a
                    // separate payments table), so no transaction row is needed.
                    $this->makeInvoice($ownerId, $property->id, $unit->id, $tenant->id, $rent, $seq++, Carbon::now()->subMonth(), INVOICE_STATUS_PAID);
                }

                // Current month pending for everyone
                $this->makeInvoice($ownerId, $property->id, $unit->id, $tenant->id, $rent, $seq++, Carbon::now(), INVOICE_STATUS_PENDING);
            }

            setOption('demo_sandbox_owner_user_id', (string) $ownerId);
            setOption(self::SENTINEL, '1');
        });
    }

    private function makeInvoice(int $ownerId, int $propertyId, int $unitId, int $tenantId, float $rent, int $seq, Carbon $period, int $status): Invoice
    {
        $invoice = new Invoice();
        $invoice->tenant_id        = $tenantId;
        $invoice->owner_user_id    = $ownerId;
        $invoice->property_id      = $propertyId;
        $invoice->property_unit_id = $unitId;
        $invoice->name             = 'Rent';
        $invoice->invoice_no       = 'DEMO-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        $invoice->month            = $period->format('F');
        $invoice->billing_period   = $period->copy()->startOfMonth()->toDateString();
        $invoice->due_date         = $period->copy()->startOfMonth()->addDays(4)->toDateString();
        $invoice->amount           = $rent;
        $invoice->tax_amount       = 0;
        $invoice->status           = $status;
        $invoice->payment_token    = (string) Str::uuid();
        $invoice->save();

        $item = new InvoiceItem();
        $item->invoice_id  = $invoice->id;
        $item->amount      = $rent;
        $item->tax_amount  = 0;
        $item->description = 'Monthly rent — ' . $period->format('F Y');
        $item->save();

        return $invoice;
    }
}
