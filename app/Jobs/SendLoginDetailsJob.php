<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\SmsMail\MailService;

class SendLoginDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $password;

    /**
     * Create a new job instance.
     *
     * @param object $user
     * @param string $password
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $message = "Dear {$this->user->first_name}, Welcome to Centresidence Property Management Technologies! Here are your account details:";
        $message .= " Email: {$this->user->email}";
        $message .= " Password: {$this->password}";
        $message .= " Please use these to access your account on centresidence.com and update your information. For any questions, contact the admiin for any assistance";
        MailService::sendMail([$this->user->email], "Your Account Login Details", $message, $this->user->id);
    }
}
