<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceApplication;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired on every finance application status transition (handbook §9.3 event
 * map). Carries the application plus the from/to statuses.
 */
class ApplicationStatusChanged
{
    use Dispatchable;

    public function __construct(
        public FinanceApplication $application,
        public ?string $fromStatus,
        public string $toStatus
    ) {
    }
}
