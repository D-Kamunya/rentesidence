<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceApplication;
use Illuminate\Foundation\Events\Dispatchable;

/** Partner rejected an application — reason recorded (handbook §9.3 event map). */
class ApplicationRejected
{
    use Dispatchable;

    public function __construct(public FinanceApplication $application)
    {
    }
}
