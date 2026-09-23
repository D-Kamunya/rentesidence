<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateApplication;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Admin inbox for prospective-affiliate applications + one-click conversion into a full account. */
class AffiliateApplicationController extends Controller
{
    public function __construct(private AffiliateService $affiliates)
    {
    }

    public function index(Request $request)
    {
        $status = $request->input('status', AffiliateApplication::STATUS_PENDING);

        $applications = AffiliateApplication::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => AffiliateApplication::where('status', AffiliateApplication::STATUS_PENDING)->count(),
            'approved' => AffiliateApplication::where('status', AffiliateApplication::STATUS_APPROVED)->count(),
            'rejected' => AffiliateApplication::where('status', AffiliateApplication::STATUS_REJECTED)->count(),
        ];

        return view('admin.affiliates.applications.index', [
            'applications' => $applications,
            'counts'       => $counts,
            'status'       => $status,
            'pageTitle'    => __('Affiliate Applications'),
        ]);
    }

    /**
     * One click: turn an approved application into a full affiliate account. Reuses the single
     * source of truth (AffiliateService::registerAffiliate) — temp password, email+SMS creds,
     * forced reset on first login — then marks the application converted.
     */
    public function approve(AffiliateApplication $application)
    {
        if ($application->status !== AffiliateApplication::STATUS_PENDING) {
            return back()->with('error', __('This application has already been handled.'));
        }
        if (User::where('email', $application->email)->exists()) {
            return back()->with('error', __('An account already exists with this email address.'));
        }

        try {
            $user = $this->affiliates->registerAffiliate([
                'first_name'     => $application->first_name,
                'last_name'      => $application->last_name,
                'email'          => $application->email,
                'contact_number' => $application->phone,
            ]);

            $application->update([
                'status'            => AffiliateApplication::STATUS_APPROVED,
                'converted_user_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Affiliate application approval failed: ' . $e->getMessage(), ['application_id' => $application->id]);
            return back()->with('error', __('Could not create the affiliate account. Please try again.'));
        }

        // DEV ONLY: surface the temp password locally (no live email/SMS in dev).
        if (config('app.debug')) {
            session()->flash('dev_credentials', [
                'name'  => $application->name,
                'email' => $application->email,
                'note'  => __('Affiliate account created — credentials sent by email + SMS.'),
            ]);
        }

        return back()->with('success', __('Affiliate account created for :name — login details sent by email and SMS.', ['name' => $application->name]));
    }

    public function reject(Request $request, AffiliateApplication $application)
    {
        if ($application->status !== AffiliateApplication::STATUS_PENDING) {
            return back()->with('error', __('This application has already been handled.'));
        }

        $application->update([
            'status'     => AffiliateApplication::STATUS_REJECTED,
            'admin_note' => $request->input('reason'),
        ]);

        return back()->with('success', __('Application declined.'));
    }
}
