@extends('saas.frontend.layouts.app')

@section('content')
{{-- Dark-premium legal/policy page — matches the CS front-end (home + House Hunt).
     Renders the admin-set $pageTitle + $description; the .cspol-prose rules style whatever
     HTML/line-broken text the admin saved so it reads cleanly on the dark surface. --}}
<div class="cspol">
    <style>
        .cspol{
            --paper:#0E1218; --card:#161C26; --line:#242B36;
            --stone-900:#EDEAE3; --stone-700:#C4C0B7; --stone-500:#9A958A;
            --cs-blue:#185FA5; --cs-blue-2:#1c72c2; --amber:#E7A339;
            --serif:Georgia,'Iowan Old Style','Times New Roman',serif;
            --sans:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
            background:var(--paper); color:var(--stone-700); font-family:var(--sans);
            line-height:1.6; min-height:70vh;
        }
        .cspol *{box-sizing:border-box}

        /* Header band */
        .cspol-head{
            position:relative; overflow:hidden;
            background:
                radial-gradient(1100px 300px at 15% -40%, rgba(24,95,165,.22), transparent 70%),
                radial-gradient(700px 260px at 100% 0%, rgba(231,163,57,.08), transparent 70%),
                #12161D;
            border-bottom:1px solid var(--line);
        }
        .cspol-head__wrap{max-width:820px; margin:0 auto; padding:64px 24px 40px}
        .cspol-eyebrow{
            display:inline-flex; align-items:center; gap:8px; font-size:12px; font-weight:600;
            letter-spacing:.14em; text-transform:uppercase; color:var(--amber); margin-bottom:14px;
        }
        .cspol-eyebrow::before{content:''; width:22px; height:1px; background:var(--amber); opacity:.7}
        .cspol-head h1{
            margin:0; font-family:var(--serif); font-weight:600; letter-spacing:-.015em;
            color:var(--stone-900); font-size:clamp(28px,4.5vw,44px); line-height:1.1;
        }
        .cspol-head__sub{margin:16px 0 0; font-size:14.5px; color:var(--stone-500); max-width:60ch}

        /* Body */
        .cspol-body{max-width:820px; margin:0 auto; padding:44px 24px 88px}
        .cspol-prose{
            font-size:15.5px; line-height:1.78; color:var(--stone-700);
            max-width:72ch;
        }
        .cspol-prose > *:first-child{margin-top:0}
        .cspol-prose p{margin:0 0 18px}
        .cspol-prose h1,.cspol-prose h2,.cspol-prose h3,.cspol-prose h4{
            font-family:var(--serif); color:var(--stone-900); letter-spacing:-.01em;
            line-height:1.25; margin:34px 0 12px; font-weight:600;
        }
        .cspol-prose h1{font-size:26px} .cspol-prose h2{font-size:22px}
        .cspol-prose h3{font-size:18px} .cspol-prose h4{font-size:16px}
        .cspol-prose a{color:var(--cs-blue-2); text-decoration:none; border-bottom:1px solid rgba(28,114,194,.4)}
        .cspol-prose a:hover{color:#4a9de8; border-bottom-color:#4a9de8}
        .cspol-prose strong,.cspol-prose b{color:var(--stone-900); font-weight:600}
        .cspol-prose ul,.cspol-prose ol{margin:0 0 18px; padding-left:22px}
        .cspol-prose li{margin:0 0 8px}
        .cspol-prose ul li::marker{color:var(--cs-blue)}
        .cspol-prose hr{border:0; border-top:1px solid var(--line); margin:28px 0}
        .cspol-prose blockquote{
            margin:0 0 18px; padding:12px 18px; border-left:3px solid var(--cs-blue);
            background:var(--card); border-radius:0 8px 8px 0; color:var(--stone-700);
        }
        .cspol-prose table{width:100%; border-collapse:collapse; margin:0 0 18px; font-size:14.5px}
        .cspol-prose th,.cspol-prose td{border:1px solid var(--line); padding:9px 12px; text-align:left}
        .cspol-prose th{background:var(--card); color:var(--stone-900); font-weight:600}

        .cspol-empty{color:var(--stone-500); font-size:15px}

        @media (max-width:520px){
            .cspol-head__wrap{padding:48px 20px 32px}
            .cspol-body{padding:32px 20px 64px}
        }
    </style>

    <header class="cspol-head">
        <div class="cspol-head__wrap">
            <span class="cspol-eyebrow">{{ __('Legal') }}</span>
            <h1>{{ $pageTitle }}</h1>
            <p class="cspol-head__sub">{{ __('Please read this carefully. It governs your use of the platform and our services.') }}</p>
        </div>
    </header>

    <main class="cspol-body">
        <div class="cspol-prose">
            @if(!empty(trim(strip_tags($description ?? ''))))
                {!! nl2br($description) !!}
            @else
                <p class="cspol-empty">{{ __('This document has not been published yet. Please check back shortly.') }}</p>
            @endif
        </div>
    </main>
</div>
@endsection
