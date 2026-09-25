{{-- First-login tenant welcome tour — steps only; the shared engine renders a desktop spotlight
     walk-along (highlighting the real nav items) or a mobile carousel. Replayable via csOpenTour(). --}}
@php
    $app = getOption('app_name') ?: 'Centresidence';
    $tourKey = 'tenant_intro';
    $tourAutoshow = ! app(\App\Services\OnboardingTourService::class)->completed((int) auth()->id(), $tourKey);
    $tourSteps = [
        ['ico' => '👋', 'title' => __('Welcome to :app', ['app' => $app]), 'body' => __('Your home account, in one place. Here is a quick tour — about 30 seconds.')],
        ['ico' => '💳', 'title' => __('Pay rent over M-Pesa'),        'body' => __('Open Invoices, tap your rent invoice and pay over M-Pesa — no queues, no cash.'), 'target' => '[data-tour="tenant-invoices"]'],
        ['ico' => '🧾', 'title' => __('Receipts & rental score'),     'body' => __('Every payment is receipted and builds your portable rental score — proof of a good tenant.'), 'target' => '[data-tour="tenant-score"]'],
        ['ico' => '🛒', 'title' => __('Shop from your landlord'),     'body' => __('Browse and order products your landlord offers — and track your orders — here.'), 'target' => '[data-tour="tenant-shop"]'],
        ['ico' => '🛠️', 'title' => __('Report a problem'),           'body' => __('Something needs fixing? Raise a maintenance request and follow its progress.'), 'target' => '[data-tour="tenant-maintenance"]'],
        ['ico' => '🎫', 'title' => __('Your tickets'),               'body' => __('Follow up on your requests and messages in My Tickets.'), 'target' => '[data-tour="tenant-tickets"]'],
        ['ico' => '💬', 'title' => __('Help is one tap away'),        'body' => __('Reach Centresidence support any time from here. Tip: keep your password private.'), 'target' => '[data-tour="tenant-support"]'],
    ];
@endphp
@include('common.partials.onboarding-tour', ['tourKey' => $tourKey, 'tourSteps' => $tourSteps, 'tourAutoshow' => $tourAutoshow])
