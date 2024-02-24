<?php

namespace App\Http\Controllers\SmsMail;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailTemplateRequest;
use App\Services\SmsMail\EmailTemplateService;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public $emailTemplateService;

    public function __construct()
    {
        $this->emailTemplateService = new EmailTemplateService;
    }

    public function index()
    {
        $data['pageTitle'] = __('Email Template');
        $data['templates'] = $this->emailTemplateService->getAllByOwner();
        return view('sms-mail.template', $data);
    }

    public function store(EmailTemplateRequest $request)
    {
        return $this->emailTemplateService->store($request);
    }

    public function details(Request $request)
    {
        return $this->emailTemplateService->details($request);
    }
}
