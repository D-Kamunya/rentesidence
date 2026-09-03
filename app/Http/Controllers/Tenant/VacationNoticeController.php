<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\VacationNoticeService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class VacationNoticeController extends Controller
{
    use ResponseTrait;

    /** Tenant files a notice to vacate. Early move-out is allowed but flagged by the service. */
    public function store(Request $request)
    {
        $request->validate([
            'intended_move_out_date' => 'required|date',
            'message'                => 'nullable|string|max:1000',
        ]);

        $tenant = optional(auth()->user())->tenant;
        if (!$tenant || (int) $tenant->status !== TENANT_STATUS_ACTIVE) {
            return $this->error([], __('No active tenancy found.'));
        }

        $res = app(VacationNoticeService::class)->fileNotice(
            $tenant,
            $request->intended_move_out_date,
            $request->message
        );

        return $res['ok']
            ? $this->success(['meets_notice' => $res['meets_notice'] ?? true], $res['message'])
            : $this->error([], $res['message']);
    }
}
