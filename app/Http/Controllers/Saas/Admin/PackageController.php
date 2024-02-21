<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Services\OwnerService;
use App\Services\PackageService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use ResponseTrait;
    public $packageService;

    public function __construct()
    {
        $this->packageService = new PackageService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->packageService->getAllData($request);
        } else {
            $data['pageTitle'] = __('All Packages');
            return view('admin.packages.index', $data);
        }
    }

    public function store(PackageRequest $request)
    {
        return $this->packageService->store($request);
    }

    public function getInfo(Request $request)
    {
        $data = $this->packageService->getInfo($request->id);
        return $this->success($data);
    }

    public function destroy($id)
    {
        return $this->packageService->destroy($id);
    }

    public function ownerPackage(Request $request)
    {
        if ($request->ajax()) {
            return $this->packageService->getUserPackagesData($request);
        } else {
            $data['pageTitle'] = __('Owner Packages');
            $ownerService = new OwnerService;
            $data['owners'] = $ownerService->getAll();
            $data['packages'] = $this->packageService->getAll();
            return view('admin.packages.owner', $data);
        }
    }

    public function assignPackage(Request $request)
    {
        return $this->packageService->assignPackage($request);
    }
}
