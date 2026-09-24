{{-- Opens the page wrapper. 'standard' (owner/tenant/affiliate) supplies the app's main-content card;
     'plain' (finance-partner, whose layout already pads .fp-content) renders content directly. --}}
@if (($chrome ?? 'standard') === 'standard')
<div class="main-content">
<div class="page-content">
  <div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
@else
<div class="sup-plain">
@endif
