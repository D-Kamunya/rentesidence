<?php

namespace App\Http\Controllers\SmsMail;

use App\Http\Controllers\Controller;
use App\Http\Requests\SmsSendRequest;
use App\Http\Requests\TestSmsRequest;
use App\Models\Maintainer;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MaintainerService;
use App\Services\PropertyService;
use App\Services\SmsMail\TwilioSmsService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class SmsController extends Controller
{
    use ResponseTrait;
    public $twilioSmsService, $smsService, $propertyService, $tenantService, $maintainerService;
    public function __construct()
    {
        $this->twilioSmsService = new TwilioSmsService;
        $this->propertyService = new PropertyService;
        $this->tenantService = new TenantService;
        $this->maintainerService = new MaintainerService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __("Sms");
        $data['subSmsMailActiveClass'] = 'active';
        $data['properties'] = $this->propertyService->getAll();
        $data['tenants'] = $this->tenantService->getActiveAll();
        $data['maintainers'] = $this->maintainerService->getAll();
        if ($request->ajax()) {
            return $this->twilioSmsService->getAllDataByOwnerId();
        }
        return view('sms-mail.sms')->with($data);
    }

    public function send(SmsSendRequest $request)
    {
        try {
            $numbers = [];
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

                        $numbers = Tenant::query()
                            ->join('users', 'tenants.user_id', '=', 'users.id')
                            ->where('tenants.status', ACTIVE)
                            ->whereIn('tenants.unit_id', $unitIds)
                            ->whereNotNull('contact_number')
                            ->select('users.contact_number')
                            ->pluck('users.contact_number')
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
                        $numbers = Tenant::query()
                            ->join('users', 'tenants.user_id', '=', 'users.id')
                            ->where('tenants.status', ACTIVE)
                            ->whereIn('tenants.user_id', $request->tenant_id)
                            ->where('tenants.owner_user_id', auth()->id())
                            ->whereNotNull('contact_number')
                            ->select('users.contact_number')
                            ->pluck('users.contact_number')
                            ->toArray();
                    } else {
                        throw new Exception('Select Tenant');
                    }
                } elseif ($request->user_type == USER_TYPE_MAINTAINER) {
                    if (!is_null($request->maintainer_id)) {
                        $numbers = Maintainer::query()
                            ->join('users', 'maintainers.user_id', '=', 'users.id')
                            ->whereIn('maintainers.user_id', $request->maintainer_id)
                            ->where('maintainers.owner_user_id', auth()->id())
                            ->whereNotNull('contact_number')
                            ->select('users.contact_number')
                            ->pluck('users.contact_number')
                            ->toArray();
                    } else {
                        throw new Exception('Select Maintainer');
                    }
                }
            } elseif ($request->target_audience == TARGET_AUDIENCE_CUSTOM) {
                if (!is_null($request->custom_user_id)) {
                    $numbers = User::query()
                        ->whereIn('id', $request->custom_user_id)
                        ->where('owner_user_id', auth()->id())
                        ->whereNotNull('contact_number')
                        ->select('contact_number')
                        ->pluck('contact_number')
                        ->toArray();
                } else {
                    throw new Exception('Select Users');
                }
            } else {
                throw new Exception(__(SOMETHING_WENT_WRONG));
            }
            $message = $request->message;
            $sendSms = $this->twilioSmsService->sendSms($numbers, $message, auth()->id());
            if ($sendSms == 'success') {
                return $this->success([], __(SENT_SUCCESSFULLY));
            } else {
                throw new Exception($sendSms);
            }
        } catch (Exception $e) {
            return $this->error([], $e->getMessage());
        }
    }

    public function testSend(TestSmsRequest $request)
    {
        $sid = getOption('TWILIO_ACCOUNT_SID');
        $token = getOption('TWILIO_AUTH_TOKEN');
        $from_number = getOption('TWILIO_PHONE_NUMBER');

        try {
            if (getOption('TWILIO_STATUS', 0) == 1 && $sid) {
                $client = new Client($sid, $token);
                $sendStatus = $client->messages->create(
                    $request->phone_number,
                    [
                        'from' => $from_number,
                        'body' => $request->message
                    ]
                );
                if ($sendStatus->status == 'queued' || $sendStatus->status == 'delivered') {
                    return $this->success([], __(SENT_SUCCESSFULLY));
                } else {
                    throw new Exception('sms status not delivered');
                }
            } else {
                throw new Exception('Please setup sms setting');
            }
        } catch (Exception $e) {
            return $this->error([], $e->getMessage());
        }
    }
}
