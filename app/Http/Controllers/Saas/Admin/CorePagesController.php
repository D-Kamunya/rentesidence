<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CorePageRequest;
use App\Services\CorePageService;
use Illuminate\Http\Request;

class CorePagesController extends Controller
{
    public $corePageService;

    public function __construct()
    {
        $this->corePageService = new CorePageService;
    }

    public function index()
    {
        $data['pageTitle'] = __("Core Page");
        $data['subCorePageActiveClass'] = 'active';
        $data['corePages'] = $this->corePageService->getAll();
        return view('admin.frontend.core-page')->with($data);
    }

    public function store(CorePageRequest $request)
    {
        return $this->corePageService->store($request);
    }

    public function getInfo(Request $request)
    {
        return $this->corePageService->getInfo($request->id);
    }


    public function destroy($id)
    {
        return $this->corePageService->delete($id);
    }
}
