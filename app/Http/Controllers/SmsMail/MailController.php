<?php

namespace App\Http\Controllers\SmsMail;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailSendRequest;
use App\Models\Maintainer;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MaintainerService;
use App\Services\PropertyService;
use App\Services\SmsMail\MailService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class MailController extends Controller
{
    use ResponseTrait;
    public $mailService, $smsService, $propertyService, $tenantService, $maintainerService;
    public function __construct()
    {
        $this->mailService = new MailService;
        $this->propertyService = new PropertyService;
        $this->tenantService = new TenantService;
        $this->maintainerService = new MaintainerService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __("Mail");
        $data['subMailMailActiveClass'] = 'active';
        $data['properties'] = $this->propertyService->getAll();
        $data['tenants'] = $this->tenantService->getActiveAll();
        $data['maintainers'] = $this->maintainerService->getAll();
        if ($request->ajax()) {
            return $this->mailService->getAllDataByOwnerId();
        }
        return view('sms-mail.mail')->with($data);
    }

    public function send(MailSendRequest $request)
    {
        try {
            $emails = [];
            if ($request->target_audience == TARGET_AUDIENCE_PROPERTY) {
                if (!is_null($request->property_id)) {
                    $propertiesIds = Property::query()
                        ->when(!in_array('all', $request->property_id ?? []), function ($q) use ($request) {
                            $q->whereIn('id', $request->property_id ?? []);
                        })
                        ->where('owner_user_id', auth()->id())
                        ->select('id')
                        ->pluck('id')
                        ->toArray();
                    if (!is_null($request->unit_id)) {
                        $unitIds = PropertyUnit::query()
                            ->when(!in_array('all', $request->unit_id ?? []), function ($q) use ($request) {
                                $q->whereIn('id', $request->unit_id ?? []);
                            })
                            ->whereIn('property_id', $propertiesIds ?? [])
                            ->select('id')
                            ->pluck('id')
                            ->toArray();

                        $emails = Tenant::query()
                            ->join('users', 'tenants.user_id', '=', 'users.id')
                            ->where('tenants.status', ACTIVE)
                            ->whereIn('tenants.unit_id', $unitIds)
                            ->whereNotNull('email')
                            ->select('users.email')
                            ->pluck('users.email')
                            ->toArray();
                    } else {
                        throw new Exception('Select Unit');
                    }
                } else {
                    throw new Exception('Select Property');
                }
            } elseif ($request->target_audience == TARGET_AUDIENCE_USER) {
                if ($request->user_type == USER_TYPE_TENANT) {
                    if (!is_null($request->tenant_id)) {
                        $emails = Tenant::query()
                            ->join('users', 'tenants.user_id', '=', 'users.id')
                            ->where('tenants.status', ACTIVE)
                            ->whereIn('tenants.user_id', $request->tenant_id)
                            ->where('tenants.owner_user_id', auth()->id())
                            ->whereNotNull('email')
                            ->select('users.email')
                            ->pluck('users.email')
                            ->toArray();
                    } else {
                        throw new Exception('Select Tenant');
                    }
                } elseif ($request->user_type == USER_TYPE_MAINTAINER) {
                    if (!is_null($request->maintainer_id)) {
                        $emails = Maintainer::query()
                            ->join('users', 'maintainers.user_id', '=', 'users.id')
                            ->whereIn('maintainers.user_id', $request->maintainer_id)
                            ->where('maintainers.owner_user_id', auth()->id())
                            ->whereNotNull('email')
                            ->select('users.email')
                            ->pluck('users.email')
                            ->toArray();
                    } else {
                        throw new Exception('Select Maintainer');
                    }
                }
            } elseif ($request->target_audience == TARGET_AUDIENCE_CUSTOM) {
                if (!is_null($request->custom_user_id)) {
                    $emails = User::query()
                        ->whereIn('id', $request->custom_user_id)
                        ->where('owner_user_id', auth()->id())
                        ->whereNotNull('email')
                        ->select('email')
                        ->pluck('email')
                        ->toArray();
                } else {
                    throw new Exception('Select Users');
                }
            } else {
                throw new Exception(__(SOMETHING_WENT_WRONG));
            }
            $message = $request->message;
            $subject = $request->subject;
            $sendMail = $this->mailService->sendMail($emails, $subject, $message, auth()->id());
            if ($sendMail == 'success') {
                return $this->success([], __(SENT_SUCCESSFULLY));
            } else {
                throw new Exception($sendMail);
            }
        } catch (Exception $e) {
            return $this->error([], $e->getMessage());
        }
    }
}
