<?php
namespace App\Jobs\Mail;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Activation nudge for an owner who signed up but hasn't got going — the "created an account and
 * drifted" cohort. Two stages: 'property' (no property yet → add your first) and 'tenant' (has a
 * property but no tenant → add a tenant and start collecting rent). CS-styled; bounded + stopped by
 * the command that dispatches it (OwnerActivationNudge). An inactive owner is the one place the
 * whole chain — owner, platform, and the affiliate who brought them — earns nothing.
 */
class SendOwnerActivationNudgeMail extends BaseMailJob
{
    /** $stage: 'property' = no property yet; 'tenant' = has a property but no tenant. */
    public function __construct(public int $ownerUserId, public string $stage = 'property') {}

    public function handle(): void
    {
        $user = User::find($this->ownerUserId);
        if (! $user || empty($user->email)) {
            return;
        }

        $appName = getOption('app_name') ?: 'Centresidence';
        $first   = e($user->first_name ?: __('there'));
        $dash    = Route::has('owner.dashboard') ? route('owner.dashboard') : url('/');

        if ($this->stage === 'tenant') {
            // Has a property but no tenant → the step that unlocks billing + rent collection.
            $cta = Route::has('owner.tenant.create') ? route('owner.tenant.create') : $dash;
            $this->sendCs(
                [$user->email],
                __('Add a tenant and start collecting rent') . ' | ' . $appName,
                [
                    'eyebrow' => __('You\'re almost there'), 'eyebrowColor' => '#185FA5',
                    'title'   => __('One step from automatic rent collection'),
                    'blocks'  => [
                        ['type' => 'text', 'html' => __('Hi :name, you\'ve set up your property on :app — nicely done. The next step is to <strong>add a tenant</strong>, and the system starts doing the work for you.', ['name' => "<strong>{$first}</strong>", 'app' => $appName])],
                        ['type' => 'text', 'html' => __('Once a unit has a tenant, :app generates the rent invoice automatically and the tenant pays by <strong>M-Pesa prompt</strong> — you see the payment and receipt instantly. No more chasing.', ['app' => $appName])],
                        ['type' => 'button', 'url' => $cta, 'label' => __('Add my first tenant')],
                        ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>" . __('Need a hand? Reply to this email or reach us in-app (Support) and we\'ll help you set it up.') . '</span>'],
                    ],
                ]
            );
            return;
        }

        // 'property' — no property yet: the first real step.
        $cta = Route::has('owner.property.add') ? route('owner.property.add') : $dash;
        $this->sendCs(
            [$user->email],
            __('Add your first property and get started') . ' | ' . $appName,
            [
                'eyebrow' => __('A quick nudge'), 'eyebrowColor' => '#185FA5',
                'title'   => __('You\'re set up — let\'s get your first property in'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hi :name, welcome to :app! Your account is ready — you just haven\'t added a property yet.', ['name' => "<strong>{$first}</strong>", 'app' => $appName])],
                    ['type' => 'text', 'html' => __('Add one building, split it into units with their rent, and you\'re ready to onboard tenants and collect rent automatically over M-Pesa. It only takes a few minutes, and it\'s <strong>free</strong> to run.')],
                    ['type' => 'button', 'url' => $cta, 'label' => __('Add my first property')],
                    ['type' => 'text', 'html' => "<span style='color:#6b7280;font-size:13px;'>" . __('Stuck or short on time? Reply here or use in-app Support and we\'ll walk you through it.') . '</span>'],
                ],
            ]
        );
    }
}
