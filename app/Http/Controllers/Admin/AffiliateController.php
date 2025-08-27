<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use App\Http\Requests\AffiliateRegisterRequest;
use App\Models\Affiliate;
use App\Models\User;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AffiliateController extends Controller
{
    public $affiliateService;
    public function __construct()
    {
        $this->affiliateService = new AffiliateService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->affiliateService->getAllData($request);
        } else {
            $data['pageTitle'] = __('Affiliates');
            return view('admin.affiliates.index', $data);
        }
    }

    public function affiliate_register_form()
    {
        $data['pageTitle'] = __('Add Affiliate');
        $data['navAffiliatesAddMMShowClass'] = 'active';
        return view('admin.affiliates.add', $data);
    }

    public function affiliate_register_store(AffiliateRegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->affiliateService->registerAffiliate($request->validated());
            return back()->with('success', __("AFFILIATE REGISTERED SUCCESSFULLY"));
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
