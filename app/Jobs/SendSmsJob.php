<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\SmsMail\AdvantaSmsService;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $numbers;
    protected $message;
    protected $ownerUserId;

    /**
     * Create a new job instance.
     *
     * @param array $numbers
     * @param string $message
     * @param int|null $ownerUserId
     */
    public function __construct($numbers, $message, $ownerUserId = null)
    {
        $this->numbers = $numbers;
        $this->message = $message;
        $this->ownerUserId = $ownerUserId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        AdvantaSmsService::sendSms($this->numbers, $this->message, $this->ownerUserId);
    }
}
