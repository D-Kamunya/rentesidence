<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceApplication;
use Illuminate\Foundation\Events\Dispatchable;

/** Owner submitted an application — partner notified (handbook §9.3 event map). */
class ApplicationSubmitted
{
    use Dispatchable;

    public function __construct(public FinanceApplication $application)
    {
    }
}
