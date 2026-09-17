<?php

namespace App\Jobs;

use App\Mail\Concerns\SendsCsMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Reaches an invited landlord on the tenant's behalf when the tenant uses the direct-invite
 * form (phone / email). Two channels only — the landlord isn't a user yet, so there's no
 * in-app bell:
 *   - Email: on-brand CS lifecycle mail with a CTA to the /invite/{code} landing.
 *   - SMS: platform-paid (ownerUserId = null) and link-carrying (the landlord has no other
 *     way in), kept short. This is a customer-acquisition cost, so the platform pays.
 *
 * No account, no credentials — the landlord still fills the vetted public form. Fail-safe per
 * channel: a failure on one never blocks the other or the invite record.
 */
class SendLandlordInviteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsCsMail;

    public function __construct(
        private ?string $referrerName,
        private string $inviteUrl,
        private ?string $phone = null,
        private ?string $email = null,
        private ?string $inviteeName = null,
    ) {
    }

    public function handle(): void
    {
        $who   = $this->referrerName ? trim($this->referrerName) : __('Your tenant');
        $hello = $this->inviteeName ? __('Hello :name,', ['name' => e($this->inviteeName)]) : __('Hello,');

        // ── Email (on-brand, gated inside sendCs) ───────────────────────────────
        if ($this->email) {
            try {
                $this->sendCs(
                    [$this->email],
                    __(':who invited you to Centresidence', ['who' => $who]),
                    [
                        'eyebrow'      => __('Invitation'), 'eyebrowColor' => '#185FA5',
                        'title'        => __('Manage your property the modern way'),
                        'preheader'    => __(':who thinks Centresidence would work for your rentals.', ['who' => $who]),
                        'blocks'       => [
                            ['type' => 'text', 'html' => $hello . ' ' . __(':who uses Centresidence to handle their rent and invited you to put your property on it — collect rent from your tenants\' phones (M-Pesa included), send receipts automatically, and see every unit at a glance. It\'s free to start.', ['who' => "<strong>" . e($who) . "</strong>"])],
                            ['type' => 'button', 'url' => $this->inviteUrl, 'label' => __('Get set up')],
                            ['type' => 'note', 'html' => __('Our team reviews every request and will reach out to help you get going. No account is created until you complete the short form.')],
                        ],
                    ]
                );
            } catch (\Throwable $e) {
                Log::channel('sms-mail')->info('Landlord invite email failed: ' . $e->getMessage());
            }
        }

        // ── SMS (platform-paid, link-carrying) ──────────────────────────────────
        if ($this->phone) {
            try {
                $msg = __(':who invited you to Centresidence to manage your rent. Get started: :url', [
                    'who' => $who,
                    'url' => $this->inviteUrl,
                ]);
                SendSmsJob::dispatch([$this->phone], $msg, null);
            } catch (\Throwable $e) {
                Log::channel('sms-mail')->info('Landlord invite SMS failed: ' . $e->getMessage());
            }
        }
    }
}
