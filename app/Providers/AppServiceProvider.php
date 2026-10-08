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
        // Passport's oauth_* tables already exist on our databases and are managed outside our
        // migration set. Stop Passport auto-loading its 2016 create-migrations so a plain
        // `php artisan migrate` doesn't choke on "table already exists" on every deploy.
        if (class_exists(\Laravel\Passport\Passport::class)) {
            \Laravel\Passport\Passport::ignoreMigrations();
        }
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
     *
     * NOTE on comms: credential/SMS jobs deliberately CATCH their own delivery errors
     * (one channel failing must never block the other), so they never "fail" here — the
     * genuine "user got no credentials" incident is raised inside those jobs instead.
     * This queue hook is the catch-all safety net for jobs that genuinely die.
     */
    private function registerIncidentHooks(): void
    {
        // Any job that exhausts its retries and dies is a genuine background failure worth
        // surfacing — but a dead background job is not, by itself, a wake-the-admin event,
        // so it opens as a WARNING (visible on the dashboard, no SMS). The money/comms paths
        // that DO warrant a page are captured at their own hooks (callbacks, credential jobs).
        \Illuminate\Support\Facades\Queue::failing(function (\Illuminate\Queue\Events\JobFailed $event) {
            try {
                $name  = method_exists($event->job, 'resolveName') ? $event->job->resolveName() : get_class($event->job);
                $short = class_basename($name);

                app(\App\Services\SystemIncidentService::class)->report(
                    \App\Models\SystemIncident::TYPE_JOB_FAILED,
                    \App\Models\SystemIncident::SEVERITY_WARNING,
                    'Background job failing: ' . $short,
                    'Job ' . $short . ' failed after exhausting its retries: '
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

        // Deliverability safety net: on EVERY outbound email, suppress sends to dead/test/
        // already-flagged addresses (EmailGuard) so automated mail can't erode sender reputation —
        // and surface each suppression on System Health so a human can review (and un-suppress a
        // false positive). FAIL-OPEN: a guard error must never block legitimate mail.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, function ($event) {
            // Sender identity: make every automated email say WHO it's from. On many deploys
            // MAIL_FROM_NAME is left as a bare "no-reply" literal, so inboxes show "no-reply"
            // with no brand — unprofessional. Force the From DISPLAY NAME to the brand (keeping
            // the configured no-reply ADDRESS) whenever it's empty or a no-reply/placeholder
            // literal. Self-healing + env-independent; the "please don't reply" cue lives in the
            // email body/footer instead. FAIL-OPEN — never block mail over a cosmetic header.
            try {
                $from = $event->message->getFrom();
                if (! empty($from)) {
                    $addr = $from[0];
                    $name = method_exists($addr, 'getName') ? trim((string) $addr->getName()) : '';
                    $lc   = strtolower($name);
                    if ($name === '' || str_contains($lc, 'no-reply') || str_contains($lc, 'no reply')
                        || str_contains($lc, 'noreply') || $lc === 'example') {
                        $brand = getOption('app_name') ?: 'Centresidence';
                        $event->message->from(new \Symfony\Component\Mime\Address($addr->getAddress(), $brand));
                    }
                }
            } catch (\Throwable $e) {
                // cosmetic only — ignore
            }

            try {
                foreach ((array) $event->message->getTo() as $addr) {
                    $to     = method_exists($addr, 'getAddress') ? $addr->getAddress() : (string) $addr;
                    $reason = \App\Services\Mail\EmailGuard::shouldSuppress($to);
                    if ($reason !== null) {
                        app(\App\Services\SystemIncidentService::class)->report(
                            \App\Models\SystemIncident::TYPE_COMMS_FAILURE,
                            \App\Models\SystemIncident::SEVERITY_WARNING,
                            'Email suppressed to protect deliverability',
                            'Skipped an email to ' . $to . ' (' . $reason . '). If this address is genuine, remove it from the suppression list.',
                            ['email' => $to, 'reason' => $reason],
                            'comms_suppressed',
                            1,
                            25 // a burst of suppressions escalates to CRITICAL — something is mass-mailing bad addresses
                        );
                        return false; // cancel this send
                    }
                }
            } catch (\Throwable $e) {
                // FAIL-OPEN — never block mail because the guard itself errored.
            }

            return null;
        });
    }
}
