@extends('owner.layouts.app')

@php $pageTitle = __('Share Vacancies'); @endphp

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
            <div class="page-title-left">
              <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
            </div>
            <div class="page-title-right">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <style>
        .vsh{--cs-blue:#185FA5;--cs-blue-2:#1c72c2;--amber:#E7A339;--line:#E6E1D8;--ink:#1B1E22;--muted:#6B7280}
        .vsh-intro{color:var(--muted);font-size:14.5px;max-width:720px;line-height:1.6;margin:0 0 22px}
        .vsh-card{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 10px 26px -18px rgba(20,23,28,.14);padding:24px;margin-bottom:22px}
        .vsh-owner{display:flex;gap:26px;align-items:center;flex-wrap:wrap;
          background:linear-gradient(135deg,#0F3D6B,#185FA5);color:#fff}
        .vsh-owner .vsh-qr{background:#fff;border-radius:14px;padding:12px;width:168px;height:168px;flex:0 0 auto}
        .vsh-owner .vsh-qr svg{width:100%;height:100%;display:block}
        .vsh-owner__body{flex:1;min-width:280px}
        .vsh-owner__body .eyebrow{font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#BcD8F3;margin:0 0 6px}
        .vsh-owner__body h4{font-size:21px;font-weight:800;margin:0 0 8px}
        .vsh-owner__body p{color:#DCE8F5;font-size:14px;margin:0 0 16px;max-width:480px;line-height:1.55}
        .vsh-linkrow{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
        .vsh-link{flex:1;min-width:220px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);
          border-radius:10px;padding:11px 14px;color:#fff;font-size:13px;font-family:ui-monospace,Menlo,Consolas,monospace;
          word-break:break-all}
        .vsh-btn{border:none;border-radius:10px;padding:11px 18px;font-size:13.5px;font-weight:700;cursor:pointer;
          text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:.15s}
        .vsh-btn--light{background:#fff;color:var(--cs-blue)}
        .vsh-btn--ghost{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.3)}
        .vsh-btn--blue{background:var(--cs-blue);color:#fff}
        .vsh-btn--blue:hover{background:var(--cs-blue-2);color:#fff}
        .vsh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px}
        .vsh-prop{display:flex;gap:18px;align-items:flex-start}
        .vsh-prop .vsh-qr{background:#fff;border:1px solid var(--line);border-radius:12px;padding:8px;width:110px;height:110px;flex:0 0 auto}
        .vsh-prop .vsh-qr svg{width:100%;height:100%;display:block}
        .vsh-prop__body{flex:1;min-width:0}
        .vsh-prop__body h5{font-size:16px;font-weight:800;color:var(--ink);margin:0 0 4px}
        .vsh-pill{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;border-radius:99px;padding:4px 10px}
        .vsh-pill--live{background:#E7F6EC;color:#166534}
        .vsh-pill--none{background:#F1F0EC;color:#6B7280}
        .vsh-pill--warn{background:#FDF1DD;color:#9A5B00;margin-left:6px}
        .vsh-prop__links{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
        .vsh-nudge{background:#FDF6EA;border:1px solid #F4E3C4;border-radius:10px;padding:10px 12px;margin-top:10px;
          font-size:12.5px;color:#7A5200;line-height:1.5}
        .vsh-nudge a{color:#9A5B00;font-weight:700;text-decoration:underline}
        .vsh-copytoast{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);background:#1B1E22;color:#fff;
          padding:11px 20px;border-radius:10px;font-size:13.5px;font-weight:600;opacity:0;pointer-events:none;transition:.2s;z-index:9999}
        .vsh-copytoast.show{opacity:1}
      </style>

      <p class="vsh-intro">
        {{ __('Every vacant unit you have is already a live, shareable web page — no setup, no teardown. Post the QR on the premises or share the link when someone asks “what do you have available?”. It always shows your current vacancies, and when you have none it quietly collects interested tenants for you.') }}
      </p>

      @if($tokenMissing)
        <div class="alert alert-warning">
          {{ __('Your share link will be ready once the latest update is applied to the database. Please run the pending migration (app:deploy).') }}
        </div>
      @else
        {{-- Owner-level share — "what vacancies do you have?" --}}
        <div class="vsh-card vsh-owner">
          <div class="vsh-qr">{!! $ownerQr !!}</div>
          <div class="vsh-owner__body">
            <p class="eyebrow">{{ __('Your vacancies — all properties') }}</p>
            <h4>{{ $totalVacant }} {{ trans_choice('vacant unit|vacant units', $totalVacant) }} {{ __('live right now') }}</h4>
            <p>{{ __('This one link covers every property you manage. Share it anywhere — WhatsApp, your bio, a flyer — and tenants can browse and apply themselves.') }}</p>
            <div class="vsh-linkrow">
              <span class="vsh-link" id="ownerLink">{{ $ownerUrl }}</span>
              <button type="button" class="vsh-btn vsh-btn--light" onclick="vshCopy('{{ $ownerUrl }}')">
                <i class="ri-file-copy-line"></i> {{ __('Copy link') }}
              </button>
              <a class="vsh-btn vsh-btn--ghost" href="{{ $ownerUrl }}" target="_blank" rel="noopener">
                <i class="ri-external-link-line"></i> {{ __('Preview') }}
              </a>
              <a class="vsh-btn vsh-btn--ghost" href="https://wa.me/?text={{ urlencode(__('Here are the homes I have available: ') . $ownerUrl) }}" target="_blank" rel="noopener">
                <i class="ri-whatsapp-line"></i> {{ __('WhatsApp') }}
              </a>
            </div>
          </div>
        </div>

        @if($totalMissingPhoto > 0)
          <div class="vsh-nudge" style="margin-bottom:22px">
            📸 {{ trans_choice(
                ':count vacant unit has no photos yet — listings with real photos get far more applications.|:count vacant units have no photos yet — listings with real photos get far more applications.',
                $totalMissingPhoto, ['count' => $totalMissingPhoto]) }}
          </div>
        @endif

        {{-- Per-property share --}}
        <h5 style="font-weight:800;color:#1B1E22;margin:4px 0 14px">{{ __('Per-property links') }}</h5>

        @if($cards->isEmpty())
          <div class="vsh-card"><p class="mb-0 text-muted">{{ __('You haven’t added any properties yet. Add a property and its vacant units become shareable here automatically.') }}</p></div>
        @else
          <div class="vsh-grid">
            @foreach($cards as $card)
              <div class="vsh-card">
                <div class="vsh-prop">
                  <div class="vsh-qr">{!! $card->qr !!}</div>
                  <div class="vsh-prop__body">
                    <h5>{{ $card->property->name }}</h5>
                    @if($card->vacant_count > 0)
                      <span class="vsh-pill vsh-pill--live">● {{ $card->vacant_count }} {{ trans_choice('vacancy|vacancies', $card->vacant_count) }}</span>
                    @else
                      <span class="vsh-pill vsh-pill--none">{{ __('No vacancies') }}</span>
                    @endif
                    @if($card->missing_photos > 0)
                      <span class="vsh-pill vsh-pill--warn">📸 {{ $card->missing_photos }} {{ __('need photos') }}</span>
                    @endif

                    <div class="vsh-prop__links">
                      <button type="button" class="vsh-btn vsh-btn--blue" onclick="vshCopy('{{ $card->url }}')">
                        <i class="ri-file-copy-line"></i> {{ __('Copy') }}
                      </button>
                      <a class="vsh-btn vsh-btn--ghost" style="color:var(--cs-blue);border-color:var(--line)" href="{{ $card->url }}" target="_blank" rel="noopener">
                        <i class="ri-external-link-line"></i> {{ __('Preview') }}
                      </a>
                    </div>

                    @if($card->missing_photos > 0)
                      <div class="vsh-nudge">
                        {{ __('Add real photos to this property’s vacant units so they actually sell.') }}
                        <a href="{{ route('owner.property.show', $card->property->id) }}">{{ __('Add photos →') }}</a>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      @endif

    </div>
  </div>
</div>

<div class="vsh-copytoast" id="vshToast">{{ __('Link copied') }}</div>

<script>
function vshCopy(text) {
  const done = () => {
    const t = document.getElementById('vshToast');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 1600);
  };
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(done).catch(done);
  } else {
    const ta = document.createElement('textarea');
    ta.value = text; document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta); done();
  }
}
</script>
@endsection
