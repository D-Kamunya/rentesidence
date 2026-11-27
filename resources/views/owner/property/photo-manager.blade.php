@extends('owner.layouts.app')

@php $pageTitle = __('Add Photos'); @endphp

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
            <div class="page-title-left">
              <h3 class="mb-sm-0">{{ $pageTitle }} — {{ $property->name }}</h3>
            </div>
            <div class="page-title-right">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('owner.vacancy.share') }}">{{ __('Share Vacancies') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div id="pmRoot" class="page-content-wrapper bg-white p-3 p-md-4 radius-20 pm">
        <style>
          .pm{--cs-blue:#185FA5;--cs-blue-2:#1c72c2;--line:#E6E1D8;--ink:#1B1E22;--muted:#6B7280;--amber:#E7A339}
          .pm-intro{color:var(--muted);font-size:14.5px;line-height:1.6;max-width:760px;margin:0 0 20px}
          .pm-guide{display:flex;gap:12px;align-items:flex-start;background:#FDF6EA;border:1px solid #F4E3C4;
            border-radius:12px;padding:14px 16px;color:#7A5200;font-size:13.5px;line-height:1.5;margin-bottom:24px}
          .pm-guide.ok{background:#E7F6EC;border-color:#BfE8CB;color:#166534}
          .pm-guide b{color:inherit}
          .pm-sec{border:1px solid var(--line);border-radius:16px;padding:20px;margin-bottom:22px;box-shadow:0 10px 26px -20px rgba(20,23,28,.12)}
          .pm-sec__head{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px}
          .pm-sec__head h5{font-size:16px;font-weight:800;color:var(--ink);margin:0}
          .pm-sec__hint{color:var(--muted);font-size:13px;margin:2px 0 16px;max-width:620px;line-height:1.5}
          .pm-pill{font-size:11px;font-weight:800;border-radius:99px;padding:3px 10px}
          .pm-pill--vacant{background:#E7F0F9;color:#185FA5}
          .pm-pill--need{background:#FDF1DD;color:#9A5B00}
          .pm-pill--ok{background:#E7F6EC;color:#166534}
          .pm-thumbs{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px}
          .pm-thumb{position:relative;width:104px;height:104px;border-radius:12px;overflow:hidden;border:1px solid var(--line);background:#F4F1EC;flex:0 0 auto}
          .pm-thumb img{width:100%;height:100%;object-fit:cover;display:block}
          .pm-thumb__del{position:absolute;top:5px;right:5px;width:26px;height:26px;border:none;border-radius:50%;
            background:rgba(17,20,26,.72);color:#fff;font-size:15px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center}
          .pm-thumb__del:hover{background:#c0392b}
          .pm-thumb--big{width:100%;max-width:360px;height:200px}
          .pm-empty{color:var(--muted);font-size:13.5px;font-style:italic;margin-bottom:16px}
          /* Camera/upload button (label wrapping a hidden file input) */
          .pm-add{display:inline-flex;align-items:center;gap:9px;background:var(--cs-blue);color:#fff;font-weight:700;
            font-size:14.5px;padding:12px 20px;border-radius:11px;cursor:pointer;border:none;transition:.15s;margin:0}
          .pm-add:hover{background:var(--cs-blue-2)}
          .pm-add input{display:none}
          .pm-add.busy{opacity:.6;pointer-events:none}
          .pm-add svg{width:18px;height:18px}
          .pm-tip{font-size:12px;color:var(--muted);margin-top:9px}
          .pm-unit{border-top:1px solid var(--line);padding-top:20px;margin-top:4px}
          .pm-unit:first-of-type{border-top:none;padding-top:0}
          .pm-toast{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);background:#1B1E22;color:#fff;
            padding:11px 20px;border-radius:10px;font-size:13.5px;font-weight:600;opacity:0;pointer-events:none;transition:.2s;z-index:9999}
          .pm-toast.show{opacity:1}
          .pm-toast.err{background:#c0392b}
        </style>

        <p class="pm-intro">
          {{ __('Add real photos so this property’s vacancies actually sell. Snap them right here from your phone — a clear building photo heads the listing, and interior photos of each unit do the rest.') }}
        </p>

        @php $buildingMissing = ! $buildingPhotoUrl; @endphp
        @if($buildingMissing || $vacantNeedPhotos > 0)
          <div class="pm-guide">
            <span style="font-size:17px;line-height:1">📸</span>
            <div>
              <b>{{ __('What’s still missing:') }}</b>
              @if($buildingMissing) {{ __('a building (exterior) photo;') }} @endif
              @if($vacantNeedPhotos > 0) {{ trans_choice(':count vacant unit needs interior photos.|:count vacant units need interior photos.', $vacantNeedPhotos, ['count' => $vacantNeedPhotos]) }} @endif
              {{ __('They’re flagged below.') }}
            </div>
          </div>
        @else
          <div class="pm-guide ok">
            <span style="font-size:17px;line-height:1">✅</span>
            <div>{{ __('Nice — every vacant unit here has photos and the building has a cover shot. Add more any time to make the listing even stronger.') }}</div>
          </div>
        @endif

        {{-- ── BUILDING PHOTO ─────────────────────────────────────────────── --}}
        <div class="pm-sec">
          <div class="pm-sec__head">
            <h5>🏢 {{ __('Building photo') }}</h5>
            @if($buildingMissing)
              <span class="pm-pill pm-pill--need">{{ __('missing') }}</span>
            @else
              <span class="pm-pill pm-pill--ok">{{ __('added') }}</span>
            @endif
          </div>
          <p class="pm-sec__hint">{{ __('One clear exterior shot of the whole building (or the entrance / common area). This is the cover image for all of this property’s vacancies.') }}</p>

          <div class="pm-thumbs">
            <div class="pm-thumb pm-thumb--big" id="pmBuildingThumb">
              @if($buildingPhotoUrl)
                <img src="{{ $buildingPhotoUrl }}" alt="{{ $property->name }}">
              @else
                <span class="pm-empty" style="margin:auto">{{ __('No building photo yet') }}</span>
              @endif
            </div>
          </div>

          <label class="pm-add" id="pmBuildingBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            {{ $buildingMissing ? __('Add building photo') : __('Replace building photo') }}
            <input type="file" accept="image/*" id="pmBuildingInput">
          </label>
          <div class="pm-tip">📷 {{ __('On your phone you can take it right now, or choose one from your gallery.') }}</div>
        </div>

        {{-- ── UNIT PHOTOS ────────────────────────────────────────────────── --}}
        <div class="pm-sec">
          <div class="pm-sec__head"><h5>🚪 {{ __('Unit photos') }}</h5></div>
          <p class="pm-sec__hint">{{ __('Interior photos of each unit — living room, bedrooms, kitchen, bathroom. Vacant units with no photos are shown first.') }}</p>

          @forelse($units as $unit)
            <div class="pm-unit" data-unit="{{ $unit->id }}">
              <div class="pm-sec__head">
                <h5 style="font-size:15px">{{ __('Unit') }} {{ $unit->unit_name ?? $unit->id }}</h5>
                @if($unit->is_vacant)<span class="pm-pill pm-pill--vacant">{{ __('vacant') }}</span>@endif
                @if($unit->needs_photos)
                  <span class="pm-pill pm-pill--need" data-role="need">{{ __('needs photos') }}</span>
                @else
                  <span class="pm-pill pm-pill--ok" data-role="count">{{ $unit->images->count() }} {{ trans_choice('photo|photos', $unit->images->count()) }}</span>
                @endif
              </div>

              <div class="pm-thumbs" data-role="thumbs">
                @foreach($unit->images as $img)
                  <div class="pm-thumb" data-img="{{ $img->id }}">
                    <img src="{{ $img->file_url }}" alt="{{ __('Unit photo') }}">
                    <button type="button" class="pm-thumb__del" title="{{ __('Remove') }}" onclick="pmDeleteUnitImage({{ $img->id }}, this)">×</button>
                  </div>
                @endforeach
              </div>

              <label class="pm-add" style="background:#2A3642">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                {{ __('Add unit photos') }}
                <input type="file" accept="image/*" multiple onchange="pmUploadUnitImages({{ $unit->id }}, this)">
              </label>
            </div>
          @empty
            <p class="pm-empty">{{ __('This property has no units yet. Add units first, then come back to photograph them.') }}</p>
          @endforelse
        </div>

      </div>
    </div>
  </div>
</div>

<div class="pm-toast" id="pmToast"></div>

<script>
const PM_CSRF = @json(csrf_token());
const PM_THUMB_UPDATE = @json(route('owner.property.thumbnailImage.update', $property->id));
const PM_UNIT_STORE   = @json(route('owner.property.unit-image.store'));
const PM_UNIT_DELETE  = @json(url('owner/unit-image')); // + /{id}

function pmToast(msg, isErr) {
  const t = document.getElementById('pmToast');
  t.textContent = msg; t.classList.toggle('err', !!isErr); t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2200);
}

// ── Building photo (property thumbnail) ──────────────────────────────────────
document.getElementById('pmBuildingInput').addEventListener('change', function () {
  const file = this.files && this.files[0];
  if (!file) return;
  const btn = document.getElementById('pmBuildingBtn');
  btn.classList.add('busy');
  const fd = new FormData();
  fd.append('file', file);
  fd.append('_token', PM_CSRF);
  fetch(PM_THUMB_UPDATE, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': PM_CSRF, 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      if (data.status === true || data.status === 'success') {
        // Instant local preview (server has stored it; a reload would confirm).
        const url = URL.createObjectURL(file);
        document.getElementById('pmBuildingThumb').innerHTML = '<img src="' + url + '" alt="building">';
        document.querySelector('#pmRoot .pm-sec .pm-pill').outerHTML = '<span class="pm-pill pm-pill--ok">{{ __('added') }}</span>';
        pmToast(@json(__('Building photo saved.')));
      } else {
        pmToast(data.message || @json(__('Upload failed.')), true);
      }
    })
    .catch(() => pmToast(@json(__('Upload failed.')), true))
    .finally(() => { btn.classList.remove('busy'); this.value = ''; });
});

// ── Unit photos (multi) ──────────────────────────────────────────────────────
function pmUploadUnitImages(unitId, input) {
  const files = input.files;
  if (!files || !files.length) return;
  const label = input.closest('.pm-add');
  label.classList.add('busy');
  const fd = new FormData();
  fd.append('unit_id', unitId);
  fd.append('_token', PM_CSRF);
  for (const f of files) fd.append('images[]', f);

  fetch(PM_UNIT_STORE, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': PM_CSRF, 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      if ((data.status === true || data.status === 'success') && data.data && data.data.images) {
        const wrap = input.closest('.pm-unit');
        const thumbs = wrap.querySelector('[data-role="thumbs"]');
        data.data.images.forEach(img => {
          const div = document.createElement('div');
          div.className = 'pm-thumb'; div.setAttribute('data-img', img.id);
          div.innerHTML = '<img src="' + img.url + '" alt="unit photo">' +
            '<button type="button" class="pm-thumb__del" onclick="pmDeleteUnitImage(' + img.id + ', this)">×</button>';
          thumbs.appendChild(div);
        });
        pmRefreshUnitBadge(wrap);
        pmToast(@json(__('Photos added.')));
      } else {
        pmToast(data.message || @json(__('Upload failed.')), true);
      }
    })
    .catch(() => pmToast(@json(__('Upload failed.')), true))
    .finally(() => { label.classList.remove('busy'); input.value = ''; });
}

function pmDeleteUnitImage(imgId, btn) {
  if (!confirm(@json(__('Remove this photo?')))) return;
  fetch(PM_UNIT_DELETE + '/' + imgId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': PM_CSRF, 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success' || data.status === true) {
        const thumb = btn.closest('.pm-thumb');
        const wrap = thumb.closest('.pm-unit');
        thumb.remove();
        pmRefreshUnitBadge(wrap);
        pmToast(@json(__('Photo removed.')));
      } else {
        pmToast(data.message || @json(__('Could not remove.')), true);
      }
    })
    .catch(() => pmToast(@json(__('Could not remove.')), true));
}

function pmRefreshUnitBadge(wrap) {
  const count = wrap.querySelectorAll('[data-role="thumbs"] .pm-thumb').length;
  const head = wrap.querySelector('.pm-sec__head');
  let badge = head.querySelector('[data-role="need"], [data-role="count"]');
  if (!badge) { badge = document.createElement('span'); head.appendChild(badge); }
  if (count === 0) {
    badge.className = 'pm-pill pm-pill--need'; badge.setAttribute('data-role', 'need');
    badge.textContent = @json(__('needs photos'));
  } else {
    badge.className = 'pm-pill pm-pill--ok'; badge.setAttribute('data-role', 'count');
    badge.textContent = count + ' ' + (count === 1 ? @json(__('photo')) : @json(__('photos')));
  }
}
</script>
@endsection
