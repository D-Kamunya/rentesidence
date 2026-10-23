{{-- The GUIDED DEMO — shown only inside the read-only demo sandbox (session demo_mode). Same shared
     tour engine as the owner welcome tour, but driven by a SALES script: it walks a prospect through
     what Centresidence does for a landlord, and gives the marketplace + financing differentiators
     their full weight (the "why switch to us" moment). Always auto-shows (a fresh prospect each time;
     completion never persists because writes are blocked in demo mode). --}}
@php
    $app = getOption('app_name') ?: 'Centresidence';
    $tourKey = 'guided_demo';
    $tourAutoshow = true;

    $tourSteps = [
        ['ico' => '👋', 'title' => __('Welcome — this is a live account'),
         'body' => __('Everything here is real, working software. Give me 60 seconds and I\'ll show you how :app runs a landlord\'s whole rental business.', ['app' => $app])],

        ['ico' => '💰', 'title' => __('Rent that collects itself'),
         'body' => __('Money your tenants pay over M-Pesa lands here automatically, with an instant receipt. No chasing, no cash, no lost records.'),
         'target' => '[data-tour="owner-wallet"]'],

        ['ico' => '📩', 'title' => __('Invoices on autopilot'),
         'body' => __('Rent invoices generate every month on their own. Your tenant gets an M-Pesa prompt and pays in seconds — you just watch it come in.'),
         'target' => '[data-tour="owner-billing"]'],

        ['ico' => '🏠', 'title' => __('Every property in one place'),
         'body' => __('All your buildings, units, rent and deposits — organised in one system instead of scattered across notebooks and WhatsApp.'),
         'target' => '[data-tour="owner-properties"]'],

        ['ico' => '👥', 'title' => __('Tenants & screening'),
         'body' => __('Add tenants in a tap — their login is sent automatically — and screen a new applicant\'s history before you hand over the keys.'),
         'target' => '[data-tour="owner-tenants"]'],

        ['ico' => '🛒', 'title' => __('★ Your own shop — a second income'),
         'body' => __('Sell water, gas, cooking supplies — anything — to your tenants right inside the app, paid by M-Pesa. Your building becomes a little marketplace and a new income stream. Most rental software simply can\'t do this.'),
         'target' => '[data-tour="owner-shop"]'],

        ['ico' => '⚡', 'title' => __('★ Finance upgrades, repaid from rent'),
         'body' => __('Install smart prepaid water/gas meters, smart locks and more — funded by a partner and repaid automatically from rent. No upfront cash from you. This is the part owners leave their old software for.'),
         'target' => '[data-tour="owner-financing"]'],

        // Spotlights the Agreements menu where present (PROTYAGREEMENT, e.g. live); the tour engine
        // gracefully centres this step on installs where that menu isn't shown.
        ['ico' => '🖊️', 'title' => __('Sign leases in-app'),
         'body' => __('Send a lease and your tenant signs it on their phone with a one-time code — a certified PDF with an audit trail. No printing, no scanning.'),
         'target' => '[data-tour="owner-agreement"]'],

        ['ico' => '🚀', 'title' => __('That\'s :app', ['app' => $app]),
         'body' => __('Set up your first property in minutes and collect this month\'s rent over M-Pesa. Tap "Start free" below when you\'re ready — or talk to the partner who shared this with you.')],
    ];
@endphp
@include('common.partials.onboarding-tour', ['tourKey' => $tourKey, 'tourSteps' => $tourSteps, 'tourAutoshow' => $tourAutoshow])

{{-- Engagement signal: once the prospect has advanced through ~80% of the tour, ping the server
     once so a demo_viewed activity is recorded on the affiliate's lead (if the link carried one). --}}
<script>
(function(){
  var total = {{ count($tourSteps) }};
  var threshold = Math.max(1, Math.ceil(0.8 * (total - 1)));
  var step = 0, fired = false;
  function fire(){
    if (fired) return; fired = true;
    try { fetch(@json(route('demo.viewed')), { method: 'GET', keepalive: true, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); } catch (e) {}
  }
  document.addEventListener('click', function(e){
    var t = e.target && e.target.closest ? e.target.closest('#cstNext') : null;
    if (!t) return;
    step++;
    if (step >= threshold) fire();
  }, true);
})();
</script>
