<?php
// app/Jobs/Mail/SendFreeActivatedMail.php
namespace App\Jobs\Mail;

use App\Models\Lead;
use App\Models\User;

/**
 * Fires when a lead ACCEPTS a "Set up on Free" invite and the owner account is created.
 *
 *   - Owner: a short on-brand welcome (they already chose their password, so no credentials).
 *   - Affiliate: email + an in-app bell confirming their lead is now live on Free. The bell's
 *     sender is the NEW OWNER's user (a real user) — the notification feed inner-joins users on
 *     sender_id, so a null/system sender would be saved but never shown.
 */
class SendFreeActivatedMail extends BaseMailJob
{
    public function __construct(
        public int $leadId,
        public int $ownerUserId,
    ) {}

    public function handle(): void
    {
        $lead = Lead::with(['company', 'affiliate'])->find($this->leadId);
        if (! $lead) {
            return;
        }

        $appName   = getOption('app_name');
        $owner     = User::find($this->ownerUserId);
        $company   = optional($lead->company)->company_name ?? trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
        $companyE  = e($company);

        // 1. Owner — welcome (no credentials; they set their own password accepting the invite).
        if ($owner && $owner->email) {
            $this->sendCs(
                [$owner->email],
                __('Welcome to :app', ['app' => $appName]),
                [
                    'eyebrow' => __('Welcome'), 'eyebrowColor' => '#0F6E56',
                    'title'   => __('Your free account is live'),
                    'blocks'  => [
                        ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>" . e($owner->first_name ?: $company) . "</strong>"])
                            . ' ' . __('Your free :app account is ready. Sign in any time to add your properties and start collecting rent.', ['app' => $appName])],
                        ['type' => 'button', 'url' => route('login'), 'label' => __('Sign in')],
                        ['type' => 'note', 'text' => __('Your plan is free, with nothing to pay. You only ever pay for extras you choose to switch on.')],
                    ],
                ]
            );
        }

        // 2. Affiliate — email + in-app bell (sender = the new owner, a real user).
        $affiliate = $lead->affiliate; // leads.affiliate_id -> users.id
        if ($affiliate && $affiliate->email) {
            $this->sendCs(
                [$affiliate->email],
                __('Your lead is live on Free') . ' - ' . $company . ' | ' . $appName,
                [
                    'eyebrow' => __('Account activated'), 'eyebrowColor' => '#0F6E56',
                    'title'   => __('Account activated on Free'),
                    'blocks'  => [
                        ['type' => 'text', 'html' => __('Hello :name,', ['name' => "<strong>" . e($affiliate->first_name ?: '') . "</strong>"])
                            . ' ' . __(':company accepted your invite and is now live on the free plan. Follow up to help them add their first property — that\'s where your earnings begin.', ['company' => "<strong>{$companyE}</strong>"])],
                        ['type' => 'button', 'url' => route('affiliate.leads.show', $lead->id), 'label' => __('View lead')],
                    ],
                ]
            );

            addNotification(
                __('Lead activated on Free'),
                $company . ' ' . __('accepted your invite and is now on the free plan.'),
                route('affiliate.leads.show', $lead->id),
                null,
                $affiliate->id,          // recipient
                $this->ownerUserId,      // sender — a real user, so the bell renders
            );
        }
    }
}
