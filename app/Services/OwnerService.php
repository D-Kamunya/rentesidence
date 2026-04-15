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
                'owners.status as status',
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
            ->addColumn('status', function ($owner) {
                if ($owner->status == ACTIVE) {
                    return '
                        <form action="' . route('admin.owner.deactivate', $owner->owner_id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            <button type="submit" class="btn deactivate"
                                style="
                                    display: inline-flex; align-items: center; gap: 5px;
                                    background: #FDF4F1; border: 0.5px solid #F5C4B3;
                                    color: #712B13; border-radius: 99px;
                                    font-size: 11px; font-weight: 500;
                                    padding: 4px 11px; white-space: nowrap;
                                    cursor: pointer; line-height: 1.4;
                                ">
                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                Deactivate
                            </button>
                        </form>';
                }

                return '
                    <form action="' . route('admin.owner.activate', $owner->owner_id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        <button type="submit" class="btn activate"
                            style="
                                display: inline-flex; align-items: center; gap: 5px;
                                background: #F0F9F4; border: 0.5px solid #9FE1CB;
                                color: #085041; border-radius: 99px;
                                font-size: 11px; font-weight: 500;
                                padding: 4px 11px; white-space: nowrap;
                                cursor: pointer; line-height: 1.4;
                            ">
                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Activate
                        </button>
                    </form>';
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
