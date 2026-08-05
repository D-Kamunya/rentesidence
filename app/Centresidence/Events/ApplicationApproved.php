<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceApplication;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Partner approved an application — a finance facility is created from this
 * (handbook §9.3 event map; facility creation lands in WP7).
 */
class ApplicationApproved
{
    use Dispatchable;

    public function __construct(public FinanceApplication $application)
    {
    }
}
