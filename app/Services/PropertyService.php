<?php

namespace App\Services;

use App\Models\FileManager;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\PropertyImage;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\InvoiceRecurringSetting;
use App\Models\InvoiceRecurringSettingItem;
use App\Models\InvoiceType;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    use ResponseTrait;

    public function getAll($paginate=true)
    {
        $data = Property::query()
            ->leftJoin('tenants', ['properties.id' => 'tenants.property_id', 'tenants.status' => (DB::raw(TENANT_STATUS_ACTIVE))])
            ->leftJoin('users', function ($q) {
                $q->on('tenants.user_id', 'users.id')->whereNull('users.deleted_at');
            })
            ->selectRaw('properties.number_of_unit - (COUNT(users.id)) as available_unit,properties.*')
            ->groupBy('properties.id')
            ->where('properties.owner_user_id', auth()->id());

        if ($paginate){
            return $data->paginate(10);
        }else{
            return $data->get();
        }
    }

    public function getAllData()
    {
        $properties = $this->getAll();

        return datatables($properties)
            ->addIndexColumn()
            ->addColumn('image', function ($property) {
                return '<img src="' . $property->thumbnail_image . '"
                class="rounded-circle avatar-md tbl-user-image"
                alt="">';
            })
            ->addColumn('name', function ($property) {
                return $property->name;
            })
            ->addColumn('address', function ($property) {
                return $property->propertyDetail?->address;
            })
            ->addColumn('unit', function ($property) {
                return $property->number_of_unit;
            })
            ->addColumn('rooms', function ($property) {
                return propertyTotalRoom($property->id);
            })
            ->addColumn('available', function ($property) {
                return $property->available_unit;
            })

            ->addColumn('action', function ($property) {
                return '<div class="tbl-action-btns d-inline-flex">
                            <a type="button" class="p-1 tbl-action-btn" href="' . route('owner.property.edit', $property->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></a>
                            <a type="button" class="p-1 tbl-action-btn" href="' . route('owner.property.show', $property->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></a>
                            <button onclick="deleteItem(\'' . route('owner.property.delete', $property->id) . '\', \'allDataTable\')" class="p-1 tbl-action-btn"   title="' . __('Delete') . '"><span class="iconify" data-icon="ep:delete-filled"></span></button>
                        </div>';
            })
            ->rawColumns(['name', 'address', 'unit', 'rooms', 'image', 'available', 'action'])
            ->make(true);
    }

    public function allUnit($paginate=true)
    {
        $data = PropertyUnit::query()
            ->join('properties', ['property_units.property_id' => 'properties.id'])
            ->leftJoin('tenants', ['property_units.id' => 'tenants.unit_id', 'tenants.status' => (DB::raw(TENANT_STATUS_ACTIVE))])
            ->leftJoin('users', function ($q) {
                $q->on('tenants.user_id', 'users.id')->whereNull('users.deleted_at');
            })
            ->leftJoin('file_managers', ['property_units.id' => 'file_managers.origin_id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\PropertyUnit'"))])
            ->select('property_units.*', 'properties.name as property_name', 'users.first_name', 'users.last_name', 'file_managers.file_name', 'file_managers.folder_name')
            ->orderBy('properties.id', 'asc')
            ->where('properties.owner_user_id', auth()->id());

        if ($paginate){
            return $data->paginate(10);
        }else{
            return $data->get();
        }
    }

    public function getEmptyUnitsGroupedByProperty($paginate = true)
{
    $data = PropertyUnit::query()
        ->join('properties', 'property_units.property_id', '=', 'properties.id')

        ->leftJoin('tenants', function ($join) {
            $join->on('property_units.id', '=', 'tenants.unit_id')
                ->where('tenants.status', DB::raw(TENANT_STATUS_ACTIVE));
        })
        ->leftJoin('users as tenants_users', function ($q) {
            $q->on('tenants.user_id', '=', 'tenants_users.id')->whereNull('tenants_users.deleted_at');
        })

        ->leftJoin('file_managers', function ($join) {
            $join->on('properties.thumbnail_image_id', '=', 'file_managers.id')
                 ->where('file_managers.origin_type', '=', 'App\\Models\\Property');
        })

        ->leftJoin('users as owners', 'properties.owner_user_id', '=', 'owners.id')
        ->leftJoin('property_details', 'properties.id', '=', 'property_details.property_id')

        ->whereNull('tenants.id')
        ->whereNotNull('properties.id')

        ->select(
            'properties.id as property_id',
            'properties.name as property_name',
            'owners.first_name as owner_first_name',
            'owners.last_name as owner_last_name',
            'file_managers.file_name',
            'file_managers.folder_name',
            DB::raw('COUNT(property_units.id) as empty_units_count'),
            'property_details.country_id as country',
            'property_details.state_id as state',
            'property_details.city_id as city',
            'property_details.map_link',
            DB::raw("CONCAT('/storage/', file_managers.folder_name, '/', file_managers.file_name) as thumbnail_url")
        )

        ->groupBy(
            'properties.id',
            'properties.name',
            'owners.first_name',
            'owners.last_name',
            'file_managers.file_name',
            'file_managers.folder_name',
            'property_details.country_id',
            'property_details.state_id',
            'property_details.city_id',
            'property_details.map_link'
        )
        ->orderBy('properties.id', 'asc');

    return $paginate ? $data->paginate(10) : $data->get();
}

public function getEmptyUnitsByProperty($propertyId)
{
    return PropertyUnit::query()
        ->where('property_units.property_id', $propertyId)
        ->join('properties', 'property_units.property_id', '=', 'properties.id')

        ->leftJoin('users as owners', 'properties.owner_user_id', '=', 'owners.id')

        ->leftJoin('tenants', function ($join) {
            $join->on('property_units.id', '=', 'tenants.unit_id')
                ->where('tenants.status', DB::raw(TENANT_STATUS_ACTIVE));
        })

        ->leftJoin('users as tenants_users', function ($q) {
            $q->on('tenants.user_id', '=', 'tenants_users.id')
                ->whereNull('tenants_users.deleted_at');
        })

        ->leftJoin('file_managers as unit_images', function ($join) {
            $join->on('property_units.id', '=', 'unit_images.origin_id')
                ->where('unit_images.origin_type', '=', DB::raw("'App\\\\Models\\\\PropertyUnit'"));
        })

        ->leftJoin('file_managers as property_thumbnails', function ($join) {
            $join->on('properties.thumbnail_image_id', '=', 'property_thumbnails.id')
                ->where('property_thumbnails.origin_type', '=', DB::raw("'App\\\\Models\\\\Property'"));
        })
        ->leftJoin('property_details', 'properties.id', '=', 'property_details.property_id')
        ->whereNull('tenants.id') // Only unoccupied units
        ->select(
            'property_units.*',
            'properties.id as property_id',
            'properties.name as property_name',
            'owners.first_name as owner_first_name',
            'property_details.state_id as state',
            'property_details.city_id as city',
            'owners.last_name as owner_last_name',
            'unit_images.file_name as unit_image_file',
            'unit_images.folder_name as unit_image_folder',
            'property_thumbnails.file_name as thumbnail_file',
            'property_thumbnails.folder_name as thumbnail_folder',
            DB::raw("CONCAT('/storage/', property_thumbnails.folder_name, '/', property_thumbnails.file_name) as thumbnail_url")
        )
        ->orderBy('property_units.id', 'asc')
        ->get();
}

    public function getAllCount()
    {
        $data = Property::query()
            ->leftJoin('tenants', ['properties.id' => 'tenants.property_id', 'tenants.status' => (DB::raw(TENANT_STATUS_ACTIVE))])
            ->leftJoin('property_details', 'properties.id', '=', 'property_details.property_id')
            ->leftJoin('maintainers', 'properties.id', '=', 'maintainers.property_id')
            ->selectRaw('COUNT(DISTINCT tenants.id) as total_tenant,COUNT(DISTINCT maintainers.id) as total_maintainers,properties.*,property_details.address')
            ->groupBy('properties.id')
            ->orderBy('properties.id')
            ->where('properties.owner_user_id', auth()->id())
            ->get();
        return $data?->makeHidden(['updated_at', 'created_at', 'deleted_at']);
    }

    public function getById($id)
    {
        return Property::where('owner_user_id', auth()->id())->findOrFail($id);
    }

    public function getDetailsById($id)
    {
        $data = Property::query()
            ->leftJoin('property_details', 'properties.id', '=', 'property_details.property_id')
            ->leftJoin('users', 'properties.maintainer_id', '=', 'users.id')
            ->leftJoin('users as owners', 'properties.owner_user_id', '=', 'owners.id')
            ->leftJoin('tenants', ['properties.id' => 'tenants.property_id', 'tenants.status' => (DB::raw(TENANT_STATUS_ACTIVE))])
            ->selectRaw('properties.number_of_unit - (COUNT(tenants.id)) as available_unit,
            (avg(tenants.general_rent)) as avg_general_rent,
            sum(tenants.security_deposit) as total_security_deposit,
            sum(tenants.late_fee) as total_late_fee,properties.*,
            property_details.lease_amount,
            property_details.lease_start_date,
            property_details.lease_end_date,
            property_details.country_id,
            property_details.state_id,
            property_details.city_id,
            property_details.zip_code,
            property_details.address,
            property_details.map_link,
            users.first_name,
            users.last_name,
            owners.first_name as owner_first_name,
            owners.last_name as owner_last_name')
            ->groupBy('properties.id')
            ->findOrFail($id);
        return $data?->makeHidden(['updated_at', 'created_at', 'deleted_at']);
    }

    public function getByType($type, $paginate=true)
    {
        $data = Property::query()
            ->leftJoin('tenants', ['properties.id' => 'tenants.property_id', 'tenants.status' => (DB::raw(TENANT_STATUS_ACTIVE))])
            ->leftJoin('users', function ($q) {
                $q->on('tenants.user_id', 'users.id')->whereNull('users.deleted_at');
            })
            ->selectRaw('properties.number_of_unit - (COUNT(users.id)) as available_unit,properties.*')
            ->groupBy('properties.id')
            ->where('properties.property_type', $type)
            ->where('properties.owner_user_id', auth()->id());
        
        if ($paginate){
            return $data->paginate(10);
        }else{
            return $data->get();
        }
    }

    public function getByTypeCount($type)
    {
        return Property::query()
            ->where('property_type', $type)
            ->where('owner_user_id', auth()->id())
            ->count();
    }

    public function getByTypeData($type)
    {
        $properties = Property::query()
            ->leftJoin('tenants', ['properties.id' => 'tenants.property_id', 'tenants.status' => (DB::raw(TENANT_STATUS_ACTIVE))])
            ->selectRaw('properties.number_of_unit - (COUNT(tenants.id)) as available_unit,properties.*')
            ->groupBy('properties.id')
            ->where('properties.property_type', $type)
            ->where('properties.owner_user_id', auth()->id());

        return datatables($properties)
            ->addIndexColumn()
            ->addColumn('image', function ($property) {
                return '<img src="' . $property->thumbnail_image . '"
                class="rounded-circle avatar-md tbl-user-image"
                alt="">';
            })
            ->addColumn('name', function ($property) {
                return $property->name;
            })
            ->addColumn('address', function ($property) {
                return $property->propertyDetail?->address;
            })
            ->addColumn('unit', function ($property) {
                return $property->number_of_unit;
            })
            ->addColumn('rooms', function ($property) {
                return propertyTotalRoom($property->id);
            })
            ->addColumn('available', function ($property) {
                return $property->available_unit;
            })
            ->addColumn('action', function ($property) {
                return '<div class="tbl-action-btns d-inline-flex">
                            <a type="button" class="p-1 tbl-action-btn" href="' . route('owner.property.edit', $property->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></a>
                            <a type="button" class="p-1 tbl-action-btn" href="' . route('owner.property.show', $property->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></a>
                            <button onclick="deleteItem(\'' . route('owner.property.delete', $property->id) . '\', \'allDataTable\')" class="p-1 tbl-action-btn"   title="' . __('Delete') . '"><span class="iconify" data-icon="ep:delete-filled"></span></button>
                        </div>';
            })
            ->rawColumns(['name', 'address', 'unit', 'rooms', 'image', 'available', 'action'])
            ->make(true);
    }

    public function getPropertyIdsByMaintainerIds($id)
    {
        return Property::query()
            ->where('maintainer_id', $id)
            ->pluck('id')
            ->toArray();
    }

    public function getPropertyWithUnitsById($id)
    {
        try {
            $property = Property::query()
                ->join('property_details', 'properties.id', '=', 'property_details.property_id')
                ->select('properties.name', 'properties.id', 'properties.thumbnail_image_id', 'property_details.address')
                ->where('properties.owner_user_id', auth()->id())
                ->findOrFail($id);
            $propertyUnits = PropertyUnit::query()
                ->select('id', 'unit_name as name', 'general_rent', 'security_deposit', 'late_fee', 'security_deposit_type', 'late_fee_type', 'incident_receipt', 'rent_type', 'monthly_due_day', 'yearly_due_day')
                ->where('property_id', $id)
                ->get();

            $data = $property;
            $data->units = $propertyUnits;
            $data->image = $property->thumbnail_image;
            return $this->success($data);
        } catch (\Exception $e) {
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([], $message);
        }
    }

    public function propertyInformationStore($request)
    {
        DB::beginTransaction();
        try {
            if ($request->property_id) {
                $property = Property::with('propertyDetail')->where('owner_user_id', auth()->id())->where('id', $request->property_id)->firstOrFail();
            } else {
                if (getOwnerLimit(RULES_PROPERTY) < 1) {
                    throw new Exception(__('Your Property Limit is Finished. Choose or Renew Package Plan'));
                }
                $property = new Property();
            }
            $property->property_type = $request->property_type;
            $property->owner_user_id = auth()->id();
            $property->name = ($request->property_type == PROPERTY_TYPE_OWN) ? $request->own_property_name : $request->lease_property_name;
            $property->number_of_unit = ($request->property_type == PROPERTY_TYPE_OWN) ? $request->own_number_of_unit : $request->lease_number_of_unit;
            $property->description = ($request->property_type == PROPERTY_TYPE_OWN) ? $request->own_description : $request->lease_description;
            $property->save();

            $propertyDetail = PropertyDetail::wherePropertyId($property->id)->first();
            if (!$propertyDetail) {
                $propertyDetail = new PropertyDetail();
            }
            $propertyDetail->property_id = $property->id;
            $propertyDetail->lease_amount = ($request->property_type == PROPERTY_TYPE_LEASE) ? $request->lease_amount : 0;
            $propertyDetail->lease_start_date = ($request->property_type == PROPERTY_TYPE_LEASE && !empty($request->lease_start_date)) ? date('Y-m-d', strtotime($request->lease_start_date)) : null;
            $propertyDetail->lease_end_date = ($request->property_type == PROPERTY_TYPE_LEASE && !empty($request->lease_end_date)) ? date('Y-m-d', strtotime($request->lease_end_date)) : null;
            $propertyDetail->save();
            DB::commit();

            $locationService = new LocationService;
            $response['countries'] = $locationService->getCountry()->getData()->data;
            $response['property'] = $property;
            $response['message'] = $request->property_id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            $response['step'] = LOCATION_ACTIVE_CLASS;
            $response['view'] = view('owner.property.partial.render-location', $response)->render();
            return $this->success($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function locationStore($request)
    {
        DB::beginTransaction();
        try {
            $property = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);
            $propertyDetail = PropertyDetail::wherePropertyId($property->id)->first();
            if (!$propertyDetail) {
                $propertyDetail = new PropertyDetail();
            }
            $propertyDetail->country_id = $request->country_id;
            $propertyDetail->state_id = $request->state_id;
            $propertyDetail->city_id = $request->city_id;
            $propertyDetail->zip_code = $request->zip_code;
            $propertyDetail->address = $request->address;
            $propertyDetail->map_link = $request->map_link;
            $propertyDetail->save();

            DB::commit();
            $response['property'] = $property;
            $response['message'] = __(UPDATED_SUCCESSFULLY);
            $response['propertyUnits'] = PropertyUnit::where('property_id', $property->id)->get();
            $response['step'] = UNIT_ACTIVE_CLASS;
            $response['view'] = view('owner.property.partial.render-unit', $response)->render();
            return $this->success($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function unitEdit($request)
    {
        DB::beginTransaction();
        try {
            $property = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);
            if (isset($request->unit_id)){
                $property_unit = PropertyUnit::find((int) $request->unit_id);
                if (!$property_unit) {
                    throw new Exception(__('Selected Unit not found'));
                }
            }else{
                if (getOwnerLimit(RULES_UNIT) < 1) {
                    throw new Exception(__('Your Unit Limit is Finished. Choose or Renew Package Plan'));
                }
                $property_unit = new PropertyUnit();
            }
            $property_unit->property_id = $property->id;
            $property_unit->unit_name = $request->unit_name;
            $property_unit->bedroom = $request->bedroom;
            $property_unit->bath = $request->bath;
            $property_unit->kitchen = $request->kitchen;
            $property_unit->square_feet = $request->square_feet;
            $property_unit->amenities = $request->amenities;
            $property_unit->condition = $request->condition;
            $property_unit->parking = $request->parking;

            $property_unit->general_rent = $request->general_rent ?? 0;
            $property_unit->security_deposit_type = $request->security_deposit_type ?? 0;
            $property_unit->security_deposit = $request->security_deposit ?? 0;
            $property_unit->late_fee_type = $request->late_fee_type ?? 0;
            $property_unit->late_fee = $request->late_fee ?? 0;
            $property_unit->incident_receipt = $request->incident_receipt ?? 0;
            $property_unit->rent_type = $request->rent_type;
            $property_unit->monthly_due_day = ($request->rent_type == PROPERTY_UNIT_RENT_TYPE_MONTHLY) ? $request->monthly_due_day : null;
            $property_unit->yearly_due_day = ($request->rent_type == PROPERTY_UNIT_RENT_TYPE_YEARLY) ? $request->yearly_due_day : null;
            $property_unit->lease_start_date = ($request->rent_type == PROPERTY_UNIT_RENT_TYPE_CUSTOM) ? date('Y-m-d', strtotime($request->lease_start_date)) : null;
            $property_unit->lease_end_date = ($request->rent_type == PROPERTY_UNIT_RENT_TYPE_CUSTOM) ? date('Y-m-d', strtotime($request->lease_end_date)) : null;
            $property_unit->lease_payment_due_date = ($request->rent_type == PROPERTY_UNIT_RENT_TYPE_CUSTOM) ? date('Y-m-d', strtotime($request->lease_payment_due_date)) : null;

            $property_unit->description = $request->description;
            $property_unit->save();

            $this->updateRecurringRentAmounts($property_unit->id, $property_unit->general_rent);

            if (isset($request->unit_image)) {
                $exitFile = FileManager::where('origin_type', 'App\Models\PropertyUnit')->where('origin_id', $property_unit->id)->first();
                if ($exitFile) {
                    $exitFile->removeFile();
                    $upload = $exitFile->updateUpload($exitFile->id, 'PropertyUnit', $request->unit_image, $property_unit->id);
                } else {
                    $newFile = new FileManager();
                    $upload = $newFile->upload('PropertyUnit', $request->unit_image, $property_unit->id);
                }

                if ($upload['status']) {
                    $upload['file']->origin_id = $property_unit->id;
                    $upload['file']->origin_type = "App\Models\PropertyUnit";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            if (!isset($request->unit_id)){
                $property->number_of_unit = $property->number_of_unit+1;
                $property->save();
            }

            DB::commit();

            $message = $request->unit_id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function unitStore($request)
    {
        DB::beginTransaction();
        try {
            $property = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);
            $property->unit_type = $request->unit_type;
            $property->save();

            $notDeletedIds = array();
            if ($request->unit_type == PROPERTY_UNIT_TYPE_SINGLE) {
                for ($i = 0; $i < count($request->single['unit_name']); $i++) {
                    $property_unit = PropertyUnit::find($request->single['id'][$i]);
                    array_push($notDeletedIds, $request->single['id'][$i]);
                    if (!$property_unit) {
                        if (getOwnerLimit(RULES_UNIT) < 1) {
                            throw new Exception(__('Your Unit Limit is Finished. Choose or Renew Package Plan'));
                        }
                        $property_unit = new PropertyUnit();
                    }
                    $property_unit->property_id = $property->id;
                    $property_unit->unit_name = $request->single['unit_name'][$i];
                    $property_unit->bedroom = $request->single['bedroom'][$i];
                    $property_unit->bath = $request->single['bath'][$i];
                    $property_unit->kitchen = $request->single['kitchen'][$i];
                    $property_unit->save();
                }
            } else {
                for ($i = 0; $i < count($request->multiple['unit_name']); $i++) {
                    $property_unit = PropertyUnit::find((int) $request->multiple['id'][$i]);
                    array_push($notDeletedIds, $request->multiple['id'][$i]);
                    if (!$property_unit) {
                        if (getOwnerLimit(RULES_UNIT) < 1) {
                            throw new Exception(__('Your Unit Limit is Finished. Choose or Renew Package Plan'));
                        }
                        $property_unit = new PropertyUnit();
                    }
                    $property_unit->property_id = $property->id;
                    $property_unit->unit_name = $request->multiple['unit_name'][$i];
                    $property_unit->bedroom = $request->multiple['bedroom'][$i];
                    $property_unit->bath = $request->multiple['bath'][$i];
                    $property_unit->kitchen = $request->multiple['kitchen'][$i];
                    $property_unit->square_feet = $request->multiple['square_feet'][$i];
                    $property_unit->amenities = $request->multiple['amenities'][$i];
                    $property_unit->condition = $request->multiple['condition'][$i];
                    $property_unit->parking = $request->multiple['parking'][$i];
                    $property_unit->description = $request->multiple['description'][$i];
                    $property_unit->save();

                    if (isset($request->multiple['images'][$i])) {
                        $exitFile = FileManager::where('origin_type', 'App\Models\PropertyUnit')->where('origin_id', $property_unit->id)->first();
                        if ($exitFile) {
                            $exitFile->removeFile();
                            $upload = $exitFile->updateUpload($exitFile->id, 'PropertyUnit', $request->multiple['images'][$i], $property_unit->id);
                        } else {
                            $newFile = new FileManager();
                            $upload = $newFile->upload('PropertyUnit', $request->multiple['images'][$i], $property_unit->id);
                        }

                        if ($upload['status']) {
                            $upload['file']->origin_id = $property_unit->id;
                            $upload['file']->origin_type = "App\Models\PropertyUnit";
                            $upload['file']->save();
                        } else {
                            throw new Exception($upload['message']);
                        }
                    }
                }
            }
            PropertyUnit::whereNotIn('id', $notDeletedIds)->where('property_id', $property->id)->get()->map(function ($q) {
                $q->delete();
            });

            DB::commit();
            $response['property'] = $property;
            $response['propertyUnits'] = PropertyUnit::where('property_id', $response['property']->id)->get();
            $response['propertyUnitIds'] = PropertyUnit::where('property_id', $response['property']->id)->pluck('id')->toArray();
            $response['message'] = __(UPDATED_SUCCESSFULLY);
            $response['step'] = RENT_CHARGE_ACTIVE_CLASS;
            $response['view'] = view('owner.property.partial.render-rent-charge', $response)->render();
            return $this->success($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function rentChargeStore($request)
    {
        DB::beginTransaction();
        try {
            $property = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);

            for ($i = 0; $i < count($request->propertyUnit['id']); $i++) {
                $property_unit = PropertyUnit::find($request->propertyUnit['id'][$i]);
                $property_unit->general_rent = $request->propertyUnit['general_rent'][$i] ?? 0;
                $property_unit->security_deposit_type = $request->propertyUnit['security_deposit_type'][$i] ?? 0;
                $property_unit->security_deposit = $request->propertyUnit['security_deposit'][$i] ?? 0;
                $property_unit->late_fee_type = $request->propertyUnit['late_fee_type'][$i] ?? 0;
                $property_unit->late_fee = $request->propertyUnit['late_fee'][$i] ?? 0;
                $property_unit->incident_receipt = $request->propertyUnit['incident_receipt'][$i] ?? 0;
                $property_unit->rent_type = $request->propertyUnit['rent_type'][$i];
                $property_unit->monthly_due_day = ($request->propertyUnit['rent_type'][$i] == PROPERTY_UNIT_RENT_TYPE_MONTHLY) ? $request->propertyUnit['monthly_due_day'][$i] : null;
                $property_unit->yearly_due_day = ($request->propertyUnit['rent_type'][$i] == PROPERTY_UNIT_RENT_TYPE_YEARLY) ? $request->propertyUnit['yearly_due_day'][$i] : null;
                $property_unit->lease_start_date = ($request->propertyUnit['rent_type'][$i] == PROPERTY_UNIT_RENT_TYPE_CUSTOM) ? date('Y-m-d', strtotime($request->propertyUnit['lease_start_date'][$i])) : null;
                $property_unit->lease_end_date = ($request->propertyUnit['rent_type'][$i] == PROPERTY_UNIT_RENT_TYPE_CUSTOM) ? date('Y-m-d', strtotime($request->propertyUnit['lease_end_date'][$i])) : null;
                $property_unit->lease_payment_due_date = ($request->propertyUnit['rent_type'][$i] == PROPERTY_UNIT_RENT_TYPE_CUSTOM) ? date('Y-m-d', strtotime($request->propertyUnit['lease_payment_due_date'][$i])) : null;
                $property_unit->save();
                $this->updateRecurringRentAmounts($property_unit->id, $property_unit->general_rent);
            }
            DB::commit();
            $response['property'] = $property;
            $response['message'] = __(UPDATED_SUCCESSFULLY);
            $response['step'] = IMAGE_ACTIVE_CLASS;
            $response['view'] = view('owner.property.partial.render-image', $response)->render();
            return $this->success($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function imageStore($request, $id)
    {
        DB::beginTransaction();
        try {
            $property = Property::where('owner_user_id', auth()->id())->findOrFail($id);
            /*File Manager Call upload*/
            if ($request->file('file')) {
                $new_file = new FileManager();
                $upload = $new_file->upload('PropertyImage', $request->file);

                if ($upload['status']) {
                    $propertyImage = new PropertyImage();
                    $propertyImage->property_id = $property->id;
                    $propertyImage->file_id = $upload['file']->id;
                    $propertyImage->save();

                    $upload['file']->origin_id = $propertyImage->id;
                    $upload['file']->origin_type = "App\Models\PropertyImage";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            /*End*/

            DB::commit();
            $property = $property;
            return $this->success($property, __(UPLOADED_SUCCESSFULLY));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function imageDelete($id)
    {
        DB::beginTransaction();
        try {
            $existsImage = PropertyImage::query()
                ->join('properties', 'property_images.property_id', '=', 'properties.id')
                ->where('property_images.id', $id)
                ->where('properties.owner_user_id', auth()->id())
                ->exists();
            if ($existsImage) {
                $propertyImage = PropertyImage::findOrFail($id);
                $file = FileManager::where('origin_type', 'App\Models\PropertyImage')->where('origin_id', $id)->first();
                if ($file) {
                    $file->removeFile();
                    $file->delete();
                    $propertyImage->delete();
                }
                DB::commit();
                return $this->success([], __(DELETED_SUCCESSFULLY));
            } else {
                throw new Exception(__(SOMETHING_WENT_WRONG));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function thumbnailImageUpdate($request, $id)
    {
        DB::beginTransaction();
        try {
            /*File Manager Call upload for Thumbnail Image*/
            $property = Property::where('owner_user_id', auth()->id())->findOrFail($id);
            if ($request->file) {
                $new_file = new FileManager();
                $upload = $new_file->upload('Property', $request->file);

                if ($upload['status']) {
                    $property->thumbnail_image_id = $upload['file']->id;
                    $property->save();

                    $upload['file']->origin_type = "App\Models\Property";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            /*End*/
            DB::commit();
            return $this->success([], __(UPLOADED_SUCCESSFULLY));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function getPropertyInformation($request)
    {
        DB::beginTransaction();
        try {
            $response = [];
            if ($request->property_id) {
                $response['property'] = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);
            }

            $view = view('owner.property.partial.render-property-information', $response)->render();
            DB::commit();
            return $this->success($view);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e));
        }
    }

    public function getLocation($request)
    {
        try {
            $response['property'] = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);
            $country_file = public_path('file/countries.csv');
            $response['countries'] = csvToArray($country_file);
            $response['view'] = view('owner.property.partial.render-location', $response)->render();
            return $this->success($response);
        } catch (\Exception $e) {
            return $this->error([], getErrorMessage($e));
        }
    }

    public function getUnitByPropertyId($request)
    {
        try {
            $response['property'] = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);
            $response['propertyUnits'] = PropertyUnit::where('property_id', $response['property']->id)->get();
            $response['view'] = view('owner.property.partial.render-unit', $response)->render();
            return $this->success($response);
        } catch (\Exception $e) {
            return $this->error([], getErrorMessage($e));
        }
    }

    public function getUnitById($id)
    {
        try {
            $data=[];
            $data['unit'] = PropertyUnit::query()
                        ->leftJoin('file_managers', ['property_units.id' => 'file_managers.origin_id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\PropertyUnit'"))])
                        ->select('property_units.*', 'file_managers.file_name', 'file_managers.folder_name')
                        ->where('property_units.id', $id)
                        ->first();
            $data['property'] = Property::findOrFail($data['unit']->property_id);
            return $data;
        } catch (\Exception $e) {
            return $this->error([], getErrorMessage($e));
        }
    }

    public function getUnitByPropertyIds($request)
    {
        try {
            $propertiesIds = Property::query()
                ->when(!in_array('all', $request->property_ids ?? []), function ($q) use ($request) {
                    $q->whereIn('id', $request->property_ids ?? []);
                })
                ->where('owner_user_id', auth()->id())
                ->select('id')
                ->pluck('id')
                ->toArray();
            $data['units'] = PropertyUnit::whereIn('property_id', $propertiesIds ?? [])->get();
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error([], getErrorMessage($e));
        }
    }

    public function getRentCharge($request)
    {
        try {
            $response['property'] = Property::where('owner_user_id', auth()->id())->findOrFail($request->property_id);
            $response['propertyUnits'] = PropertyUnit::where('property_id', $response['property']->id)->get();
            $response['propertyUnitIds'] = PropertyUnit::where('property_id', $response['property']->id)->pluck('id')->toArray();
            $response['view'] = view('owner.property.partial.render-rent-charge', $response)->render();
            return $this->success($response);
        } catch (\Exception $e) {
            return $this->error([], getErrorMessage($e));
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $tenant = Tenant::where('property_id', $id)->where('status', TENANT_STATUS_ACTIVE)->first();
            if ($tenant) {
                throw new Exception('Tenant Available! You can\'t delete');
            }
            $property = Property::where('owner_user_id', auth()->id())->findOrFail($id);
            if ($property) {
                foreach (@$property->propertyImages as $propertyImage) {
                    $propertyImage = PropertyImage::find($propertyImage->id);
                    $fileManager = FileManager::find($propertyImage->file_id);
                    if ($propertyImage && $fileManager) {
                        $fileManager->removeFile();
                        $fileManager->delete();
                        $propertyImage->delete();
                    }
                }
                if ($property->propertyDetail) {
                    $property->propertyDetail->delete();
                }
                PropertyUnit::where('property_id', $property->id)->delete();
                $property->delete();
            }
            DB::commit();
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getUnitsByPropertyId($id,$activeTenants)
    {
        $propertyUnits = PropertyUnit::query()
            ->leftJoin('tenants', 'property_units.id', '=', 'tenants.unit_id')
            ->leftJoin('users', function ($q) {
                $q->on('tenants.user_id', 'users.id')->whereNull('users.deleted_at');
            })
            ->leftJoin('file_managers', function ($q) {
                $q->on('property_units.id', '=', 'file_managers.origin_id')
                ->where('file_managers.origin_type', '=', 'App\\Models\\PropertyUnit');
            })
            ->select(
                'property_units.*',
                'tenants.status as tenant_status',
                'tenants.user_id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'file_managers.file_name',
                'file_managers.folder_name'
            )
            ->where('property_units.property_id', $id);

        if ($activeTenants) {
            $propertyUnits->where('tenants.status', TENANT_STATUS_ACTIVE);
        }

        $propertyUnits = $propertyUnits
            ->groupBy('property_units.id')
            ->get();

        return $this->success($propertyUnits);
    }

    public function unitDelete($id)
    {
        try {
            $tenant = Tenant::where('unit_id', $id)->where('status', TENANT_STATUS_ACTIVE)->first();
            if ($tenant) {
                throw new Exception('Tenant Available! You can\'t delete');
            }

            $propertyIds = Property::query()
                ->where('owner_user_id', auth()->id())
                ->withTrashed()
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();

            $unit = PropertyUnit::query()
                ->whereIn('property_id', $propertyIds)
                ->find($id);

            if ($unit) {
                $unit->delete();
                $property = Property::findOrFail($unit->property_id);
                $property->number_of_unit = $property->number_of_unit-1;
                $property->save();
            } else {
                throw new Exception(__('No Data Found'));
            }
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateRecurringRentAmounts($unitId, $newRent)
    {
        $rentType = InvoiceType::where('name', 'Rent')
                    ->where('owner_user_id', auth()->id())
                    ->first();
        if (!$rentType) return;

        // Fetch ALL recurring settings for the given unit
        $recurringSettings = InvoiceRecurringSetting::where('property_unit_id', $unitId)->get();

        foreach ($recurringSettings as $setting) {
            $rentItems = $setting->items()
                ->where('invoice_type_id', $rentType->id)
                ->get();

            if ($rentItems->isEmpty()) {
                continue;
            }

            $totalOldAmount = $rentItems->sum('amount');
            $totalNewAmount = $rentItems->count() * $newRent;

            foreach ($rentItems as $item) {
                $item->amount = $newRent;
                $item->save();
            }

            $setting->amount = $setting->amount - $totalOldAmount + $totalNewAmount;
            $setting->save();
        }
    }

}
