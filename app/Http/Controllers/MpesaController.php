<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MpesaController extends Controller
{

    
    public function confirm (Request $request){
        Log::info($request->getContent());
    }
}
