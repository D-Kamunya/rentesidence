{{-- First-login owner welcome tour — the desktop walk-along that orients a new owner around the
     dashboard (setup checklist → properties → tenants → getting paid). Spotlight on desktop, carousel
     on mobile, via the shared engine. Replayable via csOpenTour(). --}}
@php
    $app = getOption('app_name') ?: 'Centresidence';
    $tourKey = 'owner_intro';
    $tourAutoshow = ! app(\App\Services\OnboardingTourService::class)->completed((int) auth()->id(), $tourKey);
    $tourSteps = [
        ['ico' => '👋', 'title' => __('Welcome to :app', ['app' => $app]), 'body' => __('Let us show you around your dashboard — how to get set up and run your rentals.')],
        ['ico' => '✅', 'title' => __('Your setup checklist'),  'body' => __('Work through these steps to start collecting rent — each links straight to the action.'), 'target' => '#csSetup'],
        ['ico' => '🏠', 'title' => __('Properties & units'),    'body' => __('Add your buildings and the units inside them here.'), 'target' => '[data-tour="owner-properties"]'],
        ['ico' => '👥', 'title' => __('Your tenants'),          'body' => __('Add tenants to units — their login is sent automatically by SMS and email.'), 'target' => '[data-tour="owner-tenants"]'],
        ['ico' => '💳', 'title' => __('Getting paid'),          'body' => __('Set how rent reaches you, and manage invoices, in the Billing Center.'), 'target' => '[data-tour="owner-billing"]'],
    ];
@endphp
@include('common.partials.onboarding-tour', ['tourKey' => $tourKey, 'tourSteps' => $tourSteps, 'tourAutoshow' => $tourAutoshow])
