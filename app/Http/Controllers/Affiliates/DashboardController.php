<?php

namespace App\Http\Controllers\Affiliates;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        // For now, mock some data for agents and affiliate earnings.
        $owners = [
            ['name' => 'John Doe', 'package' => '1-50 Units', 'package_cost' => 5000, 'status' => 'active'],
            ['name' => 'Jane Smith', 'package' => '51-100 Units', 'package_cost' => 10000, 'status' => 'active'],
            ['name' => 'Bob Lee', 'package' => '101-150 Units', 'package_cost' => 15000, 'status' => 'dormant'],
        ];

        // For demonstration, you can calculate 7% of each owner's package
        foreach ($owners as &$owner) {
            $owner['affiliate_earning'] = $owner['package_cost'] * 0.07;
        }

        // Pass the data to the view
        return view('affiliates.dashboard', compact('owners'));
    }
}
