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
            ->join('users', 'owners.user_id', '=', 'users.id')
            ->leftJoin('domains', 'owners.user_id', '=', 'domains.owner_user_id')
            ->select('users.*', 'domains.domain')
            ->orderBy('owners.id', 'desc');

        return datatables($owners)
            ->addIndexColumn()
            ->addColumn('name', function ($owner) {
                return $owner->first_name . ' ' . $owner->last_name;
            })
            ->addColumn('email', function ($owner) {
                return $owner->email;
            })
            ->addColumn('contact_number', function ($owner) {
                return $owner->contact_number;
            })
            ->addColumn('domain', function ($owner) {
                if ($owner->domain) {
                    return $owner->domain;
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
            ->rawColumns(['name', 'status', 'trail', 'action'])
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
