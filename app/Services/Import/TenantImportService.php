<?php

namespace App\Services\Import;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceType;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\TenantDetails;
use App\Models\User;
use App\Services\InvoiceRecurringService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Bulk tenant/unit import — parsing, validation and dry-run PREVIEW (no writes). One
 * denormalised row = one tenant-in-a-unit; the row upserts a property, a unit, a tenant
 * (login) and the tenancy, plus an optional carried-over opening balance. This class owns
 * the column contract (also used to build the downloadable template) and turns an uploaded
 * CSV into a row-by-row preview so nothing is written until the owner confirms.
 *
 * The actual writes live in Stage 2 (a queued job) and reuse validateRows() first.
 */
class TenantImportService
{
    /** Allowed rent types (maps to property_units.rent_type). */
    public const RENT_TYPES = ['monthly', 'yearly', 'custom'];

    /**
     * The column contract: key => [label, required, type, hint]. `key` is the canonical
     * header; a header also matches on its human label (case-insensitive).
     */
    public function columns(): array
    {
        return [
            'property_name'     => ['label' => 'Property',        'required' => true,  'type' => 'string', 'hint' => 'Building/estate name. Reused if it already exists.'],
            'unit_name'         => ['label' => 'Unit',            'required' => true,  'type' => 'string', 'hint' => 'Unit/door number, unique within the property.'],
            'bedrooms'          => ['label' => 'Bedrooms',        'required' => false, 'type' => 'int',    'hint' => 'Whole number (new units only).'],
            'bathrooms'         => ['label' => 'Bathrooms',       'required' => false, 'type' => 'int',    'hint' => 'Whole number (new units only).'],
            'rent_amount'       => ['label' => 'Monthly Rent',    'required' => true,  'type' => 'money',  'hint' => 'Rent per period, numbers only.'],
            'rent_type'         => ['label' => 'Rent Type',       'required' => false, 'type' => 'enum',   'hint' => 'monthly, yearly or custom (default monthly).'],
            'due_day'           => ['label' => 'Rent Due Day',    'required' => false, 'type' => 'day',    'hint' => 'Day of month rent is due, 1–31.'],
            'deposit_amount'    => ['label' => 'Deposit',         'required' => false, 'type' => 'money',  'hint' => 'Security deposit, numbers only.'],
            'tenant_first_name' => ['label' => 'First Name',      'required' => true,  'type' => 'string', 'hint' => "Tenant's first name."],
            'tenant_last_name'  => ['label' => 'Last Name',       'required' => false, 'type' => 'string', 'hint' => "Tenant's last name."],
            'tenant_phone'      => ['label' => 'Phone',           'required' => true,  'type' => 'phone',  'hint' => 'M-Pesa/contact number, e.g. 07XXXXXXXX.'],
            'tenant_email'      => ['label' => 'Email',           'required' => false, 'type' => 'email',  'hint' => 'Recommended — used to send the login invite.'],
            'lease_start_date'  => ['label' => 'Lease Start',     'required' => false, 'type' => 'date',   'hint' => 'YYYY-MM-DD.'],
            'lease_end_date'    => ['label' => 'Lease End',       'required' => false, 'type' => 'date',   'hint' => 'YYYY-MM-DD, on/after lease start.'],
            'opening_balance'   => ['label' => 'Opening Balance', 'required' => false, 'type' => 'money',  'hint' => 'Arrears carried over from your old system (creates an unpaid invoice).'],
        ];
    }

    /** One example data row for the downloadable template. */
    public function templateSampleRow(): array
    {
        return [
            'property_name' => 'Riverside Apartments', 'unit_name' => 'A1',
            'bedrooms' => '2', 'bathrooms' => '1',
            'rent_amount' => '25000', 'rent_type' => 'monthly', 'due_day' => '5',
            'deposit_amount' => '25000',
            'tenant_first_name' => 'Jane', 'tenant_last_name' => 'Doe',
            'tenant_phone' => '0712345678', 'tenant_email' => 'jane@example.com',
            'lease_start_date' => '2026-01-01', 'lease_end_date' => '2026-12-31',
            'opening_balance' => '0',
        ];
    }

