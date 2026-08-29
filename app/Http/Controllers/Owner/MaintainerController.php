<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintainerRequest;
use App\Models\Owner;
use App\Services\MaintainerService;
use App\Services\PropertyService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class MaintainerController extends Controller
{
    use ResponseTrait;
    public $maintainerService;
    public $propertyService;

    public function __construct()
    {
        $this->maintainerService = new MaintainerService;
        $this->propertyService = new PropertyService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Maintainer');
        $data['properties'] = $this->propertyService->getAll();
        if ($request->ajax()) {
            return $this->maintainerService->getAllData();
        }
        $data['canConfirmRent'] = (bool) Owner::where('user_id', auth()->id())->value('caretaker_can_confirm_rent');
        return view('owner.maintains.maintainer', $data);
    }

    /** Owner delegates (or revokes) cash rent-payment confirmation to their caretaker(s). */
    public function updatePermissions(Request $request)
    {
        Owner::where('user_id', auth()->id())
            ->update(['caretaker_can_confirm_rent' => $request->boolean('caretaker_can_confirm_rent')]);

        return $this->success([], $request->boolean('caretaker_can_confirm_rent')
            ? __('Your caretaker can now confirm cash rent payments. You’ll be notified each time.')
            : __('Cash rent confirmation is now off — only you can mark rent paid.'));
    }

    public function store(MaintainerRequest $request)
    {
        return $this->maintainerService->store($request);
    }

    public function getInfo(Request $request)
    {
        try {
            $data = $this->maintainerService->getInfo($request->id);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage());
        }
    }

    public function delete($id)
    {
        return $this->maintainerService->deleteById($id);
    }
}
