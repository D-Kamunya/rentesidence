<?php

namespace App\Console\Commands;

use App\Mail\Concerns\SendsCsMail;
use App\Models\PropertyUnit;
use App\Models\User;
use App\Models\VacancyNotifyRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * House Watch delivery. Walks the prospects captured off the Vacancy Storefront "notify me"
 * funnel and, when a vacancy now matches what they asked for (a specific owner, a specific
 * property, or their area of interest), emails them a link to go apply — then marks them notified
 * so they aren't pinged again inside the cooldown window.
 *
 * Email only: it's the zero-marginal-cost channel. Prospect SMS would ride a metered sender and is
 * a separate decision (owners' SMS credits are for their own tenants, not cold prospects).
 */
class VacancyWatchNotify extends Command
{
    use SendsCsMail;

    protected $signature = 'vacancy:watch-notify {--dry-run : List matches without sending}';
    protected $description = 'Notify House Watch prospects when a matching vacancy opens up';

    public function handle(): int
    {
        if (! Schema::hasTable('vacancy_notify_requests')) {
            $this->warn('vacancy_notify_requests table not migrated yet — nothing to do.');
            return self::SUCCESS;
        }

        $cooldown = (int) getOption('vacancy_watch_cooldown_days', 7) ?: 7;
        $cutoff   = now()->subDays($cooldown);
        $dry      = (bool) $this->option('dry-run');

        $watchers = VacancyNotifyRequest::query()
            ->whereNotNull('email')
            ->where(fn ($q) => $q->whereNull('notified_at')->orWhere('notified_at', '<', $cutoff))
            ->orderBy('id')
            ->get();

        $sent = 0;
        foreach ($watchers as $watcher) {
            $match = $this->findMatch($watcher);
            if (! $match) {
                continue;
            }

            $this->line("Match for #{$watcher->id} ({$watcher->email}): {$match['label']} ({$match['count']})");

            if ($dry) {
                continue;
            }

            $this->sendCs(
                [$watcher->email],
                __('A home just opened up'),
                [
                    'eyebrow'   => __('House Watch'),
                    'title'     => __('A home just opened up'),
                    'preheader' => __(':label — see it before it’s gone.', ['label' => $match['label']]),
                    'blocks'    => [
                        ['type' => 'text', 'html' => __('Good news :name — a home matching what you’re looking for is now available:', ['name' => $watcher->name ?: __('there')])],
                        ['type' => 'text', 'html' => '<strong>' . e($match['label']) . '</strong> — ' . __(':count available right now.', ['count' => $match['count']])],
                        ['type' => 'button', 'label' => __('View & apply'), 'url' => $match['url']],
                        ['type' => 'note', 'text' => __('Create a free account to track openings in your area and apply in one tap.')],
                    ],
                    'footnote'  => __('You’re receiving this because you asked us to watch for homes. We’ll only email you when there’s a real match.'),
                ]
            );

            $watcher->notified_at = now();
            $watcher->save();
            $sent++;
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Done. {$sent} prospect(s) notified.");
        return self::SUCCESS;
    }

    /**
     * @return array{count:int,url:string,label:string}|null
     */
    private function findMatch(VacancyNotifyRequest $watcher): ?array
    {
        // Property scope — a specific building.
        if ($watcher->scope_type === 'property' && $watcher->scope_property_id) {
            $count = PropertyUnit::where('property_id', $watcher->scope_property_id)
                ->whereDoesntHave('activeTenant')->count();
            if ($count > 0) {
                $property = optional($watcher->property);
                return [
                    'count' => $count,
                    'url'   => route('storefront.property', $watcher->scope_property_id),
                    'label' => $property->name ?? __('A property you’re watching'),
                ];
            }
            return null;
        }

        // Owner scope — any vacancy from a specific owner.
        if ($watcher->scope_type === 'owner' && $watcher->scope_owner_user_id) {
            $count = PropertyUnit::whereHas('property', fn ($q) => $q->where('owner_user_id', $watcher->scope_owner_user_id))
                ->whereDoesntHave('activeTenant')->count();
            if ($count > 0) {
                $owner = User::find($watcher->scope_owner_user_id);
                $token = $owner ? $owner->ensureStorefrontToken() : null;
                if (! $token) {
                    return null;
                }
                $name = $owner ? trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')) : '';
                return [
                    'count' => $count,
                    'url'   => route('storefront.owner', $token),
                    'label' => $name !== '' ? __('Homes from :name', ['name' => $name]) : __('Homes you’re watching'),
                ];
            }
            return null;
        }

        // Area watch — match current vacancies by city and/or state.
        if ($watcher->area_city || $watcher->area_state) {
            $count = PropertyUnit::whereDoesntHave('activeTenant')
                ->whereHas('property.propertyDetail', function ($d) use ($watcher) {
                    if ($watcher->area_city) {
                        $d->whereRaw('LOWER(city_id) = ?', [strtolower($watcher->area_city)]);
                    }
                    if ($watcher->area_state) {
                        $d->whereRaw('LOWER(state_id) = ?', [strtolower($watcher->area_state)]);
                    }
                })->count();
            if ($count > 0) {
                $area = $watcher->area_city ?: $watcher->area_state;
                return [
                    'count' => $count,
                    'url'   => route('house.hunt', array_filter([
                        'type' => 'rental',
                        'city' => $watcher->area_city,
                        'state' => $watcher->area_state,
                    ])),
                    'label' => __('Homes in :area', ['area' => $area]),
                ];
            }
        }

        return null;
    }
}
