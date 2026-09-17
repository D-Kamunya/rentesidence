{{-- DEV ONLY: a persistent, copyable panel showing a just-created account's temporary
     credentials, so the login flow can be tested locally without live email/SMS. Rendered
     only when config('app.debug') and a 'dev_credentials' flash is present — never in prod.
     Stays on screen (dismissible) rather than auto-hiding like a toast. --}}
@if (config('app.debug') && session('dev_credentials'))
  @php $dc = session('dev_credentials'); @endphp
  <div id="devCredsPanel" style="margin:0 0 18px;border:1px dashed #B45309;background:#FEF6E9;border-radius:12px;padding:14px 16px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
      <span style="font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#B45309;background:#FBEEDC;border:1px solid #E7C99A;padding:2px 8px;border-radius:999px;">{{ __('Dev only') }}</span>
      <strong style="color:#7A3E06;font-size:13.5px;">{{ __('Test login credentials') }}@if(!empty($dc['name'])) — {{ $dc['name'] }}@endif</strong>
      <button type="button" onclick="document.getElementById('devCredsPanel').remove()" style="margin-left:auto;border:none;background:transparent;color:#B45309;font-size:18px;line-height:1;cursor:pointer;">&times;</button>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:18px;font-size:13.5px;color:#5b3d15;font-family:ui-monospace,Menlo,Consolas,monospace;">
      <div><span style="color:#9a7b4a;">{{ __('Email') }}:</span> <span id="dcEmail">{{ $dc['email'] ?? '—' }}</span></div>
      @if (!empty($dc['phone']))<div><span style="color:#9a7b4a;">{{ __('Phone') }}:</span> {{ $dc['phone'] }}</div>@endif
      <div><span style="color:#9a7b4a;">{{ __('Temp password') }}:</span> <span id="dcPass">{{ $dc['password'] ?? '—' }}</span></div>
      <button type="button" onclick="dcCopy()" style="border:1px solid #E7C99A;background:#fff;color:#B45309;font-weight:650;font-size:12px;padding:3px 12px;border-radius:8px;cursor:pointer;">{{ __('Copy') }}</button>
    </div>
    <div style="margin-top:8px;font-size:11.5px;color:#9a7b4a;">{{ __('They will be asked to set their own password on first sign-in. This panel never appears in production.') }}</div>
  </div>
  <script>
    function dcCopy(){
      var t = '{{ $dc['email'] ?? '' }}' + '  ' + '{{ $dc['password'] ?? '' }}';
      try {
        if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(t); }
        else { var ta=document.createElement('textarea'); ta.value=t; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); }
      } catch(e){}
    }
  </script>
@endif
