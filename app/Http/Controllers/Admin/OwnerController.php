<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OwnerService;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public $ownerService;
    public function __construct()
    {
        $this->ownerService = new OwnerService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->ownerService->getAllData($request);
        } else {
            $data['pageTitle'] = __('Owners');
            return view('admin.owner.index', $data);
        }
    }
}
