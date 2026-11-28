<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VacancyNotifyRequest;
use Illuminate\Support\Facades\Schema;

/**
 * The aggregated VACANCY DEMAND pool — everyone who asked to be told when a home opens up (the
 * storefront "notify me" / House Watch captures). This is a strategic asset: demand that owners
 * with vacancies want. Read-only for now; a future monetizable segment (see the Spirit-Airlines
 * value lens) — surface/route this demand to owners/affiliates.
 */
class VacancyDemandController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('vacancy_notify_requests')) {
            return view('admin.vacancy-demand.index', ['rows' => null, 'stats' => []]);
        }

        $rows = VacancyNotifyRequest::with([
                'property:id,name',
                'owner:id,first_name,last_name',
            ])
            ->latest()
            ->paginate(30);

        $stats = [
            'total'     => VacancyNotifyRequest::count(),
            'last_7'    => VacancyNotifyRequest::where('created_at', '>=', now()->subDays(7))->count(),
            'with_area' => VacancyNotifyRequest::where(fn ($q) => $q->whereNotNull('area_city')->orWhereNotNull('area_state'))->count(),
            'converted' => VacancyNotifyRequest::whereNotNull('converted_user_id')->count(),
        ];

        return view('admin.vacancy-demand.index', compact('rows', 'stats'));
    }
}
