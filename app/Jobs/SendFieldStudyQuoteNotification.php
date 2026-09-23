<?php

namespace App\Jobs;

use App\Centresidence\Models\FieldStudyRequest;
use App\Mail\Concerns\SendsCsMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Tell the owner their site-survey quotation is ready — in-app + CS email (with the figures) + SMS
 * — so an offline owner knows to log in and accept. Acceptance itself stays behind login (it starts
 * a financing application), so every channel just points at the surveys page. The SMS is
 * PLATFORM-PAID (null ownerUserId): this is our quote to them, a platform→owner notice, not the
 * owner's tenant-SMS credits. tries=1 + per-channel try/catch so a partial failure never re-charges.
 */
class SendFieldStudyQuoteNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendsCsMail;

    public int $tries = 1;

    public function __construct(public int $requestId) {}

    public function handle(): void
    {
        $fsr = FieldStudyRequest::with(['module', 'property'])->find($this->requestId);
        if (! $fsr || ! $fsr->isQuoted()) {
            return;
        }
        $owner = User::find($fsr->owner_id);
        if (! $owner) {
            return;
        }

        $app     = getOption('app_name') ?: 'Centresidence';
        $module  = optional($fsr->module)->name ?? __('your installation');
        $amount  = currencyPrice($fsr->quoted_amount);
        $url     = route('owner.financing.surveys');

        // In-app
        try {
            addNotification(
                __('Your site-survey quote is ready'),
                __('We have quoted :amt for your :module install. Review it to arrange financing.', ['amt' => $amount, 'module' => $module]),
                $url, null, $owner->id,
            );
        } catch (\Throwable $e) {
            Log::error('Field-study quote in-app failed', ['request_id' => $fsr->id, 'error' => $e->getMessage()]);
        }

        // Email (CS layout) — carries the figures.
        try {
            if ($owner->email) {
                $first = e($owner->first_name ?: __('there'));
                $this->sendCs(
                    [$owner->email],
                    __('Your :module quotation is ready', ['module' => $module]),
                    [
                        'eyebrow' => __('Site survey'), 'eyebrowColor' => '#0F6E56',
                        'title'   => __('Your quotation is ready'),
                        'blocks'  => [
                            ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>{$first}</strong>"])
                                . ' ' . __('we have completed the site survey and prepared your quotation.')],
                            ['type' => 'panel', 'variant' => 'green', 'title' => __('Quotation'), 'rows' => array_values(array_filter([
                                ['k' => __('Installation'), 'v' => $module],
                                ['k' => __('Property'),     'v' => optional($fsr->property)->name],
                                ['k' => __('Units'),        'v' => $fsr->units],
                                ['k' => __('Amount'),       'v' => $amount, 'amount' => true],
                            ], fn ($r) => $r['v'] !== '' && $r['v'] !== null))],
                            $fsr->quote_note ? ['type' => 'note', 'text' => e($fsr->quote_note)] : ['type' => 'text', 'html' => ''],
                            ['type' => 'text', 'html' => __('Sign in to review and accept your quote, then choose a financier.')],
                            ['type' => 'button', 'url' => $url, 'label' => __('View & accept your quote')],
                        ],
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error('Field-study quote email failed', ['request_id' => $fsr->id, 'error' => $e->getMessage()]);
        }

        // SMS — platform-paid, link-free (email carries the link).
        try {
            if (! empty($owner->contact_number)) {
                $msg = __(':app: your :module site-survey quote is ready (:amt). Log in to review and accept.', ['app' => $app, 'module' => $module, 'amt' => $amount]);
                SendSmsJob::dispatch([$owner->contact_number], $msg, null);
            }
        } catch (\Throwable $e) {
            Log::error('Field-study quote SMS failed', ['request_id' => $fsr->id, 'error' => $e->getMessage()]);
        }
    }
}
