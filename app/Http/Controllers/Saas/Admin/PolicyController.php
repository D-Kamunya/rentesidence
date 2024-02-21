<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function termsConditions()
    {
        $data['pageTitle'] = __("Terms & Conditions");
        $data['name'] = 'terms_conditions';
        $data['description'] = getOption('terms_conditions');
        return view('saas.admin.policy', $data);
    }

    public function privacyPolicy()
    {
        $data['pageTitle'] = __("Privacy Policy");
        $data['name'] = 'privacy_policy';
        $data['description'] = getOption('privacy_policy');
        return view('saas.admin.policy', $data);
    }

    public function cookiePolicy()
    {
        $data['pageTitle'] = __("Cookie Policy");
        $data['name'] = 'cookie_policy';
        $data['description'] = getOption('cookie_policy');
        return view('saas.admin.policy', $data);
    }
}
