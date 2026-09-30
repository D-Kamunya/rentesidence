@extends('admin.layouts.app')

@section('content')
@php
    $sevMeta = [
        'critical' => ['label' => __('Critical'), 'cls' => 'inc-sev--critical'],
        'warning'  => ['label' => __('Warning'),  'cls' => 'inc-sev--warning'],
    ];
    $statusMeta = [
        'open'         => ['label' => __('Open'),         'cls' => 'inc-st--open'],
        'acknowledged' => ['label' => __('Acknowledged'), 'cls' => 'inc-st--ack'],
        'resolved'     => ['label' => __('Resolved'),     'cls' => 'inc-st--resolved'],
    ];
    $tabs = ['open' => __('Needs attention'), 'resolved' => __('Resolved'), 'all' => __('All')];
@endphp
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <div class="inc-head">
          <div>
            <h1>{{ __('System Health') }}</h1>
            <p>{{ __('Genuine platform failures — failed payouts, broken callbacks, undelivered credentials, dead jobs. Transient blips are filtered out.') }}</p>
          </div>
          <div class="inc-open-pill {{ $openCount > 0 ? 'is-live' : '' }}">
            <span>{{ $openCount }}</span> {{ __('open') }}
          </div>
        </div>

        <div class="inc-tabs">
          @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.incidents.index', ['tab' => $key]) }}"
               class="inc-tab {{ $tab === $key ? 'is-active' : '' }}">{{ $label }}</a>
          @endforeach
        </div>

        @if (session('success'))
          <div class="inc-flash">{{ session('success') }}</div>
        @endif

        @forelse ($incidents as $incident)
          @php
            $sev = $sevMeta[$incident->severity] ?? $sevMeta['warning'];
            $st  = $statusMeta[$incident->status] ?? $statusMeta['open'];
          @endphp
          <div class="inc-card {{ $incident->severity === 'critical' && $incident->status !== 'resolved' ? 'inc-card--critical' : '' }}">
            <div class="inc-card__main">
              <div class="inc-card__top">
                <span class="inc-sev {{ $sev['cls'] }}">{{ $sev['label'] }}</span>
                <span class="inc-type">{{ $typeLabels[$incident->type] ?? $incident->type }}</span>
                <span class="inc-st {{ $st['cls'] }}">{{ $st['label'] }}</span>
                @if ($incident->occurrences > 1)
                  <span class="inc-count" title="{{ __('Times this has happened') }}">×{{ $incident->occurrences }}</span>
                @endif
              </div>
              <h3 class="inc-title">{{ $incident->title }}</h3>
              @if ($incident->message)
                <p class="inc-msg">{{ \Illuminate\Support\Str::limit($incident->message, 260) }}</p>
              @endif
              <div class="inc-meta">
                <span title="{{ __('First seen') }}"><i class="ri-time-line"></i> {{ __('First') }}: {{ optional($incident->first_seen_at)->diffForHumans() }}</span>
                <span title="{{ __('Last seen') }}"><i class="ri-history-line"></i> {{ __('Last') }}: {{ optional($incident->last_seen_at)->diffForHumans() }}</span>
                @if ($incident->alerted_at)
                  <span title="{{ __('Admin paged by SMS') }}"><i class="ri-notification-3-line"></i> {{ __('Paged') }}</span>
                @endif
              </div>
            </div>
            @if ($incident->status !== 'resolved')
              <div class="inc-card__actions">
                @if ($incident->status !== 'acknowledged')
                  <form method="POST" action="{{ route('admin.incidents.acknowledge', $incident->id) }}">
                    @csrf
                    <button type="submit" class="inc-btn inc-btn--ghost">{{ __('Acknowledge') }}</button>
                  </form>
                @endif
                <form method="POST" action="{{ route('admin.incidents.resolve', $incident->id) }}">
                  @csrf
                  <button type="submit" class="inc-btn inc-btn--primary">{{ __('Resolve') }}</button>
                </form>
              </div>
            @endif
          </div>
        @empty
          <div class="inc-empty">
            <i class="ri-shield-check-line"></i>
            <p>{{ $tab === 'open' ? __('All clear — no open incidents.') : __('Nothing here.') }}</p>
          </div>
        @endforelse

        <div class="inc-pager">{{ $incidents->links() }}</div>

      </div>
    </div>
  </div>
