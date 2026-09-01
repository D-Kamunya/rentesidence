<style>
    .cs-auth { --ink:#e6ebf3; --muted:#94a3b8; --faint:#5b6b82; --line:rgba(255,255,255,.09);
        --blue:#3b82f6; --blue-deep:#185FA5; --card:rgba(255,255,255,.045);
        position:fixed; inset:0; overflow:auto; background:#080b12;
        font-family:system-ui,-apple-system,'Segoe UI',Roboto,'Poppins',sans-serif; color:var(--ink); z-index:1; }

    /* Ambient background */
    .cs-auth__bg { position:fixed; inset:0; overflow:hidden; z-index:0; }
    .cs-auth__grid { position:absolute; inset:-2px;
        background-image:linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
        background-size:46px 46px; mask-image:radial-gradient(ellipse 90% 80% at 30% 20%, #000 40%, transparent 90%);
        animation:csGridDrift 26s linear infinite; }
    .cs-auth__glow { position:absolute; border-radius:50%; filter:blur(90px); opacity:.5; }
    /* Cool cs-blue anchor (top-left) + warm marble amber (bottom-right) = upscale duotone ambient. */
    .cs-auth__glow--blue { width:520px; height:520px; left:-120px; top:-140px; background:radial-gradient(circle, #1f6fd6 0%, transparent 70%); animation:csFloat 16s ease-in-out infinite; }
    .cs-auth__glow--amber { width:500px; height:500px; right:-110px; bottom:-160px; background:radial-gradient(circle, #d59a35 0%, transparent 70%); opacity:.32; animation:csFloat 20s ease-in-out infinite reverse; }
    @keyframes csGridDrift { to { background-position:46px 46px, 46px 46px; } }
    @keyframes csFloat { 50% { transform:translate(24px, 20px); } }

    .cs-auth__inner { position:relative; z-index:1; min-height:100%; display:grid; grid-template-columns:1.05fr .95fr; }

    /* Brand panel */
    .cs-auth__brand { position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between; padding:54px 60px; border-right:0.5px solid var(--line); }
    .cs-auth__brandtop, .cs-auth__brandmid, .cs-auth__brandfoot { position:relative; z-index:2; }

    /* Login slideshow (behind the dark veil) */
    .cs-auth__slides { position:absolute; inset:0; z-index:0; }
    .cs-auth__slide { position:absolute; inset:0; background-size:cover; background-position:center; opacity:0;
        transform:scale(1.06); transition:opacity 1.6s ease; }
    .cs-auth__slide.is-active { opacity:1; animation:csKenBurns 9s ease-out forwards; }
    @keyframes csKenBurns { from { transform:scale(1.06); } to { transform:scale(1.14); } }
    .cs-auth__veil { position:absolute; inset:0; z-index:1;
        background:
            linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px) 0 0/46px 46px,
            linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px) 0 0/46px 46px,
            linear-gradient(120deg, rgba(8,11,18,.92) 0%, rgba(8,11,18,.60) 52%, rgba(8,11,18,.88) 100%); }
    .cs-auth__logo img { height:40px; width:auto; max-width:190px; object-fit:contain; }
    .cs-auth__wordmark { font-size:22px; font-weight:700; letter-spacing:-.01em; color:#fff; }
    .cs-auth__wordmark--dark { color:#0f172a; }
    .cs-auth__eyebrow { display:inline-block; font-size:11px; font-weight:600; letter-spacing:.18em; text-transform:uppercase; color:#7cc0ff;
        font-family:ui-monospace,'SFMono-Regular',Menlo,monospace; margin-bottom:18px; }
    .cs-auth__headline { font-size:38px; line-height:1.12; font-weight:700; letter-spacing:-.02em; color:#fff; margin:0 0 16px; max-width:15ch; text-wrap:balance; }
    .cs-auth__sub { font-size:15px; line-height:1.7; color:var(--muted); margin:0 0 30px; max-width:44ch; }
    .cs-auth__feats { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:14px; }
    .cs-auth__feats li { display:flex; align-items:center; gap:12px; font-size:13.5px; color:#cbd5e1; }
    .cs-auth__featic { width:34px; height:34px; flex:none; border-radius:10px; display:flex; align-items:center; justify-content:center;
        background:rgba(59,130,246,.12); border:0.5px solid rgba(59,130,246,.25); color:#7cc0ff; }
    .cs-auth__brandfoot { font-size:12px; color:var(--faint); letter-spacing:.02em; display:flex; align-items:center; gap:8px; }
    .cs-auth__dot { width:7px; height:7px; border-radius:50%; background:#1D9E75; box-shadow:0 0 10px #1D9E75; }

    /* Form panel */
    .cs-auth__panel { display:flex; align-items:center; justify-content:center; padding:48px 40px; }
    .cs-auth__card { width:100%; max-width:500px; background:var(--card); border:0.5px solid var(--line); border-radius:22px;
        padding:48px 46px; backdrop-filter:blur(14px); box-shadow:0 30px 60px rgba(0,0,0,.4);
        animation:csRise .5s cubic-bezier(.2,.8,.3,1) both; }
    @keyframes csRise { from { opacity:0; transform:translateY(14px); } }
    .cs-auth__cardlogo { display:none; margin-bottom:24px; }
    .cs-auth__cardlogo img { height:36px; }
    .cs-auth__title { font-size:34px; font-weight:700; letter-spacing:-.02em; color:#fff; margin:0 0 8px; }
    .cs-auth__hint { font-size:15px; color:var(--muted); margin:0 0 30px; }
    .cs-auth__form { display:flex; flex-direction:column; gap:19px; }

    .cs-fld { display:flex; flex-direction:column; gap:8px; }
    .cs-fld__label { font-size:12px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--faint); }
    .cs-fld__wrap { position:relative; display:flex; align-items:center; }
    .cs-fld__ic { position:absolute; left:15px; color:#5b6b82; display:flex; pointer-events:none; }
    .cs-fld__input { width:100%; background:#0c111b; border:0.5px solid #1e293b; border-radius:12px; color:var(--ink);
        font-size:15.5px; padding:14px 16px 14px 44px; outline:none; transition:border-color .15s, box-shadow .15s, background .15s; }
    .cs-fld__input::placeholder { color:#475569; }
    .cs-fld__input:focus { border-color:var(--blue-deep); background:#0d1420; box-shadow:0 0 0 3px rgba(59,130,246,.18); }
    .cs-fld__input.is-bad { border-color:#b4471f; }
    .cs-fld__eye { position:absolute; right:8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;
        background:transparent; border:none; color:#5b6b82; cursor:pointer; border-radius:8px; transition:color .13s, background .13s; }
    .cs-fld__eye:hover { color:#93c5fd; background:rgba(255,255,255,.05); }
    .cs-fld__err { font-size:12.5px; color:#f0906b; }

    .cs-auth__row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:4px; }
    .cs-check { display:flex; align-items:center; gap:9px; cursor:pointer; font-size:14px; color:var(--muted); margin:0; }
    .cs-check input { width:17px; height:17px; accent-color:var(--blue-deep); cursor:pointer; }
    .cs-auth__link { font-size:14px; color:#7cc0ff; text-decoration:none; font-weight:500; }
    .cs-auth__link:hover { color:#a9d5ff; }

    .cs-auth__submit { margin-top:8px; display:flex; align-items:center; justify-content:center; gap:10px; width:100%;
        background:linear-gradient(135deg,#2b7fe0 0%, #185FA5 100%); color:#fff; border:none; border-radius:12px;
        font-size:16px; font-weight:600; padding:15px 18px; cursor:pointer;
        box-shadow:0 8px 24px rgba(24,95,165,.4); transition:transform .12s, box-shadow .15s, filter .15s; }
    .cs-auth__submit:hover { filter:brightness(1.08); box-shadow:0 12px 30px rgba(24,95,165,.55); transform:translateY(-1px); }
    .cs-auth__submit:active { transform:translateY(0); }

    /* Neutral status note (e.g. password-reset link sent) */
    .cs-auth__note { display:flex; align-items:flex-start; gap:10px; background:rgba(29,158,117,.1); border:0.5px solid rgba(29,158,117,.3);
        color:#7ee0be; border-radius:12px; padding:13px 15px; font-size:13.5px; line-height:1.55; margin-bottom:22px; }
    .cs-auth__note svg { flex:none; margin-top:1px; }
    .cs-auth__back { display:inline-flex; align-items:center; gap:7px; margin-top:24px; font-size:14px; color:#7cc0ff; text-decoration:none; font-weight:500; }
    .cs-auth__back:hover { color:#a9d5ff; }

    .cs-auth__demo { margin-top:24px; padding-top:20px; border-top:0.5px solid var(--line); }
    .cs-auth__demolabel { font-size:11px; color:var(--faint); letter-spacing:.04em; }
    .cs-auth__demogrid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px; }
    .cs-auth__demobtn { font-size:12px; font-weight:500; color:#a9b6c8; background:rgba(255,255,255,.04); border:0.5px solid var(--line);
        border-radius:8px; padding:8px; cursor:pointer; transition:all .13s; font-family:ui-monospace,Menlo,monospace; }
    .cs-auth__demobtn:hover { color:#fff; border-color:rgba(59,130,246,.4); background:rgba(59,130,246,.1); }

    @media (max-width: 900px) {
        .cs-auth__inner { grid-template-columns:1fr; }
        .cs-auth__brand { display:none; }
        .cs-auth__cardlogo { display:block; }
        .cs-auth__panel { padding:32px 20px; min-height:100%; }
        .cs-auth__card { background:rgba(255,255,255,.05); }
    }
    @media (prefers-reduced-motion: reduce) {
        .cs-auth__grid, .cs-auth__glow, .cs-auth__card { animation:none; }
    }
</style>
