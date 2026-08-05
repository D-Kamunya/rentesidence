{{-- Centresidence UI Design System — shared tokens + cs-* component classes.
     Include once at the top of any Centresidence blade. --}}
<style>
    :root {
        --blue:#185FA5; --blue-hover:#0F4A84; --blue-light:#E6F1FB; --blue-border:#B5D4F4;
        --blue-faint:#185ea56e; --blue-ghost:#185ea51c;
        --green:#1D9E75; --green-dark:#0F6E56; --green-light:#E1F5EE;
        --amber:#854F0B; --amber-light:#FAEEDA; --amber-border:#F5D9A8;
        --red:#993C1D; --red-light:#FAECE7; --purple:#534AB7; --purple-hover:#3C3489;
        --gray-900:#111827; --gray-800:#1f2937; --gray-700:#374151; --gray-500:#6b7280;
        --gray-400:#9ca3af; --gray-200:#e5e7eb; --gray-100:#f3f4f6; --gray-50:#fafafa;
    }

    /* Title + breadcrumb */
    .cs-titlebar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:18px; }
    .cs-title { font-size:22px; font-weight:500; color:var(--gray-900); margin:0; }
    .cs-crumb { display:flex; gap:6px; align-items:center; font-size:12px; color:var(--gray-400); list-style:none; padding:0; margin:6px 0 0; }
    .cs-crumb a { color:var(--blue); font-weight:500; text-decoration:none; }
    .cs-muted { color:var(--gray-500); font-size:12.5px; }

    /* Tabs (section nav) */
    .cs-tabs { display:flex; flex-wrap:wrap; gap:6px; background:var(--gray-100); border-radius:8px; padding:4px; margin-bottom:22px; }
    .cs-tab { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500; color:var(--gray-500);
        padding:6px 14px; border-radius:6px; text-decoration:none; transition:all .15s; white-space:nowrap; }
    .cs-tab:hover { color:var(--gray-900); }
    .cs-tab.is-active { background:#fff; color:var(--gray-900); box-shadow:0 1px 3px rgba(0,0,0,.08); }

    /* Cards */
    .cs-card { background:#fff; border:0.5px solid var(--blue-faint); border-radius:12px; padding:0; overflow:hidden; margin-bottom:18px;
        box-shadow:0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); }
    .cs-card__head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;
        padding:.85rem 1.1rem; border-bottom:0.5px solid var(--gray-200); background:var(--gray-50); }
    .cs-card__title { font-size:14px; font-weight:600; color:var(--gray-900); margin:0; }
    .cs-card__body { padding:18px; }
    .cs-card--pad { padding:18px; }

    /* Tables */
    .cs-tablewrap { width:100%; overflow-x:auto; }
    .cs-table { width:100%; border-collapse:collapse; }
    .cs-table thead th { background:var(--gray-50); border-bottom:0.5px solid var(--gray-200);
        font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-500);
        padding:.65rem 1rem; text-align:left; white-space:nowrap; }
    .cs-table tbody td { padding:.8rem 1rem; font-size:13px; color:var(--gray-700); border-bottom:0.5px solid var(--gray-100); vertical-align:middle; }
    .cs-table tbody tr:nth-child(even) td { background:var(--gray-50); }
    .cs-table tbody tr:hover td { background:var(--gray-100); }
    .cs-table .cs-empty { text-align:center; color:var(--gray-400); padding:1.4rem; }

    /* Badges */
    .cs-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500;
        padding:3px 9px; border-radius:99px; white-space:nowrap; }
    .cs-badge.is-paid    { background:var(--green-light); color:var(--green-dark); }
    .cs-badge.is-pending { background:var(--amber-light); color:var(--amber); border:0.5px solid var(--amber-border); }
    .cs-badge.is-danger  { background:var(--red-light); color:var(--red); }
    .cs-badge.is-blue    { background:var(--blue-light); color:#0C447C; border:0.5px solid var(--blue-border); }
    .cs-badge.is-purple  { background:#ECEBF8; color:var(--purple); }
    .cs-badge.is-grey    { background:var(--gray-100); color:var(--gray-500); border:0.5px solid var(--gray-200); }

    /* Amount pill */
    .cs-amt { font-size:13px; font-weight:600; color:var(--gray-800); white-space:nowrap; }

    /* Buttons */
    .cs-btn { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500;
        padding:7px 15px; border-radius:7px; border:none; cursor:pointer; text-decoration:none; transition:all .13s; }
    .cs-btn--primary { background:var(--blue); color:#fff; }
    .cs-btn--primary:hover { background:var(--blue-hover); transform:translateY(-1px); color:#fff; }
    .cs-btn--purple { background:var(--purple); color:#fff; }
    .cs-btn--purple:hover { background:var(--purple-hover); transform:translateY(-1px); color:#fff; }
    .cs-btn--ghost { background:var(--gray-100); color:var(--gray-700); border:0.5px solid var(--gray-200); }
    .cs-btn--ghost:hover { background:var(--blue); color:#fff; }
    .cs-btn--complete { background:var(--green-light); color:var(--green-dark); border:0.5px solid #A7DFC9; }
    .cs-btn--sm { padding:5px 11px; font-size:11.5px; }
    .cs-btn:disabled { cursor:not-allowed; }

    /* Cost breakdown lines (financing apply) */
    .cs-costline { display:flex; justify-content:space-between; align-items:center; gap:12px;
        font-size:13px; color:var(--gray-700); padding:5px 0; }
    .cs-costline b { font-variant-numeric:tabular-nums; }
    .cs-costline--total { border-top:0.5px solid var(--gray-200); margin-top:4px; padding-top:9px;
        font-size:14px; font-weight:600; color:var(--gray-900); }

    /* Stat grid */
    .cs-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
    .cs-stat { background:#fff; border:0.5px solid var(--blue-faint); border-radius:12px; padding:16px;
        box-shadow:0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);
        transition:all .25s ease; }
    .cs-stat:hover { border-color:var(--blue); transform:translateY(-3px);
        box-shadow:0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18); }
    .cs-stat__dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-bottom:12px; }
    .cs-stat__value { font-size:18px; font-weight:600; color:var(--gray-800); line-height:1.2; }
    .cs-stat__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400); margin-top:4px; }

    /* Forms */
    .cs-label { display:block; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400); margin-bottom:6px; }
    .cs-input, .cs-select { width:100%; border:0.5px solid var(--gray-200); border-radius:7px; padding:8px 11px; font-size:13px; color:var(--gray-700); background:#fff; }
    .cs-input:focus, .cs-select:focus { outline:none; border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .cs-input--sm { padding:5px 8px; font-size:12px; width:auto; }
    .cs-field { margin-bottom:16px; }

    /* Alerts / notices */
    .cs-alert { border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:18px; }
    .cs-alert.is-success { background:var(--green-light); color:var(--green-dark); border:0.5px solid #A7DFC9; }
    .cs-alert.is-danger  { background:var(--red-light); color:var(--red); border:0.5px solid #E9C4B6; }
    .cs-alert.is-amber   { background:var(--amber-light); color:var(--amber); border:0.5px solid var(--amber-border); }
    .cs-alert.is-info    { background:var(--blue-faint); color:var(--blue); border:0.5px solid rgba(24,95,165,.18); }

    /* Marketplace-style module cards */
    .cs-modgrid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
    .cs-modcard { display:flex; flex-direction:column; background:#fff; border:0.5px solid var(--blue-faint); border-radius:14px; overflow:hidden; text-decoration:none; height:100%; transition:all .25s ease;
        box-shadow:0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); }
    .cs-modcard:hover { transform:translateY(-3px); box-shadow:0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18); }
    .cs-modcard__media { height:120px; display:flex; align-items:center; justify-content:center; position:relative; }
    .cs-modcard__media i { font-size:48px; color:#fff; }
    .cs-modcard__chip { position:absolute; top:10px; right:10px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#fff; background:rgba(255,255,255,.22); padding:3px 8px; border-radius:99px; }
    .cs-modcard__body { padding:16px; flex:1; display:flex; flex-direction:column; }
    .cs-modcard__name { font-size:15px; font-weight:600; color:var(--gray-900); margin-bottom:4px; }
    .cs-modcard__tag { font-size:12.5px; color:var(--gray-500); line-height:1.45; flex:1; }
    .cs-modcard__meta { margin-top:14px; display:flex; align-items:center; justify-content:space-between; font-size:12px; color:var(--gray-700); }
    .cs-modcard__cta { display:inline-flex; align-items:center; gap:4px; font-weight:600; }

    /* Module detail hero + sections */
    .cs-hero { display:flex; gap:18px; align-items:center; border-radius:16px; padding:24px; margin-bottom:22px; color:#fff; }
    .cs-hero__icon { width:64px; height:64px; border-radius:16px; background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; flex:none; }
    .cs-hero__icon i { font-size:34px; color:#fff; }
    .cs-hero__title { font-size:22px; font-weight:700; margin:0; }
    .cs-hero__tag { font-size:14px; opacity:.92; margin-top:4px; }
    .cs-section { margin-bottom:22px; }
    .cs-section__label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400); margin-bottom:8px; }
    .cs-benefits { list-style:none; padding:0; margin:0; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .cs-benefits li { display:flex; align-items:flex-start; gap:8px; font-size:13px; color:var(--gray-700); }
    .cs-benefits li i { color:var(--green); margin-top:2px; }
    .cs-steps { white-space:pre-line; font-size:13.5px; color:var(--gray-700); line-height:1.7; }

    /* Compact 3-up stat row (module cards) */
    .cs-ministat { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin:10px 0 4px; padding:10px 0; border-top:0.5px solid var(--gray-200); }
    .cs-ministat > div { text-align:center; }
    .cs-ministat b { display:block; font-size:15px; font-weight:700; color:var(--gray-900); line-height:1.2; }
    .cs-ministat span { font-size:9.5px; color:var(--gray-500); text-transform:uppercase; letter-spacing:.04em; }

    /* Knowledge base — blog-grade reading polish */
    .cs-kbhero { border-radius:16px; padding:30px 32px; margin-bottom:22px; color:#fff;
        background:linear-gradient(135deg,#185FA5,#0F4A84); box-shadow:0 10px 28px rgba(24,95,165,.22); }
    .cs-kbhero .cs-crumb a, .cs-kbhero .cs-crumb li { color:rgba(255,255,255,.82); }
    .cs-kbhero__title { font-size:30px; font-weight:700; line-height:1.25; margin:8px 0 10px; color:#fff; }
    .cs-kbhero__excerpt { font-size:15px; line-height:1.6; color:rgba(255,255,255,.86); margin-bottom:20px; max-width:680px; }
    .cs-chip { display:inline-block; font-size:11px; font-weight:500; padding:4px 12px; border-radius:99px;
        background:rgba(255,255,255,.16); color:#fff; border:0.5px solid rgba(255,255,255,.22); }
    .cs-author { display:flex; align-items:center; gap:12px; }
    .cs-author__av { width:42px; height:42px; border-radius:11px; flex:none; display:flex; align-items:center; justify-content:center;
        font-size:17px; font-weight:600; color:#fff; background:linear-gradient(135deg,#185FA5,#534AB7); }
    .cs-kbhero .cs-author__av { background:rgba(255,255,255,.18); }
    .cs-author__name { font-size:13.5px; font-weight:600; }
    .cs-author__meta { font-size:11.5px; opacity:.82; }
    .cs-kb-body { font-size:15.5px; line-height:1.8; color:var(--gray-700); }
    .cs-kb-body h2 { font-size:20px; font-weight:700; color:var(--gray-900); margin:26px 0 10px; }
    .cs-kb-body h3 { font-size:16.5px; font-weight:600; color:var(--gray-900); margin:22px 0 8px; }
    .cs-kb-body p { margin:0 0 14px; }
    .cs-kb-body ul, .cs-kb-body ol { margin:0 0 14px; padding-left:22px; }
    .cs-kb-body li { margin-bottom:7px; }
    .cs-kb-body strong { color:var(--gray-900); font-weight:600; }
    .cs-kb-body a { color:var(--blue); }
    .cs-kb-body blockquote { border-left:3px solid var(--blue); margin:0 0 14px; padding:4px 0 4px 16px; color:var(--gray-500); }
    .cs-kbitem { display:flex; justify-content:space-between; gap:14px; align-items:center; padding:14px 18px;
        border-bottom:0.5px solid var(--gray-200); text-decoration:none; color:var(--gray-800); transition:background .15s ease; }
    .cs-kbitem:last-child { border-bottom:none; }
    .cs-kbitem:hover { background:var(--blue-faint); }
    .cs-topic { display:block; text-decoration:none; color:inherit; background:#fff; border:0.5px solid var(--blue-faint);
        border-radius:12px; padding:18px; transition:all .2s ease;
        box-shadow:0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); }
    .cs-topic:hover { transform:translateY(-2px); border-color:var(--blue); }
    .cs-topic__ic { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;
        background:var(--blue-faint); color:var(--blue); font-size:20px; margin-bottom:12px; }

    @media (max-width:1100px) { .cs-stats { grid-template-columns:repeat(2,1fr); } .cs-modgrid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:680px)  { .cs-benefits { grid-template-columns:1fr; } .cs-hero { flex-direction:column; text-align:center; align-items:center; } }
    @media (max-width:540px)  { .cs-stats { grid-template-columns:1fr; } .cs-modgrid { grid-template-columns:1fr; } .cs-titlebar { align-items:flex-start; } }
</style>
