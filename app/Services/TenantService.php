<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\FileManager;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\TenantDetails;
use App\Models\User;
use App\Jobs\SendTenantCredentialsJob;
use App\Services\SmsMail\MailService;
use App\Services\Sms\SmsCreditsService;
use Illuminate\Support\Str;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantService
{
    use ResponseTrait;

    public function getAll()
    {
        $data = Tenant::query()
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->leftJoin(DB::raw('(select tenant_id, SUM(amount) as due from invoices where status = 0 AND deleted_at IS NULL group By tenant_id) as inv'), ['inv.tenant_id' => 'tenants.id'])
            ->leftJoin(DB::raw('(select tenant_id, MAX(updated_at) as last_payment from invoices where status = 1 AND deleted_at IS NULL group By tenant_id) as inv_last'), ['inv_last.tenant_id' => 'tenants.id'])
            ->select(['tenants.*', 'inv.due', 'inv_last.last_payment', 'users.first_name', 'users.last_name', 'users.status as userStatus', 'users.contact_number', 'users.email', 'property_units.unit_name', 'properties.name as property_name'])
            ->where('tenants.owner_user_id', auth()->id())
            ->orderBy('properties.name', 'asc')  // Sort by property name first
            ->orderByRaw("REGEXP_REPLACE(property_units.unit_name, '[0-9]', '') ASC, CAST(REGEXP_REPLACE(property_units.unit_name, '[^0-9]', '') AS UNSIGNED) ASC")
            ->get();
            return $data?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }


    public function getActiveAll(Request $request = null)
    {
        $query = Tenant::query()
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->leftJoin(DB::raw('(select tenant_id, SUM(amount) as due from invoices where status = 0 AND deleted_at IS NULL group By tenant_id) as inv'), ['inv.tenant_id' => 'tenants.id'])
            ->leftJoin(DB::raw('(select tenant_id, MAX(updated_at) as last_payment from invoices where status = 1 AND deleted_at IS NULL group By tenant_id) as inv_last'), ['inv_last.tenant_id' => 'tenants.id'])
            ->select(['tenants.*', 'inv.due', 'inv_last.last_payment', 'users.first_name', 'users.last_name', 'users.status as userStatus', 'users.contact_number', 'users.email', 'users.must_change_password', 'users.last_login_at', 'property_units.unit_name', 'properties.name as property_name'])
            ->where('tenants.owner_user_id', auth()->id())
            ->where('tenants.status', TENANT_STATUS_ACTIVE);

        if ($request) {
            if ($request->filled('search')) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('users.first_name', 'like', "%$term%")
                    ->orWhere('users.last_name', 'like', "%$term%")
                    ->orWhere('users.email', 'like', "%$term%")
                    ->orWhere('properties.name', 'like', "%$term%")
                    ->orWhere('property_units.unit_name', 'like', "%$term%");
                });
            }
            if ($request->filled('property_id') && $request->property_id != '0') {
                $query->where('tenants.property_id', $request->property_id);
            }
            if ($request->filled('unit_id') && $request->unit_id != '0') {
                $query->where('tenants.unit_id', $request->unit_id);
            }
        }

        return $query
            ->orderBy('properties.name', 'asc')
            ->orderByRaw("REGEXP_REPLACE(property_units.unit_name, '[0-9]', '') ASC, CAST(REGEXP_REPLACE(property_units.unit_name, '[^0-9]', '') AS UNSIGNED) ASC")
            ->paginate(50);
    }

    public function getAllData()
    {
        $tenants = Tenant::query()
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->leftJoin(DB::raw('(select tenant_id, SUM(amount) as due from invoices where status = 0 AND deleted_at IS NULL group By tenant_id) as inv'), ['inv.tenant_id' => 'tenants.id'])
            ->leftJoin(DB::raw('(select tenant_id, MAX(updated_at) as last_payment from invoices where status = 1 AND deleted_at IS NULL group By tenant_id) as inv_last'), ['inv_last.tenant_id' => 'tenants.id'])
            ->select(['tenants.*', 'inv.due', 'inv_last.last_payment', 'users.first_name', 'users.last_name', 'users.status as userStatus', 'users.contact_number', 'users.email', 'property_units.unit_name', 'properties.name as property_name'])
            ->where('tenants.owner_user_id', auth()->id());

        return datatables($tenants)
            ->addIndexColumn()
            ->addColumn('name', function ($tenant) {
                return '<div class="tenants-tbl-info-object d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img src="' . $tenant->image . '"
                            class="rounded-circle avatar-md tbl-user-image"
                            alt="">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6>' . $tenant->first_name . ' ' . $tenant->last_name . '</h6>
                            <p class="font-13">' . $tenant->email . '</p>
                        </div>
                    </div>';
            })
            ->addColumn('property', function ($tenant) {
                return $tenant->property_name;
            })
            ->addColumn('contact', function ($tenant) {
                return $tenant->contact_number;
            })
            ->addColumn('last_payment', function ($tenant) {
                return $tenant->last_payment ? date('Y-m-d', strtotime($tenant->last_payment)) : 'N/A';
            })
            ->addColumn('due', function ($tenant) {
                return currencyPrice($tenant->due);
            })
            ->addColumn('general_rent', function ($tenant) {
                return currencyPrice($tenant->general_rent);
            })
            ->addColumn('unit', function ($tenant) {
                return $tenant->unit_name;
            })
            ->addColumn('status', function ($tenant) {
                $html = '';
                if ($tenant->userStatus == USER_STATUS_DELETED) {
                    $html = ' <div class="status-btn status-btn-orange font-13 radius-4">' . __('Deleted') . '</div>';
                } else {
                    if ($tenant->status == TENANT_STATUS_ACTIVE) {
                        $html = ' <div class="status-btn status-btn-green font-13 radius-4">' . __('Active') . '</div>';
                    } elseif ($tenant->status == TENANT_STATUS_INACTIVE) {
                        $html = ' <div class="status-btn status-btn-orange font-13 radius-4">' . __('Inactive') . '</div>';
                    } elseif ($tenant->status == TENANT_STATUS_DRAFT) {
                        $html = ' <div class="status-btn status-btn-blue font-13 radius-4">' . __('Draft') . '</div>';
                    } elseif ($tenant->status == TENANT_STATUS_CLOSE) {
                        $html = ' <div class="status-btn status-btn-red font-13 radius-4">' . __('Closed') . '</div>';
                    }
                }
                return $html;
            })
            ->addColumn('action', function ($tenant) {
                return '<div class="tbl-action-btns d-inline-flex">
                        <a href="' . route('owner.tenant.details', [$tenant->id, 'tab' => 'profile']) . '" class="p-1 tbl-action-btn" title="' . __('Edit') . '"><span class="iconify" data-icon="carbon:view-filled"></span></a>
                    </div>';
            })
            ->rawColumns(['name', 'property', 'status', 'action'])
            ->make(true);
    }

    public function getAllHistoryData()
    {
        $tenants = Tenant::query()
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->select(['tenants.*', 'users.first_name', 'users.last_name', 'users.status as userStatus', 'users.contact_number', 'users.email', 'property_units.unit_name', 'properties.name as property_name'])
            ->where('tenants.owner_user_id', auth()->id());
    
        return datatables($tenants)
            ->addIndexColumn()
            ->addColumn('name', function ($tenant) {
                return '
                    <div style="display:flex;align-items:center;gap:12px;">
                        <img src="' . e($tenant->image) . '"
                            alt=""
                            style="width:36px;height:36px;border-radius:8px;border:2px solid #e0eaf5;object-fit:cover;flex-shrink:0;">
                        <div>
                            <div style="font-size:14px;font-weight:600;color:#185FA5;line-height:1.3;">
                                ' . e($tenant->first_name) . ' ' . e($tenant->last_name) . '
                            </div>
                            <div style="font-size:12px;color:#6b7280;margin-top:1px;">
                                ' . e($tenant->email) . '
                            </div>
                        </div>
                    </div>';
            })
            ->addColumn('property', function ($tenant) {
                return '<span style="font-size:13px;color:#374151;">' . e($tenant->property_name) . '</span>';
            })
            ->addColumn('unit', function ($tenant) {
                return '<span style="font-size:13px;color:#374151;">' . e($tenant->unit_name) . '</span>';
            })
            ->addColumn('status', function ($tenant) {
                if ($tenant->userStatus == USER_STATUS_DELETED) {
                    return '<span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 9px;border-radius:99px;background:#F3F4F6;color:#6b7280;border:0.5px solid #e5e7eb;white-space:nowrap;">'
                        . __('Deleted') . '</span>';
                }
    
                switch ($tenant->status) {
                    case TENANT_STATUS_ACTIVE:
                        return '<span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 9px;border-radius:99px;background:#E1F5EE;color:#0F6E56;white-space:nowrap;">'
                            . __('Active') . '</span>';
    
                    case TENANT_STATUS_INACTIVE:
                        return '<span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 9px;border-radius:99px;background:#FAEEDA;color:#854F0B;border:0.5px solid #F5D9A8;white-space:nowrap;">'
                            . __('Inactive') . '</span>';
    
                    case TENANT_STATUS_DRAFT:
                        return '<span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 9px;border-radius:99px;background:#E6F1FB;color:#0C447C;border:0.5px solid #B5D4F4;white-space:nowrap;">'
                            . __('Draft') . '</span>';
    
                    case TENANT_STATUS_CLOSE:
                        return '<span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 9px;border-radius:99px;background:#FAECE7;color:#993C1D;white-space:nowrap;">'
                            . __('Closed') . '</span>';
    
                    default:
                        return '—';
                }
            })
            ->addColumn('action', function ($tenant) {
                return '
                    <a href="' . route('owner.tenant.details', [$tenant->id, 'tab' => 'profile']) . '"
                        title="' . __('View') . '"
                        style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;background:#f0f4fa;color:#185FA5;border:0.5px solid #e5e7eb;transition:all .13s;text-decoration:none;"
                        onmouseover="this.style.background=\'#185FA5\';this.style.color=\'#fff\';"
                        onmouseout="this.style.background=\'#f0f4fa\';this.style.color=\'#185FA5\';">
                        <span class="iconify" data-icon="carbon:view-filled" style="font-size:13px;"></span>
                    </a>';
            })
            ->rawColumns(['name', 'property', 'unit', 'status', 'action'])
            ->make(true);
    }

    public function getById($id)
    {
        $data = Tenant::where('owner_user_id', auth()->id())->findOrFail($id);
        return $data?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function getDetailsById($id)
    {
        if (auth()->user()->role == USER_ROLE_OWNER) {
            $userId = auth()->id();
        } else {
            $userId = auth()->user()->owner_user_id;
        }
        $data = Tenant::query()
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->leftJoin('tenant_details', 'tenants.id', '=', 'tenant_details.tenant_id')
            ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
            ->leftJoin('property_details', 'properties.id', '=', 'property_details.property_id')
            ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->select(['tenants.*', 'users.first_name', 'users.last_name', 'users.contact_number', 'users.email', 'property_units.unit_name', 'properties.name as property_name', 'property_details.address as property_address', 'tenant_details.previous_address', 'tenant_details.previous_country_id', 'tenant_details.previous_state_id', 'tenant_details.previous_city_id', 'tenant_details.previous_zip_code', 'tenant_details.permanent_address', 'tenant_details.permanent_country_id', 'tenant_details.permanent_state_id', 'tenant_details.permanent_city_id', 'tenant_details.permanent_zip_code'])
            ->where('tenants.owner_user_id', $userId)
            ->where('tenants.id', $id)
            ->firstOrFail();
        return $data?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function closingStatusHistory($id)
    {
        return Tenant::query()->where('owner_user_id', auth()->id())->where('status', TENANT_STATUS_CLOSE)->findOrFail($id);
    }

    public function getPaymentByTenantId($id)
    {
        $data = Invoice::query()
            ->join('tenants', 'invoices.property_unit_id', '=', 'tenants.unit_id')
            ->select('invoices.*')
            ->where('tenants.owner_user_id', auth()->id())
            ->where('tenants.id', $id)
            ->get();
        return $data?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function payment($id)
    {
        $invoices = Invoice::query()
            ->leftJoin('properties', 'invoices.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'invoices.property_unit_id', '=', 'property_units.id')
            ->select('invoices.*', 'property_units.unit_name', 'properties.name as property_name')
            ->where('invoices.owner_user_id', auth()->id())
            ->where('invoices.tenant_id', $id);

        return datatables($invoices)
            ->addIndexColumn()
            ->addColumn('created_at', function ($invoice) {
                return $invoice->created_at->format('Y-m-d h:m');
            })
            ->addColumn('invoice', function ($invoice) {
                return $invoice->created_at->format('Y') . $invoice->id;
            })
            ->addColumn('amount', function ($invoice) {
                return $invoice->amount ?? 0;
            })
            ->addColumn('status', function ($invoice) {
                if ($invoice->status == INVOICE_STATUS_PAID) {
                    $html =  '<div class="status-btn status-btn-green font-13 radius-4">' . __('Paid') . '</div>';
                } elseif ($invoice->status == INVOICE_STATUS_PENDING) {
                    $html = '<div class="d-flex justify-content-start">';
                    $html .=  '<div class="status-btn status-btn-orange font-13 radius-4">' . __('Unpaid') . '</div>';
                    $html .= '<button type="button" class="p-1 tbl-action-btn payStatus" data-detailsurl="' . route('owner.invoice.details', $invoice->id) . '" title="Payment Status Change"><span class="iconify" data-icon="ic:outline-payments"></span></button>';
                    $html .= '</div>';
                } else {
                    $html = '<div class="d-flex justify-content-start">';
                    $html =  '<div class="status-btn status-btn-red font-13 radius-4">' . __('Due') . '</div>';
                    $html .= '<button type="button" class="p-1 tbl-action-btn payStatus" data-detailsurl="' . route('owner.invoice.details', $invoice->id) . '" title="Payment Status Change"><span class="iconify" data-icon="ic:outline-payments"></span></button>';
                    $html .= '</div>';
                }
                return $html;
            })
            ->rawColumns(['amount', 'created_at', 'status', 'invoice'])
            ->make(true);
    }

    public function paymentDue($id)
    {
        return Invoice::query()
            ->select('invoices.*')
            ->join('tenants', 'invoices.property_unit_id', '=', 'tenants.unit_id')
            ->whereNot('invoices.status', INVOICE_STATUS_PAID)
            ->where('tenants.owner_user_id', auth()->id())
            ->where('tenants.id', $id)
            ->get();
    }

    public function documentDestroy($id)
    {
        $document = FileManager::where('origin_type', 'App\Models\Tenant')->where('id', $id)->first();
        $tenantExists = Tenant::where('id', $document->origin_id)->where('owner_user_id', auth()->id())->exists();
        if (!is_null($document) && $tenantExists) {
            $document->delete();
        } else {
            $message = __("Document not found");
            return $this->error([], $message);
        }
        return $this->success([], __(DELETED_SUCCESSFULLY));
    }

    public function step1(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $tenant = Tenant::where('owner_user_id', auth()->id())->findOrFail($request->id);
                $user = User::where('owner_user_id', auth()->id())->findOrFail($tenant->user_id);
                $details = TenantDetails::firstOrNew(['tenant_id' => $tenant->id]);
            } else {
                if (!getOwnerLimit(RULES_TENANT) > 0) {
                    throw new Exception(__('Your Tenant Limit is Finished. Choose or Renew Package Plan'));
                }
                $user = new User();
                $tenant = new Tenant();
                $details = new TenantDetails();
            }

            // User
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->contact_number = $request->contact_number;

            // New tenants get a SYSTEM-generated password and must set their own on first login.
            // The plaintext is captured here (the only moment we hold it) so we can send it, then
            // it's never recoverable again. An owner-supplied reset on edit also forces a change.
            $plainPassword = null;
            if ($id == '') {
                $plainPassword = Str::random(10);
                $user->password = Hash::make($plainPassword);
                $user->must_change_password = 1;
            } elseif ($request->password) {
                $plainPassword = $request->password;
                $user->password = Hash::make($request->password);
                $user->must_change_password = 1;
            }
            $user->role = USER_ROLE_TENANT;
            $user->status = ACTIVE;
            $user->owner_user_id = auth()->id();
            $user->save();

            // Tenant
            $tenant->user_id = $user->id;
            $tenant->owner_user_id = auth()->id();
            $tenant->job = $request->job;
            $tenant->age = $request->age;
            $tenant->family_member = $request->family_member;
            // Only a BRAND-NEW tenancy starts as a draft. step1 also runs on edit, so setting this
            // unconditionally used to revert a live ACTIVE tenant to DRAFT the moment their info was
            // edited — making them vanish from "All Tenants" (they kept their unit, invoices, and
            // login, so it looked like data loss). Editing must never downgrade an existing status.
            if ($id == '') {
                $tenant->status = TENANT_STATUS_DRAFT;
            }
            $tenant->save();

            // Detail
            $details->tenant_id = $tenant->id;
            $details->permanent_country_id = $request->permanent_country_id;
            $details->permanent_state_id = $request->permanent_state_id;
            $details->permanent_city_id = $request->permanent_city_id;
            $details->permanent_address = $request->permanent_address;
            $details->permanent_zip_code = $request->permanent_zip_code;
            $details->previous_country_id = $request->previous_country_id;
            $details->previous_state_id = $request->previous_state_id;
            $details->previous_city_id = $request->previous_city_id;
            $details->previous_address = $request->previous_address;
            $details->previous_zip_code = $request->previous_zip_code;
            $details->save();

            /*File Manager Call upload for Thumbnail Image*/
            if ($request->image) {
                $new_file = FileManager::where('origin_type', 'App\Models\Tenant')->where('id', $tenant->image_id)->first();
                if ($new_file) {
                    $new_file->removeFile();
                    $upload = $new_file->updateUpload($new_file->id, 'Tenant', $request->image);
                } else {
                    $new_file = new FileManager();
                    $upload = $new_file->upload('Tenant', $request->image);
                }

                if ($upload['status']) {
                    $tenant->image_id = $upload['file']->id;
                    $tenant->save();

                    $upload['file']->origin_type = "App\Models\Tenant";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            /*End*/

            DB::commit();
            session(['tenant_id' => $tenant->id]);

            // Deliver login credentials over email + SMS. This is the tenant's only way in, so it
            // is NOT gated behind the global send_email_status toggle — a password they can't
            // receive is a locked-out tenant. Fires only on first creation (when we hold the
            // plaintext) and only when they actually have a channel to reach.
            if ($id == '' && $plainPassword && ($user->email || $user->contact_number)) {
                SendTenantCredentialsJob::dispatch($user->id, $plainPassword, 'both');
                SmsCreditsService::warnIfExhausted(auth()->id(), ! empty($user->contact_number));
            }
            // DEV ONLY: keep the generated password in the session so the owner can copy it from
            // the tenant's profile to test the login flow. Never stored in the DB; never in prod.
            if ($id == '' && $plainPassword && config('app.debug')) {
                session()->put('dev_pw_' . $tenant->id, $plainPassword);
            }
            $data = $tenant;
            $data->step = 'nextStep1';
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success($data, $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    // The old cross-owner rating lookup (broken — queried non-existent columns and leaked a
    // single owner's raw record) has been removed. Screening is now the aggregated, consented
    // TenantCreditProfile (App\Services\Screening) — no per-landlord declarations.


    public function step2(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!empty($request->unit_id)) {
                $unitExist = Tenant::where('owner_user_id', auth()->id())->where('unit_id', $request->unit_id)->where('status', TENANT_STATUS_ACTIVE)->whereNot('id', $request->id)->first();
                if (!is_null($unitExist)) {
                    throw new Exception(__('Unit already Used'));
                }
            }
            $tenant = Tenant::where('owner_user_id', auth()->id())->findOrFail($request->id);
            $tenant->property_id = $request->property_id;
            $tenant->unit_id = $request->unit_id;
            $tenant->lease_start_date = $request->lease_start_date;
            $tenant->lease_end_date = $request->lease_end_date;
            $tenant->general_rent = $request->general_rent;
            $tenant->security_deposit_type = $request->security_deposit_type;
            $tenant->security_deposit = $request->security_deposit;
            $tenant->late_fee_type = $request->late_fee_type;
            $tenant->late_fee = $request->late_fee;
            $tenant->incident_receipt = $request->incident_receipt;
            $tenant->due_date = $request->due_date;
            $tenant->save();

            DB::commit();
            $data = $tenant;
            $data->step = 'nextStep2';
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success($data, $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function step3(Request $request)
    {
        DB::beginTransaction();
        try {
            $tenant = Tenant::where('owner_user_id', auth()->id())->findOrFail($request->id);
            $tenant->status = TENANT_STATUS_ACTIVE;
            $tenant->save();
            /*File Manager Call upload*/
            if ($request->file('file')) {
                $new_file = new FileManager();
                $upload = $new_file->upload('Tenant', $request->file);

                if ($upload['status']) {
                    $upload['file']->origin_id = $tenant->id;
                    $upload['file']->origin_type = "App\Models\Tenant";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            /*End*/
            DB::commit();

            // Plug-and-play: create the unit's auto-recurring rent setting the moment the tenant
            // goes active (immediate; the generate:invoice cron backfill is the safety net).
            app(\App\Services\InvoiceRecurringService::class)->ensureUnitRecurringSetting($tenant);

            $data = $tenant;
            $data->step = 'lastStep';;
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success($data, $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function closeHistoryStore($request, $id)
    {
        DB::beginTransaction();
        try {
            $tenant = Tenant::where('owner_user_id', auth()->id())->findOrFail($id);
            $tenant->status = TENANT_STATUS_CLOSE;
            $tenant->close_refund_amount = $request->close_refund_amount;
            $tenant->close_charge = $request->close_charge;
            $tenant->close_date = $request->close_date;
            $tenant->close_reason = $request->close_reason;
            $tenant->rent_payment_rating = $request->rent_payment_rating;
            $tenant->discipline_rating = $request->discipline_rating;
            $tenant->closing_remarks = $request->closing_remarks;
            $tenant->save();

            DB::commit();
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function delete($request)
    {
        DB::beginTransaction();
        try {
            $tenant = Tenant::where('owner_user_id', auth()->id())->findOrFail($request->tenant_id);
            if ($tenant->user->email != $request->email) {
                throw new Exception(__('Tenant Not Found'));
            }
            $userId = $tenant->user_id;

            // The tenancy is disposable, the PERSON is not. Preserve the tenant's identity + credit
            // profile (the Global Tenant ID moat) whenever they have any financial footprint — we
            // only ever remove the tenancy, never a person's rental payment history. Only a bare
            // mis-entry (single tenancy, zero invoices) may take the person account with it.
            $personTenantIds = Tenant::withTrashed()->where('user_id', $userId)->pluck('id');
            $hasHistory = $personTenantIds->count() > 1
                || Invoice::whereIn('tenant_id', $personTenantIds)->exists();

            // Remove the tenancy + its owner-scoped detail (soft delete — invoices stay linked and
            // still feed the person's profile via withTrashed()).
            TenantDetails::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();

            if (! $hasHistory) {
                User::where('id', $userId)->delete();
            }

            DB::commit();
            $message = __(DELETED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    /**
     * Discard a half-built DRAFT tenant (the owner started adding one but decided not to proceed —
     * e.g. after screening showed they're unsuitable). Scoped strictly to the owner's own DRAFT
     * rows so it can never touch a live tenancy. A draft has no financial history, so its person
     * account is removed with it — this is exactly the cleanup that stops drafts piling up.
     */
    public function discardDraft($id)
    {
        DB::beginTransaction();
        try {
            $tenant = Tenant::where('owner_user_id', auth()->id())
                ->where('status', TENANT_STATUS_DRAFT)
                ->findOrFail($id);

            $userId = $tenant->user_id;

            // Safety: only remove the person if this draft is their entire footprint (no other
            // tenancy, no invoices) — mirrors delete()'s person-preserving rule.
            $personTenantIds = Tenant::withTrashed()->where('user_id', $userId)->pluck('id');
            $hasHistory = $personTenantIds->count() > 1
                || Invoice::whereIn('tenant_id', $personTenantIds)->exists();

            TenantDetails::where('tenant_id', $tenant->id)->delete();
            $tenant->forceDelete();

            if (! $hasHistory) {
                User::where('id', $userId)->forceDelete();
            }

            DB::commit();
            return $this->success([], __('Draft discarded.'));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }

    /**
     * Reset ONE tenant's password and re-send their login details (email + SMS). The original
     * password is hashed and unrecoverable, so "resend" necessarily means "reset + send new".
     * Forces a first-login change on the new password.
     */
    public function resendLogin($tenantId): array
    {
        $tenant = Tenant::where('owner_user_id', auth()->id())->with('user')->findOrFail($tenantId);
        $user = $tenant->user;
        if (! $user) {
            return ['ok' => false, 'message' => __('Tenant account not found.')];
        }
        if (empty($user->email) && empty($user->contact_number)) {
            return ['ok' => false, 'message' => __('This tenant has no email or phone number to send login details to.')];
        }

        // Pre-flight the SMS balance so the owner learns NOW, on this page, that a text
        // can't go out — instead of seeing "sent" and finding it blocked later on the SMS
        // page (the deduction happens in a queued job). Email is never gated by SMS credits.
        $hasEmail  = ! empty($user->email);
        $hasPhone  = ! empty($user->contact_number);
        $smsFunded = SmsCreditsService::hasCredits(auth()->id());

        // SMS is the only way to reach this tenant and there are no credits: don't burn a
        // password reset that can't be delivered — ask the owner to top up first.
        if ($hasPhone && ! $hasEmail && ! $smsFunded) {
            return ['ok' => false, 'message' => __(':name can only be reached by SMS, and your SMS credit balance is 0 — nothing was sent. Top up SMS credits, then resend.', ['name' => $user->first_name])];
        }

        $plain = Str::random(10);
        $user->password = Hash::make($plain);
        $user->must_change_password = 1;
        $user->save();

        SendTenantCredentialsJob::dispatch($user->id, $plain, 'both');

        // DEV ONLY: persist for copy from the profile header (never DB, never prod).
        if (config('app.debug')) {
            session()->put('dev_pw_' . $tenant->id, $plain);
        }

        // The email is on its way, but warn if the SMS half couldn't go (no credits), so the
        // owner isn't misled into thinking the text was delivered.
        $warning = ($hasPhone && ! $smsFunded)
            ? __('Login details were emailed to :name, but your SMS credit balance is 0, so the text was not sent. Top up SMS credits to also deliver it by SMS.', ['name' => $user->first_name])
            : null;

        return [
            'ok'       => true,
            'message'  => __('New login details are on their way to :name.', ['name' => $user->first_name]),
            'warning'  => $warning,
            'password' => $plain, // surfaced to the owner ONLY in debug (see controller) for local testing
        ];
    }

    /**
     * Send login details to every ACTIVE tenant of this owner who hasn't onboarded yet (never set
     * their own password → must_change_password is still on). Never touches a tenant who already
     * signed in and set their own password. Each send regenerates a password + queues the invite.
     */
    public function bulkResendLogins(): array
    {
        $tenants = Tenant::where('owner_user_id', auth()->id())
            ->where('status', TENANT_STATUS_ACTIVE)
            ->whereHas('user', fn ($q) => $q->where('must_change_password', 1))
            ->with('user')
            ->get();

        $balance     = SmsCreditsService::balance(auth()->id());
        $count       = 0;
        $smsAttempts = 0; // how many of these need a text (have a phone)
        foreach ($tenants as $tenant) {
            $user = $tenant->user;
            if (! $user || (empty($user->email) && empty($user->contact_number))) {
                continue;
            }
            $plain = Str::random(10);
            $user->password = Hash::make($plain);
            $user->must_change_password = 1;
            $user->save();

            SendTenantCredentialsJob::dispatch($user->id, $plain, 'both');
            if (! empty($user->contact_number)) {
                $smsAttempts++;
            }
            $count++;
        }

        // If credits can't cover every text, warn the owner now: the shortfall is paused
        // (retryable from the SMS page after a top-up); tenants with an email still get it.
        $shortfall = max(0, $smsAttempts - $balance);
        $warning = $shortfall > 0
            ? __('Your SMS balance (:balance) can\'t cover all :attempts tenants who need a text — :short were paused for lack of credits. Top up, then retry the paused messages from the SMS Credits page. Tenants with an email still received it there.', [
                'balance' => $balance, 'attempts' => $smsAttempts, 'short' => $shortfall,
            ])
            : null;

        return ['ok' => true, 'count' => $count, 'warning' => $warning];
    }

    public function updateUnitTenant($unit)
    {
        $tenants = Tenant::where('unit_id', $unit->id)->get();

        if ($tenants->isEmpty()) {
            return;
        }
        
        if ($unit->rent_type == PROPERTY_UNIT_RENT_TYPE_MONTHLY) {
            $due_date = $unit->monthly_due_day;
        } elseif ($unit->rent_type == PROPERTY_UNIT_RENT_TYPE_YEARLY) {
            $due_date = $unit->yearly_due_day;
        } elseif ($unit->rent_type == PROPERTY_UNIT_RENT_TYPE_CUSTOM) {
            $due_date = $unit->lease_payment_due_date;
        }

        foreach ($tenants as $tenant) {
            $tenant->update([
                'property_id' => $unit->property_id,
                'unit_id' => $unit->id,
                'rent_type' => $unit->rent_type,
                'due_date' => $due_date,
                'lease_start_date' => $unit->lease_start_date,
                'lease_end_date' => $unit->lease_end_date,
                'general_rent' => $unit->general_rent,
                'security_deposit_type' => $unit->security_deposit_type,
                'security_deposit' => $unit->security_deposit,
                'late_fee_type' => $unit->late_fee_type,
                'late_fee' => $unit->late_fee,
                'incident_receipt' => $unit->incident_receipt,
            ]);
        }
    }

}

       