<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use App\Services\ReportService;
use App\Services\ReportExportService;
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

    /**
     * Server-side export of a report — builds the COMPLETE dataset (honouring the
     * current filters) and streams it as PDF or CSV, so exports are never truncated to
     * the visible DataTables page. Owner-scoped inside ReportExportService.
     */
    public function export(Request $request, string $report)
    {
        $payload = app(ReportExportService::class)->build($report);
        abort_if($payload === null, 404);

        $appName   = getOption('app_name');
        $ownerName = auth()->user()->getNameAttribute();

        $filename = trim($ownerName . ' - ' . $appName . ' ' . $payload['title']);
        $filename = preg_replace('/[^A-Za-z0-9 _-]/', '', $filename) ?: $report;

        if (strtolower((string) $request->input('format', 'pdf')) === 'csv') {
            return $this->exportCsv($payload, $filename);
        }

        $payload['logo']      = $this->convertToBase64(getSettingImage('app_logo'));
        $payload['appName']   = $appName;
        $payload['ownerName'] = $ownerName;

        $pdf = \PDF::loadView('owner.report.pdf.table', $payload)->setPaper('a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    /** Stream the report's full dataset as CSV (opens natively in Excel). */
    private function exportCsv(array $payload, string $filename)
    {
        return response()->streamDownload(function () use ($payload) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $payload['headers']);
            foreach ($payload['rows'] as $row) {
                // Strip any stray HTML/tags so the CSV holds clean values.
                fputcsv($out, array_map(fn ($c) => trim(strip_tags((string) $c)), $row));
            }
            if (! empty($payload['totals'])) {
                fputcsv($out, array_map(fn ($c) => trim(strip_tags((string) $c)), $payload['totals']));
            }
            fclose($out);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
