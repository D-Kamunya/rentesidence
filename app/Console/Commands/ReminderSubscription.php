<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\OwnerPackage;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsJob;
use App\Jobs\SendSubscriptionReminderJob;

class ReminderSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send subscription reminder emails and notifications to owners';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            if (getOption('subscription_remainder_status', 0) != SUBSCRIPTION_REMAINDER_STATUS_ACTIVE && getOption('SUBSCRIPTION_OVERDUE_REMAINDER_STATUS', 0) != SUBSCRIPTION_REMAINDER_STATUS_ACTIVE) {
                throw new Exception('Subscription Reminder status inactive');
            }
            $mailService = new MailService;
            // Only owners on a SUBSCRIPTION package have something to renew. Free and
            // transaction owners (transaction infra is charged from the rent flow, not a
            // recurring subscription) must NOT get "your subscription is expiring / renew"
            // reminders. Matches the canonical filter in SubscriptionService / OwnerBillingStandingService.
            $subscriptions = OwnerPackage::whereIn('id', function($q) {
                            $q->selectRaw('MAX(id)')
                            ->from('owner_packages')
                            ->whereIn('status', [ACTIVE])
                            ->groupBy('user_id');
                        })
                        ->where('pricing_model', 'subscription')
                        ->get();

            $sendEveryday = getOption('subscription_remainder_everyday_status') == SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE;
            $reminderDays = explode(',', getOption('subscription_reminder_days'));
            $sendEverydayOverDue = getOption('SUBSCRIPTION_OVERDUE_REMAINDER_EVERYDAY_STATUS') == SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE;
            $reminderDaysOverDue = explode(',', getOption('SUBSCRIPTION_OVERDUE_REMAINDER_DAYS'));
            foreach ($subscriptions as $subscription) {
                $dueDate = Carbon::parse($subscription->end_date)->startOfDay();
                $diffDay = $dueDate->diffInDays(today());
                if (getOption('subscription_remainder_status', 0) == SUBSCRIPTION_REMAINDER_STATUS_ACTIVE) {
                    if ($sendEveryday && $dueDate >= today()) {
                        $this->sendReminder($mailService, $subscription);
                    } elseif (!$sendEveryday && in_array($diffDay, $reminderDays) && $dueDate >= today()) {
                        $this->sendReminder($mailService, $subscription);
                    }
                }

                if (getOption('SUBSCRIPTION_OVERDUE_REMAINDER_STATUS', 0) == SUBSCRIPTION_REMAINDER_STATUS_ACTIVE) {
                    if ($sendEverydayOverDue && $dueDate <= today()) {
                        $this->sendReminder($mailService, $subscription,true);
                    } elseif (!$sendEverydayOverDue && in_array($diffDay, $reminderDaysOverDue) && $dueDate <= today()) {
                        $this->sendReminder($mailService, $subscription,true);
                    }
                }
            }
        } catch (Exception $e) {
            Log::info('Auto Subscription reminder error: ' . $e->getMessage());
        }
    }

    private function sendReminder($mailService, $subscription, $expired=false)
    {
        $when     = Carbon::parse($subscription->end_date)->diffForHumans();
        $renewUrl = route('owner.subscription.index', ['current_plan' => 'no']);

        $emailData = (object) [
            'subject'   => $expired
                ? __('Your :name subscription has expired', ['name' => $subscription->name])
                : __('Your :name subscription is expiring soon', ['name' => $subscription->name]),
            'title'     => __('Subscription reminder'),
            'message'   => $expired
                ? __('Your subscription expired :when.', ['when' => $when])
                : __('Your subscription expires :when.', ['when' => $when]),
        ];
        $notificationData = (object) [
            'title'   => __('Subscription reminder'),
            'body'    => $expired
                ? __('Your :name subscription expired :when.', ['name' => $subscription->name, 'when' => $when])
                : __('Your :name subscription expires :when.', ['name' => $subscription->name, 'when' => $when]),
            'url'     => route('owner.subscription.index'),
        ];
        SendSubscriptionReminderJob::dispatch($subscription,$emailData,$notificationData);

        $message = $expired
            ? __('Your :name subscription expired :when. Renew: :url', ['name' => $subscription->name, 'when' => $when, 'url' => $renewUrl])
            : __('Your :name subscription expires :when. Renew: :url', ['name' => $subscription->name, 'when' => $when, 'url' => $renewUrl]);
        SendSmsJob::dispatch([$subscription->owner->contact_number], $message, $subscription->user_id);
    }
}
