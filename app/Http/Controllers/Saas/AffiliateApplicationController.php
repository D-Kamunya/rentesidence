<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\AffiliateApplication;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Public "become an affiliate" page — the shareable link the team sends to prospects in the field.
 * A submission creates an AffiliateApplication (never an account) and pings admin; admin turns it
 * into a full affiliate in one click from their inbox.
 */
class AffiliateApplicationController extends Controller
{
    public function create()
    {
        return view('affiliate-apply.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name'  => 'required|string|max:60',
            'email'      => 'required|email|max:120',
            'phone'      => 'required|string|max:20',
            'location'   => 'nullable|string|max:120',
            'pitch'      => 'nullable|string|max:2000',
            'agree'      => 'accepted',
        ], [
            'agree.accepted' => __('Please confirm you\'d like to join to continue.'),
        ]);

        // Already an affiliate? Point them to sign in rather than creating a duplicate application.
        $existingAffiliate = User::where('email', $data['email'])->where('role', USER_ROLE_AFFILIATE)->exists();
        if ($existingAffiliate) {
            return redirect()->route('affiliate.apply')->with('already', true);
        }

        // Idempotent: a pending application with this email just re-confirms (no duplicates).
        $existing = AffiliateApplication::where('email', $data['email'])
            ->where('status', AffiliateApplication::STATUS_PENDING)->first();

        if (! $existing) {
            $application = AffiliateApplication::create($data + ['status' => AffiliateApplication::STATUS_PENDING]);
            $this->notifyAdmins($application);
        }

        return redirect()->route('affiliate.apply')->with('applied', true);
    }

    /** In-app notification to every admin that a new application arrived. */
    private function notifyAdmins(AffiliateApplication $application): void
    {
        User::where('role', USER_ROLE_ADMIN)->pluck('id')->each(function ($adminId) use ($application) {
            addNotification(
                __('New affiliate application'),
                $application->name . ' — ' . $application->email,
                route('admin.affiliates.applications.index'),
                null,
                $adminId,
                null
            );
        });
    }
}
