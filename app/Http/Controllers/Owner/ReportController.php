<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public $reportService, $propertyService;

    public function __construct()
    {
        $this->reportService =  new ReportService;
        $this->propertyService = new PropertyService;
    }

    public function earning(Request $request)
    {
        $data['pageTitle'] = __('Earning Report');
        if ($request->ajax()) {
            return $this->reportService->earning();
        }
        $data['properties'] = $this->propertyService->getAll();

        $imagePath = getSettingImage('app_logo');
        $base64Image = $this->convertToBase64($imagePath);

        // Get Authenticated User's Profile Image as Base64
        $userImagePath = auth()->user()->getImageAttribute() ?? null; // Assuming 'profile_image' exists
        $base64UserImage = $this->convertToBase64($userImagePath);

        $data['base64Image'] = $base64Image;
        $data['base64UserImage'] = $base64UserImage;
        return view('owner.report.earning', $data);
    }

    public function lossProfitByMonth(Request $request)
    {
        $data['pageTitle'] = __('Loss Profit By Month Report');
        $data['lossProfits'] = $this->reportService->lossProfitByMonth();
        return view('owner.report.earning-by-month', $data);
    }

    public function expenses(Request $request)
    {
        $data['pageTitle'] = __('Expenses Report');
        if ($request->ajax()) {
            return $this->reportService->expenses();
        }
        $data['properties'] = $this->propertyService->getAll();

        $imagePath = getSettingImage('app_logo');
        $base64Image = $this->convertToBase64($imagePath);

        // Get Authenticated User's Profile Image as Base64
        $userImagePath = auth()->user()->getImageAttribute() ?? null; // Assuming 'profile_image' exists
        $base64UserImage = $this->convertToBase64($userImagePath);

        $data['base64Image'] = $base64Image;
        $data['base64UserImage'] = $base64UserImage;
        return view('owner.report.expenses', $data);
    }

    public function lease(Request $request)
    {
        $data['pageTitle'] = __('Lease Report');
        if ($request->ajax()) {
            return $this->reportService->leases();
        }
        return view('owner.report.lease', $data);
    }

    public function occupancy(Request $request)
    {
        $data['pageTitle'] = __('Occupancy Report');
        if ($request->ajax()) {
            return $this->reportService->occupancy();
        }
        $imagePath = getSettingImage('app_logo');
        $base64Image = $this->convertToBase64($imagePath);

        // Get Authenticated User's Profile Image as Base64
        $userImagePath = auth()->user()->getImageAttribute() ?? null; // Assuming 'profile_image' exists
        $base64UserImage = $this->convertToBase64($userImagePath);

        $data['base64Image'] = $base64Image;
        $data['base64UserImage'] = $base64UserImage;
        return view('owner.report.occupancy', $data);
    }

    public function maintenance(Request $request)
    {
        $data['pageTitle'] = __('Maintenance Report');
        if ($request->ajax()) {
            return $this->reportService->maintenance();
        }
        $imagePath = getSettingImage('app_logo');
        $base64Image = $this->convertToBase64($imagePath);

        // Get Authenticated User's Profile Image as Base64
        $userImagePath = auth()->user()->getImageAttribute() ?? null; // Assuming 'profile_image' exists
        $base64UserImage = $this->convertToBase64($userImagePath);

        $data['base64Image'] = $base64Image;
        $data['base64UserImage'] = $base64UserImage;
        return view('owner.report.maintenance', $data);
    }

    public function tenant(Request $request)
    {
        $data['pageTitle'] = __('Tenant Report');
        if ($request->ajax()) {
            return $this->reportService->tenant();
        }
        $imagePath = getSettingImage('app_logo');
        $base64Image = $this->convertToBase64($imagePath);

        // Get Authenticated User's Profile Image as Base64
        $userImagePath = auth()->user()->getImageAttribute() ?? null; // Assuming 'profile_image' exists
        $base64UserImage = $this->convertToBase64($userImagePath);

        $data['base64Image'] = $base64Image;
        $data['base64UserImage'] = $base64UserImage;
        return view('owner.report.tenant', $data);
    }

    private function convertToBase64($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        $imagePath = str_replace(url('/'), '', $imagePath); // Convert to relative path
        $fullPath = public_path($imagePath); // Get absolute path

        if (file_exists($fullPath)) {
            $imageData = file_get_contents($fullPath);
            return 'data:image/png;base64,' . base64_encode($imageData);
        }

        return null;
    }
}
