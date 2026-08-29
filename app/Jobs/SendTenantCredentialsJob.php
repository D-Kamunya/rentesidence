<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SmsMail\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * THE single path for delivering a tenant their login credentials — used by single-tenant
 * creation and the "resend login details" actions alike (bulk import has its own per-row
 * invite job, SendTenantImportInvite, which shares this same shape). Sends over email AND/OR
 * SMS so a tenant with no email still receives their password. SMS goes through SendSmsJob →
 * AdvantaSmsService, which deducts one SMS credit atomically and simply won't send when the
 * balance is exhausted — so a resend can never overspend.
 *
 * Credentials are NOT gated behind the global `send_email_status` toggle: a password the tenant
 * can't receive is a locked-out tenant, so this always attempts delivery on the chosen channels.
 */
class SendTenantCredentialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public function __construct(
        public int $userId,
        public string $plainPassword,
        public string $channel = 'both' // email | sms | both
    ) {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        $ownerUserId = (int) ($user->owner_user_id ?: 0);
        $appName     = getOption('app_name') ?: config('app.name');
        $loginUrl    = route('login');

        // Email — the branded sign-up mail appends the password.
        if (in_array($this->channel, ['email', 'both'], true) && ! empty($user->email)) {
            try {
                $subject = __('Your :app login details', ['app' => $appName]);
                $message = __('Welcome to :app. Sign in with your email and the password below, then set your own password when prompted.', ['app' => $appName]);
                MailService::sendSignUpMail([$user->email], $subject, $message, $ownerUserId, $this->plainPassword);
            } catch (\Throwable $e) {
                Log::error('Tenant credentials email failed: ' . $e->getMessage());
            }
        }

        // SMS — short, with the login URL + password; credit-gated downstream.
        if (in_array($this->channel, ['sms', 'both'], true) && ! empty($user->contact_number)) {
            try {
                $msg = __('Welcome to :app. Sign in at :url — Email: :email Pass: :pw. You\'ll set your own password on first login.', [
                    'app'   => $appName,
                    'url'   => $loginUrl,
                    'email' => $user->email ?: $user->contact_number,
                    'pw'    => $this->plainPassword,
                ]);
                SendSmsJob::dispatch([$user->contact_number], $msg, $ownerUserId);
            } catch (\Throwable $e) {
                Log::error('Tenant credentials SMS failed: ' . $e->getMessage());
            }
        }
    }
}
