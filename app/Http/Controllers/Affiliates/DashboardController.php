<?php

namespace App\Http\Controllers\Affiliates;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function dashboard()
    {
           // Dummy summary stats
        $summary = [
            'total_commissions' => 25400.75,
            'total_clients' => 37,
            'recurring_clients' => 22,
            'tier' => 'Gold',
            'tier_rate' => '15%',
            'pending_payout' => 4200.50,
            'total_payouts' => 19800.25,
            'total_referrals' => 15
        ];

        // Dummy commission trends (last 6 months)
        $commissionTrends = [
            ['month' => 'Mar', 'amount' => 3200],
            ['month' => 'Apr', 'amount' => 4500],
            ['month' => 'May', 'amount' => 3800],
            ['month' => 'Jun', 'amount' => 5200],
            ['month' => 'Jul', 'amount' => 6100],
            ['month' => 'Aug', 'amount' => 4100],
        ];

        // Dummy recent commissions table
        $recentCommissions = [
            [
                'date' => '2025-08-14',
                'owner' => 'John Doe',
                'package' => 'Premium Plan',
                'type' => 'New',
                'amount' => 1500.00,
                'status' => 'Paid'
            ],
            [
                'date' => '2025-08-10',
                'owner' => 'Sarah Johnson',
                'package' => 'Basic Plan',
                'type' => 'Recurring',
                'amount' => 800.00,
                'status' => 'Pending'
            ],
            [
                'date' => '2025-08-08',
                'owner' => 'Michael Lee',
                'package' => 'Pro Plan',
                'type' => 'New',
                'amount' => 2000.00,
                'status' => 'Paid'
            ]
        ];

        // Dummy top clients
        $topClients = [
            ['name' => 'John Doe', 'total' => 4500],
            ['name' => 'Sarah Johnson', 'total' => 3800],
            ['name' => 'Michael Lee', 'total' => 3200],
        ];

        // Pass the data to the view
        return view('affiliates.dashboard', compact('summary', 'commissionTrends', 'recentCommissions', 'topClients'));
    }
}
