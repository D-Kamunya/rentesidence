{{-- First-login owner welcome tour — the desktop walk-along that orients a new owner around the
     dashboard (setup checklist → properties → tenants → getting paid). Spotlight on desktop, carousel
     on mobile, via the shared engine. Replayable via csOpenTour(). --}}
@php
    $app = getOption('app_name') ?: 'Centresidence';
    $tourKey = 'owner_intro';
    $tourAutoshow = ! app(\App\Services\OnboardingTourService::class)->completed((int) auth()->id(), $tourKey);

    // The wallet only receives RENT on the Transaction plan; on Free/Subscription rent goes to the
    // owner's own account, so the wallet is marketplace sales (+ other earnings). Word it accordingly
    // — and, for non-transaction owners, it doubles as a gentle Transaction nudge.
    $ownerPricingModel = optional(app(\App\Services\SubscriptionService::class)->getCurrentPlan((int) auth()->id()))->pricing_model ?? 'free';
    $walletBody = $ownerPricingModel === 'transaction'
        ? __('Rent and marketplace sales collected on your behalf land here — withdraw to M-Pesa any time.')
        : __('Your marketplace sales and other earnings land here to withdraw to M-Pesa. (On the Transaction plan, rent lands here too.)');
    $tourSteps = [
        ['ico' => '👋', 'title' => __('Welcome to :app', ['app' => $app]), 'body' => __('Let us show you around your dashboard — how to get set up and run your rentals.')],
        ['ico' => '✅', 'title' => __('Your setup checklist'),  'body' => __('New here? This checklist walks you through getting set up to collect rent — each step links straight to the action.'), 'target' => '#csSetup'],
        ['ico' => '🏠', 'title' => __('Properties & units'),    'body' => __('Add your buildings and the units inside them here.'), 'target' => '[data-tour="owner-properties"]'],
        ['ico' => '👥', 'title' => __('Your tenants'),          'body' => __('Add tenants to units — their login is sent automatically by SMS and email.'), 'target' => '[data-tour="owner-tenants"]'],
        ['ico' => '💳', 'title' => __('Rent & invoices'),       'body' => __('The Billing Center is where rent is billed, invoices are managed, and recurring rent is set up.'), 'target' => '[data-tour="owner-billing"]'],
        ['ico' => '👛', 'title' => __('Your wallet'),           'body' => $walletBody, 'target' => '[data-tour="owner-wallet"]'],
        ['ico' => '🖊️', 'title' => __('Agreements'),           'body' => __('Send a lease and have your tenant sign it in-app — no printing, no scanning.'), 'target' => '[data-tour="owner-agreement"]'],
        ['ico' => '💬', 'title' => __('SMS credits'),           'body' => __('Top up SMS credits here — they power tenant reminders and notifications.'), 'target' => '[data-tour="owner-sms"]'],
        ['ico' => '🛠️', 'title' => __('Maintenance & more'),   'body' => __('Handle maintenance requests, tickets and your property notice board from here.'), 'target' => '[data-tour="owner-maintenance"]'],
        ['ico' => '🛒', 'title' => __('Sell to your tenants'),  'body' => __('Open a shop and sell products to your tenants — an extra income stream, right inside the app.'), 'target' => '[data-tour="owner-shop"]'],
        ['ico' => '📈', 'title' => __('Finance & grow'),        'body' => __('Fund smart meters, locks and more through a partner — repaid from rent — to boost your property cashflow.'), 'target' => '[data-tour="owner-financing"]'],
        ['ico' => '🎧', 'title' => __('Help is here'),          'body' => __('Reach the Centresidence team any time from Support. That is the tour — enjoy!'), 'target' => '[data-tour="owner-support"]'],
    ];
@endphp
@include('common.partials.onboarding-tour', ['tourKey' => $tourKey, 'tourSteps' => $tourSteps, 'tourAutoshow' => $tourAutoshow])
