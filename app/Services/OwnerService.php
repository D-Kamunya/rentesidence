<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class OwnerService
{
    public function getAllData($request)
    {
        $owners = Owner::query()
            ->join('users as owner_user', 'owners.user_id', '=', 'owner_user.id')
            ->leftJoin('affiliates', 'owners.affiliate_id', '=', 'affiliates.id')
            ->leftJoin('users as affiliate_user', 'affiliates.user_id', '=', 'affiliate_user.id')
            ->select(
                'owners.id as owner_id',
                'owner_user.first_name as owner_first_name',
                'owner_user.last_name as owner_last_name',
                'owner_user.email as owner_email',
                'owner_user.contact_number as owner_contact_number',
                'affiliate_user.first_name as affiliate_first_name',
                'affiliate_user.last_name as affiliate_last_name'
            )
            ->orderBy('owners.id', 'desc')
            ->get();

        return datatables($owners)
            ->addIndexColumn()
            ->addColumn('name', function ($owner) {
                return $owner->owner_first_name . ' ' . $owner->owner_last_name;
            })
            ->addColumn('email', function ($owner) {
                return $owner->owner_email;
            })
            ->addColumn('contact_number', function ($owner) {
                return $owner->owner_contact_number;
            })
            ->addColumn('affiliate', function ($owner) {
                if ($owner->affiliate_first_name) {
                    return $owner->affiliate_first_name . ' ' . $owner->affiliate_last_name;
                } else {
                    return '';
                }
            })
            ->addColumn('status', function ($package) {
                if ($package->status == ACTIVE) {
                    return '<div class="status-btn status-btn-green font-13 radius-4">Active</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Deactivate</div>';
                }
            })
            ->rawColumns(['name', 'status', 'contact_number', 'trail', 'action'])
            ->make(true);
    }

    public function getAll()
    {
        $owners = Owner::query()
            ->join('users', 'owners.user_id', '=', 'users.id')
            ->select('users.*')
            ->orderBy('owners.id', 'desc')
            ->get();
        return $owners->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function topSearch($request)
    {
        $data['status'] = false;
        $data['tenants'] =  Tenant::query()
            ->where('tenants.owner_user_id', auth()->id())
            ->join('users', 'tenants.user_id', '=', 'users.id')
            ->where(DB::raw("concat(users.first_name, ' ', users.last_name)"), 'LIKE', "%" . $request->keyword . "%")
            ->select(DB::raw("tenants.id"), DB::raw("concat(users.first_name, ' ', users.last_name) as name"))
            ->get();

        $data['properties'] =  Property::query()
            ->where('owner_user_id', auth()->id())
            ->where('name', 'LIKE', '%' . $request->keyword . '%')
            ->select('id', 'name')
            ->get();

        $data['invoices'] =  Invoice::query()
            ->where('owner_user_id', auth()->id())
            ->where('invoice_no', 'LIKE', '%' . $request->keyword . '%')
            ->select('id', 'invoice_no')
            ->get();

        if (count($data['tenants']) > 0 || count($data['properties']) > 0 || count($data['invoices']) > 0) {
            $data['status'] = true;
        }
        return $data;
    }
}
