<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use App\Traits\ResponseTrait;
use Exception;

class PropertyController extends Controller
{
    use ResponseTrait;
    public $propertyService;

    public function __construct()
    {
        $this->propertyService = new PropertyService;
    }

    public function allProperty()
    {
        $data['properties'] = $this->propertyService->getAll();
        return $this->success($data);
    }

    public function details($id)
    {
        try {
            $data['property'] = $this->propertyService->getDetailsById($id);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage());
        }
    }

    public function allUnit()
    {
        $data['units'] = $this->propertyService->allUnit();
        return $this->success($data);
    }
}