</div>

<style>
  .inc-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
  .inc-head h1 { font-size:23px; font-weight:800; color:#1b1e22; margin:0 0 4px; }
  .inc-head p { font-size:13.5px; color:#6b7280; margin:0; max-width:620px; line-height:1.55; }
  .inc-open-pill { background:#eef1f4; color:#4b5563; border-radius:999px; padding:8px 16px; font-size:13px; font-weight:600; white-space:nowrap; }
  .inc-open-pill.is-live { background:#FEE4E2; color:#B42318; }
  .inc-open-pill span { font-weight:800; }

  .inc-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
  .inc-tab { padding:8px 15px; border-radius:10px; background:#fff; border:1px solid #e6e8eb; color:#4b5563;
    font-size:13px; font-weight:600; text-decoration:none; transition:.15s; }
  .inc-tab:hover { border-color:#c7cbd1; }
  .inc-tab.is-active { background:#185FA5; border-color:#185FA5; color:#fff !important; }

  .inc-flash { background:#ECFDF3; color:#027A48; border:1px solid #A6F4C5; border-radius:12px; padding:12px 16px; font-size:13.5px; margin-bottom:16px; }

  .inc-card { display:flex; gap:16px; justify-content:space-between; align-items:flex-start; flex-wrap:wrap;
    background:#fff; border:1px solid #e9ebee; border-left:4px solid #d1d5db; border-radius:16px;
    padding:18px 20px; margin-bottom:12px; }
  .inc-card--critical { border-left-color:#D92D20; }
  .inc-card__main { flex:1; min-width:260px; }
  .inc-card__top { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px; }
  .inc-sev { font-size:10.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; padding:3px 9px; border-radius:999px; }
  .inc-sev--critical { background:#FEE4E2; color:#B42318; }
  .inc-sev--warning  { background:#FEF0C7; color:#B54708; }
  .inc-type { font-size:12px; font-weight:700; color:#344054; background:#f2f4f7; padding:3px 9px; border-radius:999px; }
  .inc-st { font-size:11px; font-weight:700; padding:3px 9px; border-radius:999px; }
  .inc-st--open { background:#EFF8FF; color:#175CD3; }
  .inc-st--ack { background:#F4F3FF; color:#5925DC; }
  .inc-st--resolved { background:#F2F4F7; color:#667085; }
  .inc-count { font-size:12px; font-weight:800; color:#B42318; }
  .inc-title { font-size:15.5px; font-weight:700; color:#1b1e22; margin:0 0 5px; }
  .inc-msg { font-size:13.5px; color:#4b5563; line-height:1.55; margin:0 0 10px; word-break:break-word; }
  .inc-meta { display:flex; gap:16px; flex-wrap:wrap; font-size:12px; color:#8a9099; }
  .inc-meta i { vertical-align:middle; }

  .inc-card__actions { display:flex; gap:8px; align-items:center; }
  .inc-btn { border:none; border-radius:10px; padding:9px 16px; font-size:13px; font-weight:700; cursor:pointer; transition:.15s; }
  .inc-btn--ghost { background:#f2f4f7; color:#344054; }
  .inc-btn--ghost:hover { background:#e6e8eb; }
  .inc-btn--primary { background:#185FA5; color:#fff !important; }
  .inc-btn--primary:hover { background:#0F4A84; }

  .inc-empty { text-align:center; padding:56px 20px; color:#98a2b3; }
  .inc-empty i { font-size:40px; color:#12B76A; display:block; margin-bottom:10px; }
  .inc-empty p { font-size:14px; margin:0; }
  .inc-pager { margin-top:14px; }
</style>
@endsection
