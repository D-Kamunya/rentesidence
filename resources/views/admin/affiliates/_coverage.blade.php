{{-- Affiliate geographic coverage — counts by county + the counties we're NOT yet represented in,
     so recruitment can be aimed at the gaps. Data: AffiliateController@buildCoverage (config-driven). --}}
@php $cov = $coverage ?? null; @endphp
@if ($cov)
<style>
  .afc-wrap { border:1px solid #ececec; border-radius:14px; background:#fff; padding:18px 20px 20px; margin-bottom:22px; box-shadow:0 1px 3px rgba(16,24,40,.04); }
  .afc-head { margin-bottom:14px; }
  .afc-head h4 { font-size:15px; font-weight:700; color:#1b1e22; margin:0; }
  .afc-head span { font-size:12.5px; color:#9aa2ad; }
  .afc-tiles { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
  @media (max-width:720px){ .afc-tiles{ grid-template-columns:repeat(2,1fr); } }
  .afc-tile { border:1px solid #f0f0ee; border-radius:11px; padding:12px 14px; background:#fafafa; }
  .afc-tile .n { font-size:22px; font-weight:750; color:#1b1e22; line-height:1.1; font-variant-numeric:tabular-nums; }
  .afc-tile .l { font-size:11.5px; color:#9aa2ad; margin-top:3px; }
  .afc-tile--warn { background:#FEF6EC; border-color:#F5DCB3; }
  .afc-tile--warn .n { color:#B45309; }
  .afc-cols { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  @media (max-width:720px){ .afc-cols{ grid-template-columns:1fr; } }
  .afc-label { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#9aa2ad; margin-bottom:9px; }
  .afc-chips { display:flex; flex-wrap:wrap; gap:7px; max-height:148px; overflow-y:auto; padding-right:4px; }
  .afc-chip { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; color:#0F4A84; background:#E6F1FB; border:1px solid #cfe3f6; border-radius:999px; padding:4px 10px; }
  .afc-chip b { font-variant-numeric:tabular-nums; color:#185FA5; }
  .afc-chip--gap { color:#8a6d3b; background:#FBF5EA; border-color:#efe2c7; font-weight:600; }
  .afc-empty { font-size:12.5px; color:#9aa2ad; padding:6px 0; }
  .afc-country { font-size:12px; color:#6b7280; margin-top:14px; }
  .afc-country b { color:#1b1e22; }
</style>

<div class="afc-wrap">
  <div class="afc-head">
    <h4>{{ __('Affiliate coverage') }}</h4>
    <span>{{ __('Where your partners are — and the :label still open for targeted recruitment.', ['label' => \Illuminate\Support\Str::plural(strtolower($cov['divisionsLabel']))]) }}</span>
  </div>

  <div class="afc-tiles">
    <div class="afc-tile">
      <div class="n">{{ $cov['total'] }}</div>
      <div class="l">{{ __('Total affiliates') }}</div>
    </div>
    <div class="afc-tile">
      <div class="n">{{ $cov['countiesCovered'] }}<span style="font-size:14px;color:#9aa2ad;">/{{ $cov['countiesTotal'] }}</span></div>
      <div class="l">{{ $cov['divisionsLabel'] }}{{ __('s covered') }}</div>
    </div>
    <div class="afc-tile">
      <div class="n">{{ $cov['located'] }}</div>
      <div class="l">{{ __('With a location on file') }}</div>
    </div>
    <div class="afc-tile {{ $cov['unassigned'] > 0 ? 'afc-tile--warn' : '' }}">
      <div class="n">{{ $cov['unassigned'] }}</div>
      <div class="l">{{ __('No location yet') }}</div>
    </div>
  </div>

  <div class="afc-cols">
    <div>
      <div class="afc-label">{{ __('Represented') }} ({{ $cov['countiesCovered'] }})</div>
      @if ($cov['byCounty']->isEmpty())
        <div class="afc-empty">{{ __('No affiliates with a county on file yet.') }}</div>
      @else
        <div class="afc-chips">
          @foreach ($cov['byCounty'] as $county => $count)
            <span class="afc-chip">{{ $county }} <b>{{ $count }}</b></span>
          @endforeach
        </div>
      @endif
    </div>
    <div>
      <div class="afc-label">{{ __('Not yet represented') }} ({{ count($cov['notRepresented']) }})</div>
      @if (empty($cov['notRepresented']))
        <div class="afc-empty">{{ __('Every :label is covered. 🎉', ['label' => strtolower($cov['divisionsLabel'])]) }}</div>
      @else
        <div class="afc-chips">
          @foreach ($cov['notRepresented'] as $county)
            <span class="afc-chip afc-chip--gap">{{ $county }}</span>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  @if ($cov['multiCountry'])
    <div class="afc-country">
      {{ __('By country') }}:
      @foreach ($cov['byCountry'] as $country => $count)
        <b>{{ $country }}</b> ({{ $count }}){{ ! $loop->last ? ' · ' : '' }}
      @endforeach
    </div>
  @endif
</div>
@endif
