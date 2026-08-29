<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\SmsMail\MailService;

/**
 * Delivers an affiliate their system-generated login credentials over email AND SMS
 * (mirrors SendTenantCredentialsJob). Both channels tell the affiliate they'll be asked
 * to set their own password on first login — matching the ForcePasswordChange rule set
 * at registration (User::must_change_password). Credentials are never gated behind the
 * global email toggle (a password they can't receive is a locked-out account) and each
 * channel is fail-safe so one failing never blocks the other. SMS is sent ungated
 * (admin/platform action, no owner to bill).
 */
class SendLoginDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    protected $user;
    protected $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function handle()
    {
        $appName  = getOption('app_name') ?: config('app.name');
        $loginUrl = route('login');

        // Email
        if (! empty($this->user->email)) {
            try {
                $message  = __('Welcome to :app. Here are your account details — Email: :email  Password: :pw.', [
                    'app'   => $appName,
                    'email' => $this->user->email,
                    'pw'    => $this->password,
                ]);
                $message .= ' ' . __('For your security, you will be asked to set your own password when you first sign in. If you have any questions, contact the admin.');
                MailService::sendMail([$this->user->email], __('Your :app Login Details', ['app' => $appName]), $message, $this->user->id);
            } catch (\Throwable $e) {
                Log::error('Affiliate credentials email failed: ' . $e->getMessage());
            }
        }

        // SMS — short, with login URL + password; ungated (no owner to bill).
        if (! empty($this->user->contact_number)) {
            try {
                $msg = __('Welcome to :app. Sign in at :url — Email: :email Pass: :pw. You\'ll set your own password on first login.', [
                    'app'   => $appName,
                    'url'   => $loginUrl,
                    'email' => $this->user->email ?: $this->user->contact_number,
                    'pw'    => $this->password,
                ]);
                SendSmsJob::dispatch([$this->user->contact_number], $msg, null);
            } catch (\Throwable $e) {
                Log::error('Affiliate credentials SMS failed: ' . $e->getMessage());
            }
        }
    }
}
