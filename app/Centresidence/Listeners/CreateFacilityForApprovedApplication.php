<?php

namespace App\Centresidence\Listeners;

use App\Centresidence\Events\ApplicationApproved;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Services\FinanceFacilityService;

/**
 * On ApplicationApproved, create the finance facility + repayment schedule
 * (handbook §9.3 event map: "Create facility, generate schedule"). Idempotent:
 * skips if a facility already exists for the application.
 */
class CreateFacilityForApprovedApplication
{
    public function __construct(private FinanceFacilityService $facilities)
    {
    }

    public function handle(ApplicationApproved $event): void
    {
        $exists = FinanceFacility::where('finance_application_id', $event->application->id)->exists();
        if ($exists) {
            return;
        }

        $this->facilities->createFromApplication($event->application);
    }
}
