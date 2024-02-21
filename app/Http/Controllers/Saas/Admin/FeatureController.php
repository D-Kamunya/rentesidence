<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeatureRequest;
use App\Services\FeatureService;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public $featureService;

    public function __construct()
    {
        $this->featureService = new FeatureService;
    }

    public function index()
    {
        $data['pageTitle'] = __("Features");
        $data['subFeatureActiveClass'] = 'active';
        $data['features'] = $this->featureService->getAll();
        return view('admin.frontend.feature', $data);
    }

    public function store(FeatureRequest $request)
    {
        return $this->featureService->store($request);
    }

    public function getInfo(Request $request)
    {
        return $this->featureService->getInfo($request->id);
    }

    public function destroy($id)
    {
        return $this->featureService->delete($id);
    }
}
