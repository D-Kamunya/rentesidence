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

      <div id="vshRoot" class="page-content-wrapper bg-white p-3 p-md-4 radius-20 vsh">
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
        .vsh-owner__body h4{font-size:21px;font-weight:800;margin:0 0 8px;color:#fff}
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
        .vsh-btn--outline{background:#fff;color:var(--cs-blue);border:1px solid var(--line)}
        /* The theme's global a:hover{color:primary} rule carries ~16-class specificity (its long
           :not() chain), which can beat plain class rules even though these carry "btn". An ID
           out-specifies any number of classes, so this pins each button's own text colour in every
           state and the links never flip to blue on hover. */
        #vshRoot .vsh-btn--light,  #vshRoot .vsh-btn--light:hover  {color:var(--cs-blue) !important;background:#fff}
        #vshRoot .vsh-btn--ghost,  #vshRoot .vsh-btn--ghost:hover  {color:#fff !important}
        #vshRoot .vsh-btn--ghost:hover{background:rgba(255,255,255,.24)}
        #vshRoot .vsh-btn--blue,   #vshRoot .vsh-btn--blue:hover   {color:#fff !important}
        #vshRoot .vsh-btn--blue:hover{background:var(--cs-blue-2)}
        #vshRoot .vsh-btn--outline,#vshRoot .vsh-btn--outline:hover{color:var(--cs-blue) !important}
        #vshRoot .vsh-btn--outline:hover{background:#f2f6fb}
        .vsh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px}
        .vsh-propthumb{height:122px;border-radius:12px;overflow:hidden;margin-bottom:14px;background:#EFEDE8;position:relative}
        .vsh-propthumb img{width:100%;height:100%;object-fit:cover;display:block}
        .vsh-propthumb--none{display:flex;align-items:center;justify-content:center;color:#A7AEB8;font-size:12.5px;font-weight:600}
        .vsh-prop{display:flex;gap:18px;align-items:flex-start}
        .vsh-prop .vsh-qr{background:#fff;border:1px solid var(--line);border-radius:12px;padding:8px;width:110px;height:110px;flex:0 0 auto}
        .vsh-prop .vsh-qr svg{width:100%;height:100%;display:block}
        .vsh-prop__body{flex:1;min-width:0}
        .vsh-prop__body h5{font-size:16px;font-weight:800;color:var(--ink);margin:0 0 4px}
        .vsh-pill{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;border-radius:99px;padding:4px 10px}
        .vsh-pill--live{background:#E7F6EC;color:#166534}
        .vsh-pill--none{background:#F1F0EC;color:#6B7280}
        .vsh-pill--warn{background:#FDF1DD;color:#9A5B00;margin-left:6px}
        .vsh-prop__links{display:flex;gap:8px;margin-top:16px}
        .vsh-prop__links .vsh-btn{flex:1 1 0;justify-content:center;padding:10px 10px;font-size:13px;white-space:nowrap}
        .vsh-nudge{background:#FDF6EA;border:1px solid #F4E3C4;border-radius:10px;padding:10px 12px;margin-top:10px;
          font-size:12.5px;color:#7A5200;line-height:1.5}
        /* "-btn" suffix keeps it out of the global a:hover blue-ify rule (class*="btn" exclusion) */
        .vsh-photos-btn{color:#9A5B00;font-weight:700;text-decoration:underline}
        .vsh-photos-btn:hover{color:#7A4200}
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
              <a class="vsh-btn vsh-btn--ghost" href="{{ route('owner.vacancy.poster') }}" target="_blank" rel="noopener">
                <i class="ri-printer-line"></i> {{ __('Print poster') }}
              </a>
            </div>
          </div>
        </div>

        @if($totalMissingBuilding > 0 || $totalMissingPhoto > 0)
          <div class="vsh-nudge" style="margin-bottom:22px">
            📸 <strong>{{ __('Photos sell your vacancies.') }}</strong>
            @if($totalMissingBuilding > 0)
              {{ trans_choice(':count building still needs an exterior photo.|:count buildings still need an exterior photo.', $totalMissingBuilding, ['count' => $totalMissingBuilding]) }}
            @endif
            @if($totalMissingPhoto > 0)
              {{ trans_choice(':count vacant unit still needs interior photos.|:count vacant units still need interior photos.', $totalMissingPhoto, ['count' => $totalMissingPhoto]) }}
            @endif
            {{ __('Listings with real photos get far more applications.') }}
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
                @php $pt = trim($card->property->thumbnail_image ?? ''); $hasPt = $pt && ! str_contains($pt, 'no-image'); @endphp
                @if($hasPt)
                  <div class="vsh-propthumb"><img src="{{ $pt }}" alt="{{ $card->property->name }}" loading="lazy"></div>
                @else
                  <div class="vsh-propthumb"><img src="{{ asset('assets/images/property.png') }}" alt="{{ $card->property->name }}"></div>
                @endif
                <div class="vsh-prop">
                  <div class="vsh-qr">{!! $card->qr !!}</div>
                  <div class="vsh-prop__body">
                    <h5>{{ $card->property->name }}</h5>
                    @if($card->vacant_count > 0)
                      <span class="vsh-pill vsh-pill--live">● {{ $card->vacant_count }} {{ trans_choice('vacancy|vacancies', $card->vacant_count) }}</span>
                    @else
                      <span class="vsh-pill vsh-pill--none">{{ __('No vacancies') }}</span>
                    @endif
                    @if($card->vacant_count > 0 && ! $card->has_building_photo)
                      <span class="vsh-pill vsh-pill--warn">🏢 {{ __('no building photo') }}</span>
                    @endif
                    @if($card->missing_photos > 0)
                      <span class="vsh-pill vsh-pill--warn">📸 {{ $card->missing_photos }} {{ __('units need photos') }}</span>
                    @endif
                  </div>
                </div>

                {{-- Actions as a full-width inline row (more room than the narrow body column) --}}
                <div class="vsh-prop__links">
                  <button type="button" class="vsh-btn vsh-btn--blue" onclick="vshCopy('{{ $card->url }}')">
                    <i class="ri-file-copy-line"></i> {{ __('Copy') }}
                  </button>
                  <a class="vsh-btn vsh-btn--outline" href="{{ $card->url }}" target="_blank" rel="noopener">
                    <i class="ri-external-link-line"></i> {{ __('Preview') }}
                  </a>
                  <a class="vsh-btn vsh-btn--outline" href="{{ route('owner.vacancy.poster', $card->property->id) }}" target="_blank" rel="noopener">
                    <i class="ri-printer-line"></i> {{ __('Poster') }}
                  </a>
                </div>

                @if($card->vacant_count > 0 && (! $card->has_building_photo || $card->missing_photos > 0))
                  <div class="vsh-nudge">
                    @if(! $card->has_building_photo)
                      <div><strong>{{ __('Building photo:') }}</strong> {{ __('add one clear exterior shot of the whole building — it heads the listing.') }}</div>
                    @endif
                    @if($card->missing_photos > 0)
                      <div @if(! $card->has_building_photo) style="margin-top:6px" @endif>
                        <strong>{{ __('Unit photos:') }}</strong>
                        {{ trans_choice(':count unit needs interior photos (rooms, kitchen, bathroom).|:count units need interior photos (rooms, kitchen, bathroom).', $card->missing_photos, ['count' => $card->missing_photos]) }}
                      </div>
                    @endif
                    <a class="vsh-photos-btn" href="{{ route('owner.property.photos', $card->property->id) }}" style="display:inline-block;margin-top:8px">{{ __('Add photos →') }}</a>
                    <div style="font-size:11.5px;color:#9A7A3A;margin-top:5px">📷 {{ __('On your phone you can snap them straight from the camera.') }}</div>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif
      @endif
      </div>{{-- /.page-content-wrapper --}}

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
