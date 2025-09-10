<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Saas\FrontendController;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function index(Request $request)
    {
        if (isAddonInstalled('PROTYSAAS') > 1) {
            $frontendController = new FrontendController;
            return $frontendController->index($request);
        }
        return redirect()->route('login');
    }

    public function generateInvoice()
    {
        try {
            Artisan::call('generate:invoice');
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }
}
