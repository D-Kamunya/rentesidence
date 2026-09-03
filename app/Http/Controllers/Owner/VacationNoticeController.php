<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\VacationNotice;
use App\Services\VacationNoticeService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class VacationNoticeController extends Controller
{
    use ResponseTrait;

    /** Owner acknowledges a tenant's notice to vacate (owner-scoped). */
    public function acknowledge(Request $request, $id)
    {
        $notice = VacationNotice::where('owner_user_id', auth()->id())->find($id);
        if (!$notice) {
            return $this->error([], __('Notice not found.'));
        }
        if ($notice->status !== VacationNotice::STATUS_PENDING) {
            return $this->error([], __('This notice can no longer be acknowledged.'));
        }

        $notice->status          = VacationNotice::STATUS_ACKNOWLEDGED;
        $notice->acknowledged_at = now();
        $notice->save();

        // Close the loop tenant-side: bell + email + SMS (SMS on the owner's credit pool).
        app(VacationNoticeService::class)->notifyTenantAcknowledged($notice);

        return $this->success([], __('Notice acknowledged.'));
    }
}
