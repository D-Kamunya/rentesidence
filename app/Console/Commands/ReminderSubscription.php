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
                throw new Exception('Subscription Remainder status inactive');
            }
            $mailService = new MailService;
            $subscriptions = OwnerPackage::whereIn('id', function($q) {
                            $q->selectRaw('MAX(id)')
                            ->from('owner_packages')
                            ->whereIn('status', [ACTIVE])
                            ->groupBy('user_id');
                        })
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
            Log::info('Auto Subscription remainder error: ' . $e->getMessage());
        }
    }

    private function sendReminder($mailService, $subscription, $expired=false)
    {
        $emailData = (object) [
            'subject'   => $expired ? __('Subscription remainder') . ' ' . $subscription->name . ' ' . __('expired ') . ' ' . Carbon::parse($subscription->end_date)->diffForHumans() :  __('Subscription remainder') . ' ' . $subscription->name . ' ' . __('expiring in') . ' ' . Carbon::parse($subscription->end_date)->diffForHumans(),
            'title'     => __('Subscription remainder!'),
            'message'   => $expired ? __('Your subscription expired ' . Carbon::parse($subscription->end_date)->diffForHumans()): __('Your subscription is about to expire in ') . Carbon::parse($subscription->end_date)->diffForHumans(),
        ];
        $notificationData = (object) [
            'title'   => __('Subscription remainder!'),
            'body'     => $expired ? __('Subscription remainder') . ' ' . $subscription->name . ' ' . __('expired ') . ' ' . Carbon::parse($subscription->end_date)->diffForHumans() :  __('Subscription remainder') . ' ' . $subscription->name . ' ' . __('expiring in') . ' ' . Carbon::parse($subscription->end_date)->diffForHumans(),
            'url'     => route('owner.subscription.index')
        ];
        SendSubscriptionReminderJob::dispatch($subscription,$emailData,$notificationData);
        $message = $expired ? __($subscription->name.' Subscription expired ' . Carbon::parse($subscription->end_date)->diffForHumans().'. Renew Subscription: ').route('owner.subscription.index', ['current_plan' => 'no']):
        __($subscription->name.' Subscription expiring in' . ' ' . Carbon::parse($subscription->end_date)->diffForHumans().'. Renew Subscription: ').route('owner.subscription.index', ['current_plan' => 'no']) ;
        SendSmsJob::dispatch([$subscription->owner->contact_number], $message, $subscription->user_id);
    }
}
