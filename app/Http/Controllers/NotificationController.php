<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService;
    }
    public function status($id,$role)
    {
        $data = $this->notificationService->status($id);
        if ($data->getData()->status == true && $role == '1') {
            return redirect()->route('owner.notification');
        } else if($data->getData()->status == true && $role == '2'){
            return redirect()->route('tenant.notification');
        } else if($data->getData()->status == true && $role == '3'){
            return redirect()->route('maintainer.notification');
        }else if($data->getData()->status == true && $role == '4'){
            return redirect()->route('admin.notification');
        }else {
            return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
        }
    }
}
