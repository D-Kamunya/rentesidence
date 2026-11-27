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
          .pm-add--unit{background:#2A3642}
          .pm-add--unit:hover{background:#3a4857}
          /* progress state — once a unit has a photo the button goes green ("done, add more") */
          #pmRoot .pm-add--done{background:#fff !important;color:#166534 !important;border:1.5px solid #9BD9B2 !important}
          #pmRoot .pm-add--done:hover{background:#F1FBF4 !important;color:#166534 !important}
          .pm-tip{font-size:12px;color:var(--muted);margin-top:9px}
          /* room chips — tap a room, the camera opens, the photo is tagged with that room */
          .pm-rooms{display:flex;flex-wrap:wrap;gap:9px;align-items:center;margin-top:4px}
          .pm-rooms__label{font-size:12.5px;font-weight:700;color:var(--muted);margin-right:2px}
          .pm-room{display:inline-flex;align-items:center;gap:7px;background:#F2F5F9;border:1px solid #DEE6EF;color:#2A3642;
            font-weight:650;font-size:13px;padding:9px 14px;border-radius:99px;cursor:pointer;transition:.15s;margin:0}
          .pm-room:hover{background:#E7EEF7;border-color:var(--cs-blue)}
          .pm-room.busy{opacity:.5;pointer-events:none}
          .pm-room svg{width:15px;height:15px;color:var(--cs-blue)}
          .pm-room input{display:none}
          /* green once that room has a photo (progress at a glance) */
          .pm-room--has{background:#E7F6EC;border-color:#9BD9B2;color:#166534}
          .pm-room--has:hover{background:#DCF1E3;border-color:#2EA563}
          .pm-room__check{display:none;color:#2EA563}
          .pm-room--has .pm-room__check{display:inline-block}
          .pm-room--has .pm-room__cam{display:none}
          /* per-thumbnail room picker — tag/re-tag any photo (incl. bulk-uploaded) without re-snapping */
          .pm-thumb__tag{position:absolute;left:5px;bottom:5px;max-width:calc(100% - 12px);background:rgba(17,20,26,.80);
            color:#fff;font-size:10px;font-weight:700;padding:3px 7px;border:none;border-radius:6px;cursor:pointer;
            -webkit-appearance:none;appearance:none;text-overflow:ellipsis}
          .pm-thumb__tag:hover{background:rgba(17,20,26,.94)}
          .pm-thumb__tag option{color:#111}
          /* each unit is its own spaced card (left accent = progress at a glance) */
          .pm-unit{border:1px solid var(--line);border-left:4px solid var(--line);border-radius:14px;
            padding:18px 18px 20px;margin-bottom:18px;background:#FCFBF9;transition:border-color .2s,background .2s}
          .pm-unit:last-child{margin-bottom:0}
          .pm-unit.has-photos{border-left-color:#2EA563;background:#FAFDFB}
          .pm-unit.needs-photos{border-left-color:var(--amber)}
          /* top back-link + bottom done-button + autosave reassurance */
          .pm-back{display:inline-flex;align-items:center;gap:8px;color:var(--cs-blue);font-weight:700;font-size:14px;margin-bottom:16px}
          .pm-back svg{width:16px;height:16px}
          #pmRoot .pm-back,#pmRoot .pm-back:hover{color:var(--cs-blue) !important}
          .pm-autosave{display:inline-flex;align-items:center;gap:7px;color:#166534;font-size:12.5px;font-weight:700;
            background:#E7F6EC;border:1px solid #BfE8CB;border-radius:99px;padding:5px 13px}
          .pm-done-row{display:flex;justify-content:flex-end;margin-top:26px;padding-top:20px;border-top:1px solid var(--line)}
          .pm-done{display:inline-flex;align-items:center;justify-content:center;gap:9px;background:var(--cs-blue);
            color:#fff !important;font-weight:700;font-size:15px;padding:13px 26px;border-radius:12px;text-decoration:none}
          .pm-done:hover{background:var(--cs-blue-2)}
          #pmRoot .pm-done,#pmRoot .pm-done:hover{color:#fff !important}
          .pm-done svg{width:17px;height:17px}
          .pm-toast{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);background:#1B1E22;color:#fff;
            padding:11px 20px;border-radius:10px;font-size:13.5px;font-weight:600;opacity:0;pointer-events:none;transition:.2s;z-index:9999}
          .pm-toast.show{opacity:1}
          .pm-toast.err{background:#c0392b}
        </style>

        <a href="{{ $backRoute }}" class="pm-back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
          {{ $backLabel }}
        </a>

        <p class="pm-intro">
          {{ __('Add real photos so this property’s vacancies actually sell. Snap them right here from your phone — a clear building photo heads the listing, and interior photos of each unit do the rest.') }}
        </p>

        <div style="margin-bottom:22px"><span class="pm-autosave">✓ {{ __('Photos save automatically as you add them') }}</span></div>

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
              {{-- Same branded "No photo available" placeholder as the unit images, for consistency --}}
              <img src="{{ $buildingPhotoUrl ?? asset('assets/images/property.png') }}" alt="{{ $property->name }}">
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
            <div class="pm-unit {{ $unit->needs_photos ? 'needs-photos' : 'has-photos' }}" data-unit="{{ $unit->id }}">
              <div class="pm-sec__head">
                <h5 style="font-size:15px">{{ __('Unit') }} {{ $unit->unit_name ?? $unit->id }}</h5>
                @if($unit->is_vacant)<span class="pm-pill pm-pill--vacant">{{ __('vacant') }}</span>@endif
                @if($unit->needs_photos)
                  <span class="pm-pill pm-pill--need" data-role="need">{{ __('needs photos') }}</span>
                @else
                  <span class="pm-pill pm-pill--ok" data-role="count">{{ $unit->images->count() }} {{ trans_choice('photo|photos', $unit->images->count()) }}</span>
                @endif
              </div>

              @php $unitRooms = unitPhotoCategoriesFor($unit); @endphp
              <div class="pm-thumbs" data-role="thumbs" data-cats="{{ json_encode($unitRooms) }}">
                @foreach($unit->images as $img)
                  <div class="pm-thumb" data-img="{{ $img->id }}" data-cat="{{ $img->category }}">
                    <img src="{{ $img->file_url }}" alt="{{ __('Unit photo') }}">
                    <select class="pm-thumb__tag" title="{{ __('Tag the room') }}" onchange="pmSetCategory({{ $img->id }}, this)">
                      <option value="">{{ __('＋ Tag room') }}</option>
                      @foreach($unitRooms as $slug => $label)
                        <option value="{{ $slug }}" @selected($img->category === $slug)>{{ $label }}</option>
                      @endforeach
                    </select>
                    <button type="button" class="pm-thumb__del" title="{{ __('Remove') }}" onclick="pmDeleteUnitImage({{ $img->id }}, this)">×</button>
                  </div>
                @endforeach
              </div>

              {{-- Tap a room → the camera opens → the photo is tagged with that room. A chip turns
                   green once that room has a photo. Optional: tagging is never required. --}}
              @php $unitCats = $unit->images->pluck('category')->filter()->unique()->all(); @endphp
              <div class="pm-rooms">
                <span class="pm-rooms__label">📷 {{ __('Snap a room:') }}</span>
                @foreach(unitPhotoCategoriesFor($unit) as $slug => $label)
                  <label class="pm-room {{ in_array($slug, $unitCats, true) ? 'pm-room--has' : '' }}" data-cat="{{ $slug }}">
                    <svg class="pm-room__cam" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <svg class="pm-room__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    {{ $label }}
                    <input type="file" accept="image/*" multiple data-category="{{ $slug }}" onchange="pmUploadUnitImages({{ $unit->id }}, this)">
                  </label>
                @endforeach
              </div>
            </div>
          @empty
            <p class="pm-empty">{{ __('This property has no units yet. Add units first, then come back to photograph them.') }}</p>
          @endforelse
        </div>

        <div class="pm-done-row">
          <a href="{{ $backRoute }}" class="pm-done">
            {{ __('Done') }} — {{ $backLabel }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
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
const PM_UNIT_CATEGORY = @json(url('owner/property/unit-image')); // + /{id}/category

function pmTagSelectHtml(thumbsEl, imgId, cat) {
  let cats = {};
  try { cats = JSON.parse(thumbsEl.getAttribute('data-cats') || '{}'); } catch (e) {}
  let opts = '<option value="">' + @json(__('＋ Tag room')) + '</option>';
  for (const slug in cats) {
    opts += '<option value="' + slug + '"' + (slug === cat ? ' selected' : '') + '>' + pmEsc(cats[slug]) + '</option>';
  }
  return '<select class="pm-thumb__tag" title="' + @json(__('Tag the room')) + '" onchange="pmSetCategory(' + imgId + ', this)">' + opts + '</select>';
}

function pmSetCategory(imgId, sel) {
  const fd = new FormData();
  fd.append('_token', PM_CSRF);
  fd.append('category', sel.value);
  fetch(PM_UNIT_CATEGORY + '/' + imgId + '/category', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': PM_CSRF, 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      if (data.status === true || data.status === 'success') {
        const thumb = sel.closest('.pm-thumb');
        thumb.setAttribute('data-cat', (data.data && data.data.category) || '');
        pmRefreshRoomChips(sel.closest('.pm-unit'));
        pmToast(@json(__('Room updated.')));
      } else {
        pmToast(data.message || @json(__('Could not update.')), true);
      }
    })
    .catch(() => pmToast(@json(__('Could not update.')), true));
}

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
function pmEsc(s) { const d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

function pmUploadUnitImages(unitId, input) {
  const files = input.files;
  if (!files || !files.length) return;
  const chip = input.closest('.pm-room') || input.closest('.pm-add');
  if (chip) chip.classList.add('busy');
  const fd = new FormData();
  fd.append('unit_id', unitId);
  fd.append('_token', PM_CSRF);
  const category = input.dataset ? (input.dataset.category || '') : '';
  if (category) fd.append('category', category);
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
          div.setAttribute('data-cat', img.category || '');
          div.innerHTML = '<img src="' + img.url + '" alt="unit photo">' +
            pmTagSelectHtml(thumbs, img.id, img.category || '') +
            '<button type="button" class="pm-thumb__del" onclick="pmDeleteUnitImage(' + img.id + ', this)">×</button>';
          thumbs.appendChild(div);
        });
        pmRefreshUnitBadge(wrap);
        pmRefreshRoomChips(wrap);
        pmToast(@json(__('Photos added.')));
      } else {
        pmToast(data.message || @json(__('Upload failed.')), true);
      }
    })
    .catch(() => pmToast(@json(__('Upload failed.')), true))
    .finally(() => { if (chip) chip.classList.remove('busy'); input.value = ''; });
}

function pmDeleteUnitImage(imgId, btn) {
  const doDelete = function () {
    fetch(PM_UNIT_DELETE + '/' + imgId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': PM_CSRF, 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success' || data.status === true) {
          const thumb = btn.closest('.pm-thumb');
          const wrap = thumb.closest('.pm-unit');
          thumb.remove();
          pmRefreshUnitBadge(wrap);
          pmRefreshRoomChips(wrap);
          pmToast(@json(__('Photo removed.')));
        } else {
          pmToast(data.message || @json(__('Could not remove.')), true);
        }
      })
      .catch(() => pmToast(@json(__('Could not remove.')), true));
  };
  // Use the app's CS confirm dialog for destructive actions (never the native browser alert).
  if (window.csConfirm) {
    window.csConfirm({
      title: @json(__('Remove this photo?')),
      message: @json(__('This permanently deletes the photo from the listing.')),
      confirmText: @json(__('Remove')), cancelText: @json(__('Cancel')), tone: 'danger'
    }).then(function (ok) { if (ok) doDelete(); });
  } else if (confirm(@json(__('Remove this photo?')))) {
    doDelete();
  }
}

// Recompute which room chips are 'has photos' (green) from the unit's current thumbnails.
function pmRefreshRoomChips(wrap) {
  const cats = new Set();
  wrap.querySelectorAll('[data-role="thumbs"] .pm-thumb').forEach(t => {
    const c = t.getAttribute('data-cat');
    if (c) cats.add(c);
  });
  wrap.querySelectorAll('.pm-room').forEach(chip => {
    const c = chip.getAttribute('data-cat');
    chip.classList.toggle('pm-room--has', !!c && cats.has(c));
  });
}

function pmRefreshUnitBadge(wrap) {
  const count = wrap.querySelectorAll('[data-role="thumbs"] .pm-thumb').length;
  const head = wrap.querySelector('.pm-sec__head');
  let badge = head.querySelector('[data-role="need"], [data-role="count"]');
  if (!badge) { badge = document.createElement('span'); head.appendChild(badge); }
  const addBtn = wrap.querySelector('.pm-add--unit');
  const addLabel = addBtn ? addBtn.querySelector('.pm-add__label') : null;
  if (count === 0) {
    badge.className = 'pm-pill pm-pill--need'; badge.setAttribute('data-role', 'need');
    badge.textContent = @json(__('needs photos'));
    wrap.classList.add('needs-photos'); wrap.classList.remove('has-photos');
    if (addBtn) addBtn.classList.remove('pm-add--done');
    if (addLabel) addLabel.textContent = @json(__('Add unit photos'));
  } else {
    badge.className = 'pm-pill pm-pill--ok'; badge.setAttribute('data-role', 'count');
    badge.textContent = count + ' ' + (count === 1 ? @json(__('photo')) : @json(__('photos')));
    wrap.classList.add('has-photos'); wrap.classList.remove('needs-photos');
    if (addBtn) addBtn.classList.add('pm-add--done');
    if (addLabel) addLabel.textContent = @json(__('Add more photos'));
  }
}
</script>
@endsection
