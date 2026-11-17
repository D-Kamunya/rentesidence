<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsSuppression;
use App\Services\SmsMail\SmsGuard;
use Illuminate\Http\Request;

/**
 * Admin management of the SMS suppression list (the deliverability safety net — see SmsGuard).
 * The sibling of EmailSuppressionController. Lets an admin: park a test/placeholder number so it's
 * never sent to, silence a known bad number, or REMOVE a false positive so a genuine number can
 * receive SMS again. Numbers are normalised (e.g. 254700000101) on the way in.
 */
class SmsSuppressionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $suppressions = SmsSuppression::query()
            ->when($q !== '', fn ($query) => $query->where('number', 'like', '%' . SmsGuard::normalize($q) . '%')
                ->orWhere('number', 'like', '%' . $q . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.sms-suppressions.index', [
            'pageTitle'    => __('SMS Suppression'),
            'suppressions' => $suppressions,
            'q'            => $q,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:20'],
            'reason' => ['nullable', 'string', 'max:191'],
        ]);

        $number = SmsGuard::normalize($data['number']);
        if ($number === '') {
            return back()->with('error', __('That doesn\'t look like a valid number.'))->withInput();
        }

        SmsSuppression::firstOrCreate(
            ['number' => $number],
            ['reason' => $data['reason'] ?? __('Added by admin'), 'source' => 'admin'],
        );

        return back()->with('success', __('":number" will no longer receive SMS.', ['number' => $number]));
    }

    /** Un-suppress — the number can receive SMS again (e.g. a false positive). */
    public function destroy($id)
    {
        $row = SmsSuppression::findOrFail($id);
        $number = $row->number;
        $row->delete();

        return back()->with('success', __('Removed ":number" from the suppression list — it can receive SMS again.', ['number' => $number]));
    }
}
