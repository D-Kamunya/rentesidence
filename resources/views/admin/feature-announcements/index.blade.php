@extends('admin.layouts.app')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <style>
          .fam-head h1{font-size:22px;font-weight:700;color:#1b1e22;margin:0 0 4px;}
          .fam-head p{color:#6b7280;font-size:13.5px;margin:0 0 20px;}
          .fam-grid{display:grid;grid-template-columns:360px 1fr;gap:18px;align-items:start;}
          @media(max-width:960px){.fam-grid{grid-template-columns:1fr;}}
          .fam-card{background:#fff;border:1px solid #ececec;border-radius:14px;padding:20px 22px;box-shadow:0 1px 3px rgba(16,24,40,.04);}
          .fam-card h2{font-size:15px;font-weight:700;color:#1b1e22;margin:0 0 14px;}
          .fam-field{margin-bottom:13px;}
          .fam-field label{display:block;font-size:12.5px;font-weight:650;color:#4a4f57;margin-bottom:5px;}
          .fam-field input[type=text],.fam-field input[type=url],.fam-field textarea{width:100%;border:1px solid #e6e1d8;border-radius:10px;padding:10px 12px;font-size:14px;color:#1b1e22;outline:none;}
          .fam-field input:focus,.fam-field textarea:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.12);}
          .fam-aud{display:flex;flex-wrap:wrap;gap:6px;}
          .fam-aud label{display:inline-flex;align-items:center;gap:5px;font-size:12.5px;font-weight:600;color:#4a4f57;background:#f6f7f9;border:1px solid #e6e1d8;border-radius:999px;padding:5px 11px;cursor:pointer;margin:0;}
          .fam-aud input{margin:0;}
          .fam-row2{display:flex;gap:10px;}
          .fam-check{display:flex;align-items:center;gap:8px;font-size:13px;color:#4a4f57;margin:6px 0;}
          .fam-btn{border:none;border-radius:10px;padding:11px 16px;font-size:14px;font-weight:700;cursor:pointer;}
          .fam-btn--primary{background:#185FA5;color:#fff;width:100%;}
          .fam-btn--sm{padding:6px 11px;font-size:12px;border-radius:8px;}
          .fam-btn--ghost{background:#fff;border:1px solid #e6e1d8;color:#4a4f57;}
          .fam-btn--danger{background:#fff;border:1px solid #f0b8b0;color:#B42318;}
          .fam-table{width:100%;border-collapse:collapse;}
          .fam-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9aa2ad;font-weight:600;padding:11px 14px;border-bottom:1px solid #eee;}
          .fam-table td{padding:13px 14px;border-bottom:1px solid #f3f0ea;font-size:13.5px;color:#333;vertical-align:top;}
          .fam-pill{display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:999px;text-transform:uppercase;letter-spacing:.03em;}
          .fam-pill--live{background:#E1F5EE;color:#0F6E56;} .fam-pill--draft{background:#FEF3E7;color:#B45309;} .fam-pill--paused{background:#f3f4f6;color:#6b7280;}
          .fam-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
          .fam-flash--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;} .fam-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
          .fam-empty{padding:26px;text-align:center;color:#9aa2ad;font-size:13.5px;}
        </style>

        <div class="fam-head">
          <h1>{{ __('Feature Announcements') }}</h1>
          <p>{{ __('Publish a "what\'s new" note. Each targeted account sees it once in a modal on their next visit — link it to a Knowledge Base article for a full walkthrough (with video).') }}</p>
        </div>

        @if (session('success'))<div class="fam-flash fam-flash--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="fam-flash fam-flash--err">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="fam-flash fam-flash--err">{{ $errors->first() }}</div>@endif

        <div class="fam-grid">
          {{-- Create --}}
          <div class="fam-card">
            <h2 id="famFormTitle">{{ __('New announcement') }}</h2>
            <form method="POST" action="{{ route('admin.feature-announcements.store') }}" id="famForm">
              @csrf
              <input type="hidden" name="_method" id="famMethod" value="POST">
              <div class="fam-row2">
                <div class="fam-field" style="width:76px;">
                  <label>{{ __('Icon') }}</label>
                  <input type="text" name="icon" id="fa_icon" maxlength="8" placeholder="🎉" value="{{ old('icon') }}">
                </div>
                <div class="fam-field" style="flex:1;">
                  <label>{{ __('Title') }}</label>
                  <input type="text" name="title" id="fa_title" maxlength="120" required value="{{ old('title') }}">
                </div>
              </div>
              <div class="fam-field">
                <label>{{ __('Message') }}</label>
                <textarea name="body" id="fa_body" rows="4" maxlength="2000" required>{{ old('body') }}</textarea>
              </div>
              <div class="fam-field">
                <label>{{ __('Link a Knowledge Base article') }} <span style="color:#9aa2ad;font-weight:500;">({{ __('optional — no URL hunting') }})</span></label>
                <select name="kb_article_id" id="fa_kb">
                  <option value="">{{ __('— None —') }}</option>
                  @foreach ($kbArticles as $art)
                    <option value="{{ $art->id }}">{{ $art->title }}@if($art->audience) · {{ ucfirst(str_replace('_',' ',$art->audience)) }}@endif</option>
                  @endforeach
                </select>
                <div style="font-size:11.5px;color:#9aa2ad;margin-top:4px;">{{ __('The right per-role link is built automatically for whoever sees the announcement.') }}</div>
              </div>
              <div class="fam-row2">
                <div class="fam-field" style="flex:1;">
                  <label>{{ __('…or paste a link') }} <span style="color:#9aa2ad;font-weight:500;">({{ __('external / video') }})</span></label>
                  <input type="url" name="link_url" id="fa_link_url" maxlength="500" placeholder="https://…" value="{{ old('link_url') }}">
                </div>
                <div class="fam-field" style="width:120px;">
                  <label>{{ __('Link label') }}</label>
                  <input type="text" name="link_label" id="fa_link_label" maxlength="40" placeholder="{{ __('Learn more') }}" value="{{ old('link_label') }}">
                </div>
              </div>
              <div class="fam-field">
                <label>{{ __('Audience') }}</label>
                <div class="fam-aud" id="fa_aud">
                  @foreach ($audiences as $val => $label)
                    <label><input type="checkbox" name="audience[]" value="{{ $val }}" {{ $val === 'all' ? 'checked' : '' }}> {{ $label }}</label>
                  @endforeach
                </div>
              </div>
              <label class="fam-check"><input type="checkbox" name="is_active" value="1" id="fa_active" checked> {{ __('Active') }}</label>
              <label class="fam-check"><input type="checkbox" name="publish" value="1" id="fa_publish" checked> {{ __('Publish now (show to users)') }}</label>
              <button type="submit" class="fam-btn fam-btn--primary" style="margin-top:8px;">{{ __('Save announcement') }}</button>
              <button type="button" class="fam-btn fam-btn--ghost" id="famCancelEdit" style="width:100%;margin-top:8px;display:none;">{{ __('Cancel edit') }}</button>
            </form>
          </div>

          {{-- List --}}
          <div class="fam-card" style="padding:0;overflow:hidden;">
            @if ($announcements->isEmpty())
              <div class="fam-empty">{{ __('No announcements yet. Create one on the left.') }}</div>
            @else
              <table class="fam-table">
                <thead><tr><th>{{ __('Announcement') }}</th><th>{{ __('Audience') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
                <tbody>
                  @foreach ($announcements as $a)
                    @php
                      $audLabels = $a->audience === 'all' ? [__('Everyone')] : collect(explode(',', $a->audience))->map(fn($r) => $audiences[(int)$r] ?? $r)->all();
                      $isLive = $a->is_active && $a->published_at;
                      // Build the edit payload here (not inline in @json — a long inline array trips
                      // Blade's directive parser and emits invalid PHP).
                      $editData = [
                        'id' => $a->id, 'icon' => $a->icon, 'title' => $a->title, 'body' => $a->body,
                        'link_url' => $a->link_url, 'link_label' => $a->link_label, 'kb_article_id' => $a->kb_article_id,
                        'audience' => $a->audience, 'is_active' => (bool) $a->is_active, 'published' => (bool) $a->published_at,
                      ];
                    @endphp
                    <tr>
                      <td>
                        <div style="font-weight:650;color:#1b1e22;">{{ $a->icon }} {{ $a->title }}</div>
                        <div style="font-size:12px;color:#9aa2ad;margin-top:2px;max-width:44ch;">{{ \Illuminate\Support\Str::limit($a->body, 90) }}</div>
                        @if ($a->kb_article_id && $a->kbArticle)<div style="font-size:11.5px;color:#185FA5;margin-top:3px;">📚 {{ $a->kbArticle->title }}</div>
                        @elseif ($a->link_url)<div style="font-size:11.5px;color:#185FA5;margin-top:3px;">🔗 {{ $a->link_label ?: __('Learn more') }}</div>@endif
                      </td>
                      <td style="font-size:12.5px;color:#6b7280;">{{ implode(', ', $audLabels) }}</td>
                      <td>
                        @if ($isLive)<span class="fam-pill fam-pill--live">{{ __('Live') }}</span>
                        @elseif (! $a->published_at)<span class="fam-pill fam-pill--draft">{{ __('Draft') }}</span>
                        @else<span class="fam-pill fam-pill--paused">{{ __('Paused') }}</span>@endif
                      </td>
                      <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                          <button type="button" class="fam-btn fam-btn--ghost fam-btn--sm fam-edit"
                            data-a='@json($editData)'>{{ __('Edit') }}</button>
                          <form method="POST" action="{{ route('admin.feature-announcements.toggle', $a->id) }}">@csrf
                            <button type="submit" class="fam-btn fam-btn--ghost fam-btn--sm">{{ $a->is_active ? __('Pause') : __('Activate') }}</button>
                          </form>
                          <form method="POST" action="{{ route('admin.feature-announcements.destroy', $a->id) }}">@csrf @method('DELETE')
                            <button type="submit" class="fam-btn fam-btn--danger fam-btn--sm" data-cs-confirm="{{ __('Delete this announcement?') }}">{{ __('Delete') }}</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @if ($announcements->hasPages())<div style="padding:14px;">{{ $announcements->links() }}</div>@endif
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('famForm');
    var storeUrl = '{{ route('admin.feature-announcements.store') }}';
    var baseUpdate = '{{ url('admin/feature-announcements') }}';
    function resetToCreate() {
      form.setAttribute('action', storeUrl);
      document.getElementById('famMethod').value = 'POST';
      document.getElementById('famFormTitle').textContent = @json(__('New announcement'));
      document.getElementById('famCancelEdit').style.display = 'none';
      form.reset();
    }
    // KB article and a manual link are mutually exclusive — picking one disables the other.
    var kb = document.getElementById('fa_kb'), linkUrl = document.getElementById('fa_link_url');
    function syncLinkFields() {
      if (kb.value) { linkUrl.value = ''; linkUrl.disabled = true; linkUrl.style.opacity = .5; }
      else { linkUrl.disabled = false; linkUrl.style.opacity = 1; }
    }
    kb.addEventListener('change', syncLinkFields);
    document.getElementById('famCancelEdit').addEventListener('click', function () { resetToCreate(); syncLinkFields(); });
    document.querySelectorAll('.fam-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var a = JSON.parse(btn.getAttribute('data-a'));
        form.setAttribute('action', baseUpdate + '/' + a.id);
        document.getElementById('famMethod').value = 'PUT';
        document.getElementById('famFormTitle').textContent = @json(__('Edit announcement'));
        document.getElementById('fa_icon').value = a.icon || '';
        document.getElementById('fa_title').value = a.title || '';
        document.getElementById('fa_body').value = a.body || '';
        document.getElementById('fa_link_url').value = a.link_url || '';
        document.getElementById('fa_link_label').value = a.link_label || '';
        document.getElementById('fa_kb').value = a.kb_article_id || '';
        syncLinkFields();
        document.getElementById('fa_active').checked = !!a.is_active;
        document.getElementById('fa_publish').checked = !!a.published;
        var aud = (a.audience === 'all') ? ['all'] : String(a.audience).split(',');
        document.querySelectorAll('#fa_aud input').forEach(function (c) { c.checked = aud.indexOf(c.value) !== -1; });
        document.getElementById('famCancelEdit').style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    });
  })();
</script>
@endsection
