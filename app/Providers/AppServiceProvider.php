<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\Setting;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Builder;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        // Share the "ownerless" (standalone Tenant Helper) flag to every tenant view so the sidebar,
        // dashboard and guards all read one source of truth. Cheap: ->tenant is loaded once/request.
        \Illuminate\Support\Facades\View::composer('tenant.*', function ($view) {
            $view->with('ownerless', optional(auth()->user())->isOwnerlessTenant() ?? false);
        });

        // Sidebar count badges — actionable "N waiting" numbers, computed once per request
        // per role's sidebar (a count of 0 renders nothing).
        \Illuminate\Support\Facades\View::composer('owner.layouts.sidebar', function ($view) {
            $uid = auth()->id();
            $view->with('navBadges', $uid ? app(\App\Services\NavBadgeService::class)->forOwner((int) $uid) : []);
        });
        \Illuminate\Support\Facades\View::composer('tenant.layouts.sidebar', function ($view) {
            $user = auth()->user();
            $tenant = optional($user)->tenant;
            $view->with('navBadges', $tenant
                ? app(\App\Services\NavBadgeService::class)->forTenant((int) $tenant->id, (int) $user->id)
                : []);
        });
        \Illuminate\Support\Facades\View::composer('affiliate.layouts.sidebar', function ($view) {
            $uid = auth()->id();
            $view->with('navBadges', $uid ? app(\App\Services\NavBadgeService::class)->forAffiliate((int) $uid) : []);
        });
        // Finance-partner sidebar is inline in its app layout, so the badges attach to that view.
        \Illuminate\Support\Facades\View::composer('finance-partner.layouts.app', function ($view) {
            $uid = auth()->id();
            $view->with('navBadges', $uid ? app(\App\Services\NavBadgeService::class)->forFinancePartner((int) $uid) : []);
        });
        \Illuminate\Support\Facades\View::composer('admin.layouts.sidebar', function ($view) {
            $view->with('navBadges', app(\App\Services\NavBadgeService::class)->forAdmin());
        });

        // "What's new" feature-announcement modal — the unseen announcements for the current user
        // (role-targeted). Schema-guarded so it degrades cleanly on a bare/pre-migration install.
        \Illuminate\Support\Facades\View::composer('partials.feature-announcement', function ($view) {
            $user = auth()->user();
            $unseen = ($user && \Illuminate\Support\Facades\Schema::hasTable('feature_announcements'))
                ? \App\Models\FeatureAnnouncement::unseenFor($user)
                : collect();
            $view->with('featureAnnouncements', $unseen);
        });

        // Graduation account switch — offer the "Switch to tenant/affiliate" control in the
        // navbar only when the current user has a linked counterpart account.
        \Illuminate\Support\Facades\View::composer(['tenant.layouts.navbar', 'affiliate.layouts.navbar'], function ($view) {
            $view->with('accountSwitch', app(\App\Services\AffiliateGraduationService::class)->switchTargetFor(auth()->user()));
        });

        // ── System incident capture (queue + scheduler) ─────────────────────────────
        // A background job or nightly command that dies after exhausting its retries is a
        // GENUINE failure (retry-exhaustion is the "not a transient blip" gate) — surface it
        // to admin. Only a curated critical set counts, so ordinary job noise never pages.
        $this->registerIncidentHooks();
        try {
            Builder::defaultStringLength(191);
            $connection = DB::connection()->getPdo();
            if ($connection) {
                $allOptions = [];
                $allOptions['settings'] = Setting::all()->pluck('option_value', 'option_key')->toArray();
                config($allOptions);
                config(['app.name' => getOption('app_name')]);

                // Fetch the default currency from the database
                $defaultCurrencySetting = getOption('currency_id');
                session(['default_currency' => config('app.default_currency')]);
                if ($defaultCurrencySetting) {
                    $currency = Currency::where('id', $defaultCurrencySetting)->first();
                    $defaultCurrency = $currency->symbol;
                    config(['app.default_currency' => $defaultCurrency]);
                    session(['default_currency' => $defaultCurrency]);
                }
            }
        } catch (\Exception $e) {
            //
        }
    }

    /**
     * Wire the queue + scheduler failure listeners to the incident recorder. A job or
     * scheduled command only reaches these AFTER it has spent its retries, so a one-off
     * network blip that succeeds on retry never lands here — the failure is real.
     */
    private function registerIncidentHooks(): void
    {
        // Curated critical jobs → the type/label they surface as. Anything not listed
        // is left to ordinary logging; we don't page for non-critical background noise.
        $criticalJobs = [
            \App\Jobs\SendLoginDetailsJob::class          => ['comms_failure', 'Login credentials could not be delivered'],
            \App\Jobs\SendTenantCredentialsJob::class     => ['comms_failure', 'Tenant credentials could not be delivered'],
            \App\Jobs\SendSmsJob::class                   => ['comms_failure', 'SMS delivery is failing'],
            \App\Jobs\SendInvoiceNotificationAndEmailJob::class => ['job_failed', 'Invoice notification job failed'],
        ];

        \Illuminate\Support\Facades\Queue::failing(function (\Illuminate\Queue\Events\JobFailed $event) use ($criticalJobs) {
            try {
                $name  = method_exists($event->job, 'resolveName') ? $event->job->resolveName() : get_class($event->job);
                if (! isset($criticalJobs[$name])) {
                    return;
                }
                [$type, $title] = $criticalJobs[$name];
                $short = class_basename($name);

                app(\App\Services\SystemIncidentService::class)->report(
                    $type,
                    \App\Models\SystemIncident::SEVERITY_CRITICAL,
                    $title,
                    'Background job ' . $short . ' failed after exhausting its retries: '
                        . \Illuminate\Support\Str::limit(optional($event->exception)->getMessage() ?? '', 300),
                    ['job' => $short, 'connection' => $event->connectionName],
                    'job:' . $short
                );
            } catch (\Throwable $e) {
                // never let the failure handler itself throw
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Console\Events\ScheduledTaskFailed::class, function ($event) {
            try {
                $summary = method_exists($event->task, 'getSummaryForDisplay') ? $event->task->getSummaryForDisplay() : 'scheduled task';

                app(\App\Services\SystemIncidentService::class)->report(
                    \App\Models\SystemIncident::TYPE_SCHEDULE_FAILED,
                    \App\Models\SystemIncident::SEVERITY_CRITICAL,
                    'Scheduled task failed',
                    'Scheduled command failed: ' . $summary . ' — '
                        . \Illuminate\Support\Str::limit(optional($event->exception ?? null)->getMessage() ?? '', 300),
                    ['task' => $summary],
                    'schedule:' . $summary
                );
            } catch (\Throwable $e) {
                //
            }
        });
    }
}
