<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageReplyRequest;
use App\Services\ContactMessageService;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public $contactMessageService;
    public function __construct()
    {
        $this->contactMessageService = new ContactMessageService;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->contactMessageService->getAllData();
        } else {
            $data['pageTitle'] = __('All Message');
            return view('saas.admin.message', $data);
        }
    }

    public function getInfo(Request $request)
    {
        $data = $this->contactMessageService->getInfo($request->id);
        $data->is_view = ACTIVE;
        $data->save();
        return $data;
    }

    public function reply(MessageReplyRequest $request)
    {
        return $this->contactMessageService->reply($request);
    }
}
