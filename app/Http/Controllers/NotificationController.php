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
    public function status($id,$role,Request $request)
    {
        $data = $this->notificationService->status($id);
        if ($data->getData()->status == true) {
            $url = urldecode($request->query('url'));
            return redirect($url);
        }else {
            return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
        }
    }
}
