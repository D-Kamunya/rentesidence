@php $app = $appName ?? (getOption('app_name') ?: 'Centresidence'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('Terms & Conditions') }} — {{ $app }}</title>
<style>
  *{box-sizing:border-box; margin:0; padding:0}
  body{background:#0B1526; color:#1F2A37;
    font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; line-height:1.55;
    min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;}
  .card{width:100%; max-width:760px; background:#fff; border-radius:16px; overflow:hidden;
    box-shadow:0 24px 60px rgba(0,0,0,.45)}
  .head{background:#0F2A4A; border-bottom:3px solid #E7A339; padding:22px 28px; color:#fff}
  .brand{font-size:19px; font-weight:800; letter-spacing:.01em}
  .head h1{font-size:15px; font-weight:600; color:#9fb6d6; margin-top:4px}
  .body{padding:24px 28px 28px}
  .lead{font-size:14.5px; color:#48566A; margin-bottom:16px}
  .lead strong{color:#1F2A37}
  .terms{border:1px solid #DCE6F1; background:#F7FAFE; border-radius:12px; padding:18px 20px;
    max-height:46vh; overflow-y:auto; font-size:14px; color:#3a4658}
  .terms h2{font-size:15px; color:#0F2A4A; margin:16px 0 6px}
  .terms h2:first-child{margin-top:0}
  .terms ul{padding-left:20px; margin:6px 0}
  .terms li{margin:5px 0}
  .terms p{margin:7px 0}
  .empty{color:#8A97A8; font-style:italic}
  .err{background:#FEF2F2; border:1px solid #FCA5A5; color:#B42318; border-radius:9px;
    padding:10px 14px; font-size:13.5px; margin:16px 0 0}
  form{margin-top:18px}
  .agree{display:flex; align-items:flex-start; gap:10px; font-size:14px; color:#1F2A37;
    background:#F4F6F8; border:1px solid #D8DEE6; border-radius:10px; padding:14px 16px}
  .agree input{margin-top:3px; width:18px; height:18px; flex:0 0 auto; accent-color:#185FA5}
  .actions{display:flex; align-items:center; justify-content:space-between; gap:14px; margin-top:18px; flex-wrap:wrap}
  .btn{appearance:none; border:0; cursor:pointer; background:#185FA5; color:#fff; font-weight:700;
    font-size:14.5px; padding:12px 26px; border-radius:10px}
  .btn:hover{background:#134c86}
  .logout{color:#8A97A8; font-size:13px; text-decoration:none}
  .logout:hover{color:#48566A; text-decoration:underline}
  .ver{margin-top:14px; font-size:11.5px; color:#8A97A8}
</style>
</head>
<body>
  <div class="card">
    <div class="head">
      <div class="brand">{{ $app }}</div>
      <h1>{{ __('Please review and accept our Terms & Conditions to continue') }}</h1>
    </div>
    <div class="body">
      <p class="lead">{!! __('Before you continue to your dashboard, please read the Terms & Conditions below. They govern your use of :app and our services.', ['app' => '<strong>'.e($app).'</strong>']) !!}</p>

      <div class="terms">
        @if (!empty(trim(strip_tags($terms ?? ''))))
          {!! $terms !!}
        @else
          <p class="empty">{{ __('The Terms & Conditions have not been published yet. Please contact support to proceed.') }}</p>
        @endif
      </div>

      @if ($errors->any())
        <div class="err">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('owner.terms.accept') }}">
        @csrf
        <label class="agree">
          <input type="checkbox" name="accept" value="1">
          <span>{{ __('I have read and agree to the Terms & Conditions.') }}</span>
        </label>
        <div class="actions">
          <button type="submit" class="btn">{{ __('Accept & continue') }}</button>
          <a class="logout" href="{{ url('/logout') }}">{{ __('Log out instead') }}</a>
        </div>
      </form>

      <div class="ver">{{ __('Version') }} {{ $version }}</div>
    </div>
  </div>
</body>
</html>
