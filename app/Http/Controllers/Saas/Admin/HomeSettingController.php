<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;

class HomeSettingController extends Controller
{
    public function section()
    {
        $data['pageTitle'] = __("Home Section Setting");
        $data['subHomeSectionSettingActiveClass'] = 'active';
        return view('admin.frontend.section-settings', $data);
    }
}
