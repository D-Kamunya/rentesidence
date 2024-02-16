<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\OwnerPackage;
use App\Models\Package;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class PackageService
{
    use ResponseTrait;
    public function getAllData($request)
    {
        $packages = Package::query();

        return datatables($packages)
            ->addColumn('name', function ($package) {
                return $package->name;
            })
            ->addColumn('monthly_price', function ($package) {
                return currencyPrice($package->monthly_price);
            })
            ->addColumn('yearly_price', function ($package) {
                return currencyPrice($package->yearly_price);
            })
            ->addColumn('per_monthly_price', function ($package) {
                if ($package->type == PACKAGE_TYPE_PROPERTY) {
                    $packageType = __('Property');
                } elseif ($package->type == PACKAGE_TYPE_UNIT) {
                    $packageType = __('Unit');
                } elseif ($package->type == PACKAGE_TYPE_TENANT) {
                    $packageType = __('Tenant');
                } else {
                    $packageType = __('Default');
                }
                return currencyPrice($package->per_monthly_price) . '/' . $packageType;
            })
            ->addColumn('per_yearly_price', function ($package) {
                if ($package->type == PACKAGE_TYPE_PROPERTY) {
                    $packageType = __('Property');
                } elseif ($package->type == PACKAGE_TYPE_UNIT) {
                    $packageType = __('Unit');
                } elseif ($package->type == PACKAGE_TYPE_TENANT) {
                    $packageType = __('Tenant');
                } else {
                    $packageType = __('Default');
                }
                return currencyPrice($package->per_yearly_price) . '/' . $packageType;
            })
            ->addColumn('status', function ($package) {
                if ($package->status == ACTIVE) {
                    return '<div class="status-btn status-btn-green font-13 radius-4">Active</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Deactivate</div>';
                }
            })
            ->addColumn('trail', function ($package) {
                if ($package->is_trail == ACTIVE) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Yes</div>';
                } else {
                    return '<div class="status-btn status-btn-red font-13 radius-4">No</div>';
                }
            })
            ->addColumn('action', function ($package) {
                return '<div class="tbl-action-btns d-inline-flex">
                    <button type="button" class="p-1 tbl-action-btn edit" data-id="' . $package->id . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>
                    <button onclick="deleteItem(\'' . route('admin.packages.destroy', $package->id) . '\', \'allDataTable\')" class="p-1 tbl-action-btn"   title="' . __('Delete') . '"><span class="iconify" data-icon="ep:delete-filled"></span></button>
                </div>';
            })
            ->rawColumns(['name', 'status', 'trail', 'action'])
            ->make(true);
    }

    public function getAll()
    {
        return Package::query()->get();
    }

    public function getActiveAll()
    {
        return Package::where('status', ACTIVE)->where('is_trail', '!=', ACTIVE)->get();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $package = Package::findOrFail($request->id);
            } else {
                $package = new Package();
            }

            // Slug exists
            $slug = getSlug($request->name);
            $slugExist = Package::where('slug', $slug)->whereNot('id', $request->id)->exists();
            if ($slugExist) {
                throw new Exception(__('Name Already Exist'));
            }

            $package->name = $request->name;
            $package->slug = $slug;

            $package->type = $request->pricing_type;
            $package->per_monthly_price = $request->per_monthly_price;
            $package->per_yearly_price = $request->per_yearly_price;

            $package->max_property = $request->max_property ?? 0;
            $package->max_unit = $request->max_unit ?? 0;
            $package->max_tenant = $request->max_tenant ?? 0;
            $package->max_maintainer = $request->maintainer_limit_type == 1 ? $request->max_maintainer : -1;
            $package->max_invoice = $request->invoice_limit_type == 1 ? $request->max_invoice : -1;
            $package->max_auto_invoice = $request->auto_invoice_limit_type == 1 ? $request->max_auto_invoice : -1;
            $package->notice_support = $request->notice_support;
            $package->ticket_support = $request->ticket_support;
            $package->status = $request->status;
            $package->is_trail = $request->is_trail;
            $package->is_default = $request->is_default;
            $package->monthly_price = $request->monthly_price;
            $package->yearly_price = $request->yearly_price;
            $package->save();

            // update if status changed
            // if (is_null(Package::where(['status' => ACTIVE])->first())) {
            //     Package::first()->update(['status' => ACTIVE]);
            // }

            // // update if trail changed
            // if (is_null(Package::where(['is_trail' => ACTIVE, 'status' => ACTIVE])->first())) {
            //     Package::where(['status' => ACTIVE])->first()->update(['is_trail' => ACTIVE]);
            // }

            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([], $message);
        }
    }

    public function getInfo($id)
    {
        return Package::findOrFail($id);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            if (Package::where('status', ACTIVE)->count() > 1) {
                Package::findOrFail($id)->delete();

                // update if trail changed
                if (is_null(Package::where(['is_trail' => ACTIVE, 'status' => ACTIVE])->first())) {
                    Package::where(['status' => ACTIVE])->first()->update(['is_trail' => ACTIVE]);
                }

                DB::commit();
                $message = __(DELETED_SUCCESSFULLY);
                return $this->success([], $message);
            } else {
                $message = __("Trial package can not be deleted");
                return $this->error([], $message);
            }
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([], $message);
        }
    }

    public function getUserPackagesData($request)
    {
        $ownerPackages = OwnerPackage::query()
            ->join('users', 'owner_packages.user_id', '=', 'users.id')
            ->join('packages', 'owner_packages.package_id', '=', 'packages.id')
            ->join('subscription_orders', 'owner_packages.order_id', '=', 'subscription_orders.id')
            ->join('gateways', 'subscription_orders.gateway_id', '=', 'gateways.id')
            ->select('owner_packages.*', 'users.first_name', 'users.last_name', 'users.email', 'packages.name as packageName', 'subscription_orders.payment_status', 'gateways.title as gatewaysName')
            ->orderBy('owner_packages.id', 'desc');

        return datatables($ownerPackages)
            ->addIndexColumn()
            ->addColumn('user_name', function ($ownerPackage) {
                return $ownerPackage->first_name . ' ' . $ownerPackage->last_name;
            })
            ->addColumn('package_name', function ($ownerPackage) {
                return $ownerPackage->packageName;
            })
            ->addColumn('gateway', function ($ownerPackage) {
                return $ownerPackage->gatewaysName;
            })
            ->addColumn('payment_status', function ($ownerPackage) {
                if ($ownerPackage->payment_status == ORDER_PAYMENT_STATUS_PAID) {
                    return '<div class="status-btn status-btn-green font-13 radius-4">Paid</div>';
                } elseif ($ownerPackage->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                    return '<div class="status-btn status-btn-red font-13 radius-4">Pending</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Cancelled</div>';
                }
            })->addColumn('start_date', function ($ownerPackage) {
                return date('Y-m-d', strtotime($ownerPackage->start_date));
            })->addColumn('end_date', function ($ownerPackage) {
                return date('Y-m-d', strtotime($ownerPackage->end_date));
            })->addColumn('status', function ($ownerPackage) {
                if ($ownerPackage->status == ACTIVE) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Active</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Deactivate</div>';
                }
            })->addColumn('action', function ($ownerPackage) {
                return '<div class="tbl-action-btns d-inline-flex">
                    <button type="button" class="p-1 tbl-action-btn edit" data-id="' . $ownerPackage->id . '" title="Edit"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>
                </div>';
            })
            ->rawColumns(['user_name', 'package_name', 'payment_status', 'start_date', 'end_date', 'status', 'action'])
            ->make(true);
    }

    public function assignPackage($request)
    {
        DB::beginTransaction();
        try {
            $package = Package::findOrFail($request->package_id);
            $ownerUser = User::where('role', USER_ROLE_OWNER)->findOrFail($request->user_id);
            $adminUser = User::where('role', USER_ROLE_ADMIN)->first();

            $gateway = Gateway::where(['owner_user_id' => $adminUser->id, 'slug' => 'cash'])->firstOrFail();
            $currency = Currency::where('current_currency', 'on')->first()->currency_code;

            $price = 0;
            $duration = 0;
            if ($request->duration_type == PACKAGE_DURATION_TYPE_MONTHLY) {
                $price = $package->monthly_price;
                $duration = 30;
            } else {
                $price = $package->yearly_price;
                $duration = 365;
            }

            $order = SubscriptionOrder::create([
                'user_id' => $ownerUser->id,
                'package_id' => $package->id,
                'quantity' => $request->quantity ?? 1,
                'package_type' => $request->package_type ?? PACKAGE_TYPE_UNIT,
                'payment_status' => ORDER_PAYMENT_STATUS_PAID,
                'transaction_id' => str_replace("-", "", uuid_create(UUID_TYPE_RANDOM)),
                'amount' => $price,
                'system_currency' => $currency,
                'gateway_id' => $gateway->id,
                'duration_type' => $request->duration_type,
                'gateway_currency' => $currency,
                'conversion_rate' => 1,
                'subtotal' => $price,
                'total' => $price,
                'transaction_amount' => $price
            ]);

            setUserPackage($order->user_id, $package, $duration, $order->quantity, $order->id);
            DB::commit();
            $title = __("You have a new invoice");
            $body = __("Package Assign Successfully");
            addNotification($title, $body, null, null, $ownerUser->id, $adminUser->id);
            return $this->success([], __(ASSIGNED_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([], $message);
        }
    }
}
