{{-- First-login owner welcome tour — the desktop walk-along that orients a new owner around the
     dashboard (setup checklist → properties → tenants → getting paid). Spotlight on desktop, carousel
     on mobile, via the shared engine. Replayable via csOpenTour(). --}}
@php
    $app = getOption('app_name') ?: 'Centresidence';
    $tourKey = 'owner_intro';
    $tourAutoshow = ! app(\App\Services\OnboardingTourService::class)->completed((int) auth()->id(), $tourKey);
    $tourSteps = [
        ['ico' => '👋', 'title' => __('Welcome to :app', ['app' => $app]), 'body' => __('Let us show you around your dashboard — how to get set up and run your rentals.')],
        ['ico' => '✅', 'title' => __('Your setup checklist'),  'body' => __('New here? This checklist walks you through getting set up to collect rent — each step links straight to the action.'), 'target' => '#csSetup'],
        ['ico' => '🏠', 'title' => __('Properties & units'),    'body' => __('Add your buildings and the units inside them here.'), 'target' => '[data-tour="owner-properties"]'],
        ['ico' => '👥', 'title' => __('Your tenants'),          'body' => __('Add tenants to units — their login is sent automatically by SMS and email.'), 'target' => '[data-tour="owner-tenants"]'],
        ['ico' => '💳', 'title' => __('Rent & invoices'),       'body' => __('The Billing Center is where rent is billed, invoices are managed, and recurring rent is set up.'), 'target' => '[data-tour="owner-billing"]'],
        ['ico' => '👛', 'title' => __('Your wallet'),           'body' => __('Money collected on your behalf lands here — withdraw it to M-Pesa any time.'), 'target' => '[data-tour="owner-wallet"]'],
        ['ico' => '🖊️', 'title' => __('Agreements'),           'body' => __('Send a lease and have your tenant sign it in-app — no printing, no scanning.'), 'target' => '[data-tour="owner-agreement"]'],
        ['ico' => '💬', 'title' => __('SMS credits'),           'body' => __('Top up SMS credits here — they power tenant reminders and notifications.'), 'target' => '[data-tour="owner-sms"]'],
        ['ico' => '🛠️', 'title' => __('Maintenance & more'),   'body' => __('Handle maintenance requests, tickets and your property notice board from here.'), 'target' => '[data-tour="owner-maintenance"]'],
        ['ico' => '🎧', 'title' => __('Help is here'),          'body' => __('Reach the Centresidence team any time from Support. That is the tour — enjoy!'), 'target' => '[data-tour="owner-support"]'],
    ];
@endphp
@include('common.partials.onboarding-tour', ['tourKey' => $tourKey, 'tourSteps' => $tourSteps, 'tourAutoshow' => $tourAutoshow])