    /**
     * Parse a CSV file into associative rows keyed by column. Returns
     * ['rows' => [[key => value]], 'unmatchedHeaders' => [...], 'missingRequiredHeaders' => [...]].
     * Throws on an unreadable/empty file.
     */
    public function parseCsv(string $absolutePath): array
    {
        if (! is_readable($absolutePath)) {
            throw new \RuntimeException(__('The uploaded file could not be read.'));
        }

        $handle = fopen($absolutePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException(__('The uploaded file could not be opened.'));
        }

        try {
            $header = fgetcsv($handle);
            if ($header === false || $header === null) {
                throw new \RuntimeException(__('The file is empty.'));
            }

            // Strip a UTF-8 BOM from the first header cell if present.
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }

            [$indexToKey, $unmatched] = $this->mapHeaders($header);

            $rows = [];
            while (($line = fgetcsv($handle)) !== false) {
                // Skip completely blank lines.
                if (count(array_filter($line, fn ($c) => trim((string) $c) !== '')) === 0) {
                    continue;
                }
                $row = [];
                foreach ($indexToKey as $i => $key) {
                    $row[$key] = isset($line[$i]) ? trim((string) $line[$i]) : '';
                }
                $rows[] = $row;
            }

            $mappedKeys = array_values($indexToKey);
            $missingRequired = [];
            foreach ($this->columns() as $key => $meta) {
                if (! empty($meta['required']) && ! in_array($key, $mappedKeys, true)) {
                    $missingRequired[] = $meta['label'];
                }
            }

            return [
                'rows'                   => $rows,
                'unmatchedHeaders'       => $unmatched,
                'missingRequiredHeaders' => $missingRequired,
            ];
        } finally {
            fclose($handle);
        }
    }

    /** Map raw header cells to column keys (by key or by human label, case-insensitive). */
    private function mapHeaders(array $header): array
    {
        $byNormalized = [];
        foreach ($this->columns() as $key => $meta) {
            $byNormalized[$this->normalizeHeader($key)]           = $key;
            $byNormalized[$this->normalizeHeader($meta['label'])] = $key;
        }

        $indexToKey = [];
        $unmatched  = [];
        foreach ($header as $i => $cell) {
            $norm = $this->normalizeHeader((string) $cell);
            if ($norm === '') {
                continue;
            }
            if (isset($byNormalized[$norm])) {
                $indexToKey[$i] = $byNormalized[$norm];
            } else {
                $unmatched[] = trim((string) $cell);
            }
        }

        return [$indexToKey, $unmatched];
    }

    private function normalizeHeader(string $h): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($h)));
    }

    /**
     * Validate parsed rows (no writes). Returns:
     *   ['rows' => [['line'=>int,'data'=>[],'errors'=>[],'action'=>'create|update|error']],
     *    'valid' => int, 'errors' => int, 'summary' => [...]].
     *
     * `action` is a best-effort read-only classification so the preview shows what each row
     * would do; the committing job re-derives it authoritatively under a row lock.
     */
    public function validateRows(int $ownerUserId, array $rows): array
    {
        $out = [];
        $valid = 0; $errors = 0;

        // Preload the owner's existing properties/units/tenants ONCE so per-row classification
        // is in-memory — a thousands-row file must not fire thousands of queries in the preview.
        $maps = $this->preload($ownerUserId);

        // Within-file bookkeeping to catch duplicates before we ever touch the DB.
        $seenPhones = [];      // normalizedPhone => firstLine
        $seenUnits  = [];      // property|unit  => firstLine

        // Rough new-entity counters for the plan-limit summary + invite pre-check.
        $newProps = []; $newUnits = []; $newTenants = 0;
        $smsCand = 0; $emailCand = 0;

        foreach ($rows as $i => $row) {
            $line = $i + 2; // +1 header, +1 to 1-index
            $rowErrors = [];

            $get = fn ($k) => trim((string) ($row[$k] ?? ''));

            // Required fields.
            foreach ($this->columns() as $key => $meta) {
                if (! empty($meta['required']) && $get($key) === '') {
                    $rowErrors[] = __(':field is required.', ['field' => $meta['label']]);
                }
            }

            // Typed validation for present values.
            $rent = $get('rent_amount');
            if ($rent !== '' && (! is_numeric($rent) || (float) $rent < 0)) {
                $rowErrors[] = __('Monthly Rent must be a number of 0 or more.');
            }
            foreach (['deposit_amount' => 'Deposit', 'opening_balance' => 'Opening Balance'] as $k => $label) {
                $v = $get($k);
                if ($v !== '' && (! is_numeric($v) || (float) $v < 0)) {
                    $rowErrors[] = __(':field must be a number of 0 or more.', ['field' => __($label)]);
                }
            }

            $rentType = strtolower($get('rent_type'));
            if ($rentType !== '' && ! in_array($rentType, self::RENT_TYPES, true)) {
                $rowErrors[] = __('Rent Type must be monthly, yearly or custom.');
            }

            $dueDay = $get('due_day');
            if ($dueDay !== '' && (! ctype_digit($dueDay) || (int) $dueDay < 1 || (int) $dueDay > 31)) {
                $rowErrors[] = __('Rent Due Day must be a whole number from 1 to 31.');
            }

            foreach (['bedrooms' => 'Bedrooms', 'bathrooms' => 'Bathrooms'] as $k => $label) {
                $v = $get($k);
                if ($v !== '' && (! ctype_digit($v))) {
                    $rowErrors[] = __(':field must be a whole number.', ['field' => __($label)]);
                }
            }

            $start = $this->parseDate($get('lease_start_date'));
            $end   = $this->parseDate($get('lease_end_date'));
            if ($get('lease_start_date') !== '' && $start === null) {
                $rowErrors[] = __('Lease Start is not a valid date (use YYYY-MM-DD).');
            }
            if ($get('lease_end_date') !== '' && $end === null) {
                $rowErrors[] = __('Lease End is not a valid date (use YYYY-MM-DD).');
            }
            if ($start && $end && $end->lt($start)) {
                $rowErrors[] = __('Lease End must be on or after Lease Start.');
            }

            $email = $get('tenant_email');
            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = __('Email is not a valid address.');
            }

            $phoneRaw  = $get('tenant_phone');
            $phone     = $this->normalizePhone($phoneRaw);
            if ($phoneRaw !== '' && ! $this->isValidKenyanMobile($phone)) {
                $rowErrors[] = __('Phone is not a valid Kenyan mobile number (use 07XX XXX XXX or 01XX XXX XXX).');
            }

            // Within-file duplicates.
            if ($this->isValidKenyanMobile($phone)) {
                if (isset($seenPhones[$phone])) {
                    $rowErrors[] = __('Duplicate phone — same tenant also on row :line.', ['line' => $seenPhones[$phone]]);
                } else {
                    $seenPhones[$phone] = $line;
                }
            }
            $unitKey = strtolower($get('property_name') . '|' . $get('unit_name'));
            if ($get('property_name') !== '' && $get('unit_name') !== '') {
                if (isset($seenUnits[$unitKey])) {
                    $rowErrors[] = __('Duplicate unit — same property/unit also on row :line.', ['line' => $seenUnits[$unitKey]]);
                } else {
                    $seenUnits[$unitKey] = $line;
                }
            }

            // Read-only classification against the preloaded maps (the job re-checks under a lock).
            $action = 'create';
            if (empty($rowErrors)) {
                $action = $this->classifyRow($maps, $get('property_name'), $get('unit_name'), $phone, $rowErrors, $newProps, $newUnits, $newTenants);
            } else {
                $action = 'error';
            }

            if (empty($rowErrors)) {
                $valid++;
                // Invite candidates = NEW tenants we'd create who have a phone / email. Drives
                // the SMS pre-check (don't start an import that can't afford its SMS invites).
                if ($action === 'create') {
                    if ($this->isValidKenyanMobile($phone)) { $smsCand++; }
                    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) { $emailCand++; }
                }
            } else {
                $errors++;
                $action = 'error';
            }

            $out[] = ['line' => $line, 'data' => $row, 'errors' => $rowErrors, 'action' => $action];
        }

        return [
            'rows'    => $out,
            'valid'   => $valid,
            'errors'  => $errors,
            'summary' => $this->summarize($ownerUserId, count($rows), $valid, $errors, count($newProps), count($newUnits), $newTenants, $smsCand, $emailCand),
        ];
    }

    /**
     * Preload the owner's existing properties/units/tenants into lookup maps so row
     * classification is pure in-memory. A handful of queries regardless of file size.
     *
     * Returns: propByName[lower name]=>propId, unitByKey[propId|lower unit]=>unitId,
     * phoneToTenantId[phone]=>tenantId, occupantByUnitId[unitId]=>activeTenantId.
     */
    public function preload(int $ownerUserId): array
    {
        $propByName = [];
        $propIds    = [];
        foreach (Property::where('owner_user_id', $ownerUserId)->get(['id', 'name']) as $p) {
            $propByName[strtolower(trim($p->name))] = $p->id;
            $propIds[] = $p->id;
        }

        $unitByKey = [];
        if ($propIds) {
            foreach (PropertyUnit::whereIn('property_id', $propIds)->get(['id', 'property_id', 'unit_name']) as $u) {
                $unitByKey[$u->property_id . '|' . strtolower(trim($u->unit_name))] = $u->id;
            }
        }

        // Map tenant phones (from the linked user) and active-unit occupancy in one pass.
        $userPhoneById = User::where('owner_user_id', $ownerUserId)
            ->where('role', USER_ROLE_TENANT)
            ->pluck('contact_number', 'id'); // userId => phone

        $phoneToTenantId  = [];
        $occupantByUnitId = [];
        foreach (Tenant::where('owner_user_id', $ownerUserId)->get(['id', 'user_id', 'unit_id', 'status']) as $t) {
            $phone = (string) ($userPhoneById[$t->user_id] ?? '');
            if ($phone !== '') {
                $phoneToTenantId[$this->normalizePhone($phone)] = $t->id;
            }
            if ($t->unit_id && (int) $t->status === TENANT_STATUS_ACTIVE) {
                $occupantByUnitId[$t->unit_id] = $t->id;
            }
        }

        return compact('propByName', 'unitByKey', 'phoneToTenantId', 'occupantByUnitId');
    }

    /**
     * Best-effort read-only classification of a valid row against the preloaded maps:
     * 'create' (new tenant), 'update' (tenant already exists for this owner by phone), or an
     * error when the unit is already held by a DIFFERENT active tenant. Tallies new entities.
     */
    private function classifyRow(array $maps, string $propertyName, string $unitName, string $phone, array &$rowErrors, array &$newProps, array &$newUnits, int &$newTenants): string
    {
        $propKey = strtolower(trim($propertyName));
        $propId  = $maps['propByName'][$propKey] ?? null;
        if (! $propId) {
            $newProps[$propKey] = true;
        }

        $unitId = $propId ? ($maps['unitByKey'][$propId . '|' . strtolower(trim($unitName))] ?? null) : null;
        if (! $unitId) {
            $newUnits[$propKey . '|' . strtolower(trim($unitName))] = true;
        }

        $existingTenantId = $phone !== '' ? ($maps['phoneToTenantId'][$phone] ?? null) : null;

        // Unit already occupied by a DIFFERENT active tenant → hard error (can't double-let).
        if ($unitId) {
            $occupantId = $maps['occupantByUnitId'][$unitId] ?? null;
            if ($occupantId && $occupantId !== $existingTenantId) {
                $rowErrors[] = __('That unit already has an active tenant. Close them first, or use a different unit.');
                return 'error';
            }
        }

        if ($existingTenantId) {
            return 'update';
        }
        $newTenants++;
        return 'create';
    }

    private function summarize(int $ownerUserId, int $total, int $valid, int $errors, int $newProps, int $newUnits, int $newTenants, int $smsCand = 0, int $emailCand = 0): array
    {
        // Plan-limit awareness: warn (don't block here) when the import would exceed the
        // owner's remaining tenant / property / unit allowances — so a limit-based skip is
        // foreseeable in the preview, not a surprise at commit. The job enforces it per row.
        $warnings = [];
        foreach ([
            [RULES_PROPERTY, $newProps,   __('new propert(ies)'), __('property slot(s)')],
            [RULES_UNIT,     $newUnits,   __('new units'),        __('unit slot(s)')],
            [RULES_TENANT,   $newTenants, __('new tenants'),      __('tenant slot(s)')],
        ] as [$rule, $adding, $thing, $slot]) {
            $left = $this->limitFor($rule, $ownerUserId);
            if ($left !== null && $adding > $left) {
                $warnings[] = __('This import adds :n :thing but your plan has :left :slot left — :skip will be skipped.', [
                    'n' => $adding, 'thing' => $thing, 'left' => $left, 'slot' => $slot, 'skip' => $adding - $left,
                ]);
            }
        }

        return [
            'total'         => $total,
            'valid'         => $valid,
            'errors'        => $errors,
            'new_props'     => $newProps,
            'new_units'     => $newUnits,
            'new_tenants'   => $newTenants,
            'sms_invites'   => $smsCand,   // new tenants with a phone  → SMS invite candidates
            'email_invites' => $emailCand, // new tenants with an email → email invite candidates
            'warnings'      => $warnings,
        ];
    }


    /**
     * Remaining plan allowances for the owner (null = unlimited). The job seeds counters from
     * this and decrements as it creates NEW entities, so an import can never exceed the plan.
     */
    public function resolveLimits(int $ownerUserId): array
    {
        return [
            'tenant'   => $this->limitFor(RULES_TENANT, $ownerUserId),
            'property' => $this->limitFor(RULES_PROPERTY, $ownerUserId),
            'unit'     => $this->limitFor(RULES_UNIT, $ownerUserId),
        ];
    }

    private function limitFor(int $rule, int $ownerUserId): ?int
    {
        if (! function_exists('getOwnerLimit')) {
            return null;
        }
        try {
            $v = getOwnerLimit($rule, $ownerUserId);
        } catch (\Throwable $e) {
            return null; // limit lookup unavailable → treat as unlimited (don't block)
        }
        if ($v === true || ! is_numeric($v)) {
            return null; // no SaaS layer / unlimited
        }
        $v = (int) $v;
        return $v >= PHP_INT_MAX ? null : max(0, $v);
    }

    /**
     * Write ONE validated row: upsert property → unit → tenant(login) → tenancy, ensure the
     * recurring rent setting, and create a carried-over opening-balance invoice. Idempotent
     * (re-running upserts the same records) and self-contained in a transaction, so a row
     * either fully lands or not at all. Plan limits are enforced against $limits (by ref).
     *
     * @return array{action:string, errors:array, invite?:array}
     */
    public function importRow(int $ownerUserId, array $row, array &$limits, InvoiceRecurringService $recurring): array
    {
        $get   = fn ($k) => trim((string) ($row[$k] ?? ''));
        $phone = $this->normalizePhone($get('tenant_phone'));

        return DB::transaction(function () use ($ownerUserId, $get, $phone, &$limits, $recurring) {
            // 1. Property (upsert by owner + name).
            $property = Property::where('owner_user_id', $ownerUserId)->where('name', $get('property_name'))->first();
            if (! $property) {
                if ($limits['property'] !== null && $limits['property'] <= 0) {
                    return ['action' => 'skipped', 'errors' => [__('Property limit reached on your plan — row skipped.')]];
                }
                $property = new Property();
                $property->property_type  = PROPERTY_TYPE_OWN;
                $property->owner_user_id  = $ownerUserId;
                $property->name           = $get('property_name');
                $property->number_of_unit = 0;
                $property->status         = PROPERTY_STATUS_ACTIVE;
                $property->save();

                $detail = new PropertyDetail();
                $detail->property_id = $property->id;
                $detail->lease_amount = 0;
                $detail->save();

                if ($limits['property'] !== null) { $limits['property']--; }
            }

            // 2. Unit (upsert by property + unit_name).
            $rentType = $this->rentTypeToInt($get('rent_type'));
            $unit = PropertyUnit::where('property_id', $property->id)->where('unit_name', $get('unit_name'))->first();
            $newUnit = false;
            if (! $unit) {
                if ($limits['unit'] !== null && $limits['unit'] <= 0) {
                    return ['action' => 'skipped', 'errors' => [__('Unit limit reached on your plan — row skipped.')]];
                }
                $unit = new PropertyUnit();
                $unit->property_id = $property->id;
                $unit->unit_name   = $get('unit_name');
                $newUnit = true;
            }
            if ($get('bedrooms') !== '')  { $unit->bedroom = (int) $get('bedrooms'); }
            if ($get('bathrooms') !== '') { $unit->bath = (int) $get('bathrooms'); }
            $unit->general_rent = (float) $get('rent_amount');
            if ($get('deposit_amount') !== '') {
                $unit->security_deposit = (float) $get('deposit_amount');
            }
            $unit->rent_type = $rentType;
            if ($rentType === PROPERTY_UNIT_RENT_TYPE_MONTHLY) {
                $unit->monthly_due_day = $get('due_day') !== '' ? (int) $get('due_day') : ((int) ($unit->monthly_due_day ?: 5));
            } elseif ($rentType === PROPERTY_UNIT_RENT_TYPE_YEARLY && $get('due_day') !== '') {
                $unit->yearly_due_day = (int) $get('due_day');
            }
            if (($s = $this->parseDate($get('lease_start_date')))) { $unit->lease_start_date = $s->toDateString(); }
            if (($e = $this->parseDate($get('lease_end_date'))))   { $unit->lease_end_date   = $e->toDateString(); }
            $unit->save();

            if ($newUnit && $limits['unit'] !== null) { $limits['unit']--; }
            // Keep the property's unit count in step.
            $property->number_of_unit = PropertyUnit::where('property_id', $property->id)->count();
            $property->save();

            // 3. Tenant login (upsert by owner + phone).
            $existingUser = $phone !== ''
                ? User::where('owner_user_id', $ownerUserId)->where('contact_number', $phone)->where('role', USER_ROLE_TENANT)->first()
                : null;
            $isNew = ! $existingUser;
            $plainPassword = null;

            if ($isNew) {
                if ($limits['tenant'] !== null && $limits['tenant'] <= 0) {
                    return ['action' => 'skipped', 'errors' => [__('Tenant limit reached on your plan — row skipped.')]];
                }
                $user = new User();
                $plainPassword  = Str::random(10);
                $user->password = Hash::make($plainPassword);
                $user->must_change_password = 1; // set their own on first login
            } else {
                $user = $existingUser;
            }
            $user->first_name     = $get('tenant_first_name');
            $user->last_name      = $get('tenant_last_name');
            if ($get('tenant_email') !== '') { $user->email = $get('tenant_email'); }
            $user->contact_number = $phone;
            $user->role           = USER_ROLE_TENANT;
            $user->status         = ACTIVE;
            $user->owner_user_id  = $ownerUserId;
            $user->save();

            if ($isNew && $limits['tenant'] !== null) { $limits['tenant']--; }

            // 4. Tenancy (upsert by owner + user).
            $tenant = Tenant::where('owner_user_id', $ownerUserId)->where('user_id', $user->id)->first() ?: new Tenant();
            $tenant->user_id          = $user->id;
            $tenant->owner_user_id    = $ownerUserId;
            $tenant->property_id      = $property->id;
            $tenant->unit_id          = $unit->id;
            $tenant->rent_type        = $rentType;
            $tenant->due_date         = $unit->monthly_due_day ?: null;
            $tenant->lease_start_date = $this->parseDate($get('lease_start_date'))?->toDateString();
            $tenant->lease_end_date   = $this->parseDate($get('lease_end_date'))?->toDateString();
            $tenant->general_rent     = (float) $get('rent_amount');
            $tenant->security_deposit = $get('deposit_amount') !== '' ? (float) $get('deposit_amount') : 0;
            $tenant->status           = TENANT_STATUS_ACTIVE;
            $tenant->save();

            TenantDetails::firstOrNew(['tenant_id' => $tenant->id])->save();

            // 5. Auto-recurring rent (same helper the real create flow uses).
            $recurring->ensureUnitRecurringSetting($tenant);

            // 6. Opening balance → an unpaid invoice for the carried-over arrears.
            $ob = $get('opening_balance');
            if ($ob !== '' && (float) $ob > 0) {
                $this->createOpeningBalanceInvoice($tenant, $property, $unit, (float) $ob);
            }

            return [
                'action' => $isNew ? 'created' : 'updated',
                'errors' => [],
                'invite' => [
                    'user_id'  => $user->id,
                    'email'    => $user->email,
                    'phone'    => $phone,
                    'password' => $plainPassword,   // only set for a newly-created login
                    'is_new'   => $isNew,
                ],
            ];
        });
    }

    private function rentTypeToInt(string $t): int
    {
        return match (strtolower(trim($t))) {
            'yearly' => PROPERTY_UNIT_RENT_TYPE_YEARLY,
            'custom' => PROPERTY_UNIT_RENT_TYPE_CUSTOM,
            default  => PROPERTY_UNIT_RENT_TYPE_MONTHLY,
        };
    }

    private function createOpeningBalanceInvoice(Tenant $tenant, Property $property, PropertyUnit $unit, float $amount): void
    {
        $type = InvoiceType::where('owner_user_id', $tenant->owner_user_id)->where('name', 'Opening Balance')->first();
        if (! $type) {
            $type = new InvoiceType();
            $type->owner_user_id = $tenant->owner_user_id;
            $type->name          = 'Opening Balance';
            $type->is_default    = 0;
            $type->tax           = 0;
            $type->status        = ACTIVE;
            $type->save();
        }

        $invoice = new Invoice();
        $invoice->name             = 'Opening Balance';
        $invoice->tenant_id        = $tenant->id;
        $invoice->owner_user_id    = $tenant->owner_user_id;
        $invoice->property_id      = $property->id;
        $invoice->property_unit_id = $unit->id;
        $invoice->month            = month((int) now()->format('n'));
        $invoice->billing_period   = now()->toDateString();
        $invoice->due_date         = now()->endOfDay();
        $invoice->amount           = $amount;
        $invoice->status           = INVOICE_STATUS_PENDING;
        $invoice->payment_token    = Str::uuid();
        $invoice->payment_token_expires_at = invoicePayTokenExpiry($invoice->due_date);
        $invoice->save();

        $item = new InvoiceItem();
        $item->invoice_id      = $invoice->id;
        $item->invoice_type_id = $type->id;
        $item->amount          = $amount;
        $item->description     = __('Balance carried over on import');
        $item->save();
    }

    public function parseDate(string $v): ?Carbon
    {
        $v = trim($v);
        if ($v === '') {
            return null;
        }
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** 07XX / 01XX / 7XXXXXXXX / +2547XX / 2547XX → 2547XXXXXXXX (digits only). */
    public function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            return '254' . substr($digits, 1);              // 07.. / 01.. → 2547.. / 2541..
        }
        // Bare local form without the leading 0 (e.g. 712345678 / 112345678).
        if (strlen($digits) === 9 && (str_starts_with($digits, '7') || str_starts_with($digits, '1'))) {
            return '254' . $digits;
        }
        return $digits;                                     // already 2547.. / 2541.. (or invalid)
    }

    /** Kenyan mobile in canonical 254 form: 2547XXXXXXXX (Safaricom) or 2541XXXXXXXX (Airtel/Telkom). */
    public function isValidKenyanMobile(string $normalized): bool
    {
        return (bool) preg_match('/^254(7|1)\d{8}$/', $normalized);
    }
}
