@extends('owner.layouts.app')

@php $pageTitle = __('Property Photos'); @endphp

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
            <div class="page-title-left"><h3 class="mb-sm-0">{{ $pageTitle }}</h3></div>
            <div class="page-title-right">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div id="poRoot" class="page-content-wrapper bg-white p-3 p-md-4 radius-20 po">
        <style>
          .po{--cs-blue:#185FA5;--cs-blue-2:#1c72c2;--line:#E6E1D8;--ink:#1B1E22;--muted:#6B7280;--amber:#E7A339}
          .po-intro{color:var(--muted);font-size:14.5px;line-height:1.6;max-width:760px;margin:0 0 20px}
          .po-guide{display:inline-flex;gap:9px;align-items:center;border-radius:12px;padding:10px 15px;font-size:13.5px;font-weight:700;margin-bottom:22px}
          .po-guide.warn{background:#FDF6EA;border:1px solid #F4E3C4;color:#7A5200}
          .po-guide.ok{background:#E7F6EC;border:1px solid #BfE8CB;color:#166534}
          .po-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
          .po-card{display:flex;flex-direction:column;border:1px solid var(--line);border-left:4px solid var(--line);
            border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 10px 26px -20px rgba(20,23,28,.12);transition:.15s}
          .po-card.attention{border-left-color:var(--amber)}
          .po-card.ok{border-left-color:#2EA563}
          .po-thumb{height:140px;background:#EFEDE8;position:relative}
          .po-thumb img{width:100%;height:100%;object-fit:cover;display:block}
          .po-thumb--none{display:flex;align-items:center;justify-content:center;color:#A7AEB8;font-size:12.5px;font-weight:600}
          .po-body{padding:16px 18px 18px;display:flex;flex-direction:column;flex:1}
          .po-name{font-size:16px;font-weight:800;color:var(--ink);margin:0 0 10px}
          .po-pills{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:14px}
          .po-pill{font-size:11px;font-weight:800;border-radius:99px;padding:3px 10px}
          .po-pill--ok{background:#E7F6EC;color:#166534}
          .po-pill--need{background:#FDF1DD;color:#9A5B00}
          .po-pill--muted{background:#F1F0EC;color:#6B7280}
          .po-manage{margin-top:auto;display:inline-flex;align-items:center;justify-content:center;gap:8px;
            background:var(--cs-blue);color:#fff !important;font-weight:700;font-size:14px;padding:11px 16px;border-radius:11px;text-decoration:none}
          .po-manage:hover{background:var(--cs-blue-2)}
          #poRoot .po-manage,#poRoot .po-manage:hover{color:#fff !important}
          .po-manage svg{width:16px;height:16px}
        </style>

        <p class="po-intro">
          {{ __('One place for all your property photos. Open any property to add a building photo and snap each unit room-by-room from your phone. Properties that still need photos are shown first.') }}
        </p>

        @if($rows->isEmpty())
          <div class="po-card" style="padding:22px"><p class="mb-0 text-muted">{{ __('You haven’t added any properties yet.') }}</p></div>
        @else
          @if($needAttention > 0)
            <div class="po-guide warn">📸 {{ trans_choice(':count property still needs photos.|:count properties still need photos.', $needAttention, ['count' => $needAttention]) }}</div>
          @else
            <div class="po-guide ok">✅ {{ __('Every property has a building photo and all units have photos. Nice work.') }}</div>
          @endif

          <div class="po-grid">
            @foreach($rows as $row)
              <div class="po-card {{ $row->complete ? 'ok' : 'attention' }}">
                @if($row->thumb)
                  <div class="po-thumb"><img src="{{ $row->thumb }}" alt="{{ $row->property->name }}" loading="lazy"></div>
                @else
                  <div class="po-thumb po-thumb--none">{{ __('No building photo') }}</div>
                @endif
                <div class="po-body">
                  <h5 class="po-name">{{ $row->property->name }}</h5>
                  <div class="po-pills">
                    @if($row->has_building)
                      <span class="po-pill po-pill--ok">{{ __('Building ✓') }}</span>
                    @else
                      <span class="po-pill po-pill--need">{{ __('No building photo') }}</span>
                    @endif
                    @if($row->units === 0)
                      <span class="po-pill po-pill--muted">{{ __('No units yet') }}</span>
                    @elseif($row->units_no_photos > 0)
                      <span class="po-pill po-pill--need">📸 {{ $row->units_no_photos }} / {{ $row->units }} {{ __('units need photos') }}</span>
                    @else
                      <span class="po-pill po-pill--ok">{{ __('All units ✓') }}</span>
                    @endif
                    <span class="po-pill po-pill--muted">{{ $row->total_photos }} {{ trans_choice('photo|photos', $row->total_photos) }}</span>
                  </div>
                  <a class="po-manage" href="{{ $row->url }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    {{ __('Manage photos') }}
                  </a>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
