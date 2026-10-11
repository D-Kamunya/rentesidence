<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSuppression;
use Illuminate\Http\Request;

/**
 * Admin management of the email suppression list (the deliverability safety net — see EmailGuard).
 * Lets an admin: park a test/placeholder address so it's never sent to, silence a known
 * hard-bouncer, or REMOVE a false positive so a genuine address can receive mail again.
 */
class EmailSuppressionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $suppressions = EmailSuppression::query()
            ->when($q !== '', fn ($query) => $query->where('email', 'like', '%' . $q . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.email-suppressions.index', [
            'pageTitle'     => __('Email Suppression'),
            'suppressions'  => $suppressions,
            'q'             => $q,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'  => ['required', 'email', 'max:191'],
            'reason' => ['nullable', 'string', 'max:191'],
        ]);

        $email = strtolower(trim($data['email']));

        EmailSuppression::firstOrCreate(
            ['email' => $email],
            ['reason' => $data['reason'] ?? __('Added by admin'), 'source' => 'admin'],
        );

        return back()->with('success', __('":email" will no longer receive automated email.', ['email' => $email]));
    }

    /** Un-suppress — the address can receive mail again (e.g. a false positive). */
    public function destroy($id)
    {
        $row = EmailSuppression::findOrFail($id);
        $email = $row->email;
        $row->delete();

        return back()->with('success', __('Removed ":email" from the suppression list — it can receive email again.', ['email' => $email]));
    }
}
