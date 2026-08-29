<?php

namespace App\Jobs;

use App\Models\TenantImport;
use App\Models\User;
use App\Services\SmsMail\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Sends ONE imported tenant their login invite over the owner's chosen channel(s). Runs as a
 * queued job (one per new tenant) so a large import never blasts thousands of messages in a
 * single request. SMS is routed through SendSmsJob → AdvantaSmsService, which deducts an SMS
 * credit atomically and simply won't send if the balance is exhausted — so invites can never
 * overspend, even though the confirm step already pre-checks the credit budget.
 */
class SendTenantImportInvite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public function __construct(
        public int $importId,
        public int $userId,
        public ?string $plainPassword,
        public string $channel // email | sms | both
    ) {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            $this->bump('invites_failed');
            return;
        }

        $ownerUserId = (int) $user->owner_user_id;
        $appName     = getOption('app_name') ?: config('app.name');
        $ownerName   = optional(User::find($ownerUserId))->name ?: $appName;
        $loginUrl    = route('login');
        $sentAny     = false;

        // Email invite — welcomes the tenant to the new system + explains the shift, then
        // gives their login (the sign-up mail appends the generated password).
        if (in_array($this->channel, ['email', 'both'], true) && ! empty($user->email)) {
            try {
                $subject = __(':owner is now on :app — your tenant login', ['owner' => $ownerName, 'app' => $appName]);
                $message = __(':owner now manages your tenancy on :app. From now on you can view your rent invoices, pay online, and get receipts in one place. Sign in with your email and the password below, then set your own password.', ['owner' => $ownerName, 'app' => $appName]);
                MailService::sendSignUpMail([$user->email], $subject, $message, $ownerUserId, $this->plainPassword);
                $sentAny = true;
            } catch (\Throwable $e) {
                Log::error('Tenant import invite email failed: ' . $e->getMessage());
            }
        }

        // SMS invite — short + educative; credit-gated downstream (AdvantaSmsService::sendSms
        // deducts one credit and logs "Insufficient SMS credits" instead of sending when empty).
        if (in_array($this->channel, ['sms', 'both'], true) && ! empty($user->contact_number)) {
            try {
                $pw  = $this->plainPassword ? ' ' . __('Pass') . ': ' . $this->plainPassword : '';
                $msg = __(':owner now manages your rent on :app — view & pay invoices online. Login: :url', [
                    'owner' => $ownerName, 'app' => $appName, 'url' => $loginUrl,
                ]) . $pw;
                SendSmsJob::dispatch([$user->contact_number], $msg, $ownerUserId);
                $sentAny = true;
            } catch (\Throwable $e) {
                Log::error('Tenant import invite SMS failed: ' . $e->getMessage());
            }
        }

        $this->bump($sentAny ? 'invites_sent' : 'invites_failed');
    }

    /** Atomic counter bump on the import so the progress UI can show invite drain. */
    private function bump(string $column): void
    {
        TenantImport::where('id', $this->importId)->increment($column);
    }
}
