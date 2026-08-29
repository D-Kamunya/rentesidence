<style>
    .ag-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
    .ag-title { font-size:22px; font-weight:500; color:#111827; margin:0 0 4px; }
    .ag-sub   { font-size:13.5px; color:#6b7280; margin:0; }

    .ag-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; }

    .ag-table { width:100%; }
    .ag-table thead th { background:#fafafa; border-bottom:0.5px solid #e5e7eb; font-size:10px; font-weight:600;
        text-transform:uppercase; letter-spacing:.06em; color:#6b7280; padding:.7rem 1rem; }
    .ag-table tbody td { padding:.8rem 1rem; border-bottom:0.5px solid #f3f4f6; font-size:13px; color:#111827; vertical-align:middle; }
    .ag-table tbody tr:last-child td { border-bottom:none; }
    .ag-muted { color:#6b7280; font-size:12.5px; }
    .ag-empty { text-align:center; color:#9ca3af; padding:2.5rem 1rem; font-size:13px; }
    .ag-link  { color:#185FA5; text-decoration:none; font-weight:500; font-size:12.5px; }
    .ag-link:hover { color:#0F4A84 !important; text-decoration:underline; }

    .ag-badge { display:inline-flex; align-items:center; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; white-space:nowrap; }
    .ag-badge--green { background:#E1F5EE; color:#0F6E56; }
    .ag-badge--amber { background:#FAEEDA; color:#854F0B; }
    .ag-badge--blue  { background:#E6F1FB; color:#0C447C; }
    .ag-badge--coral { background:#FAECE7; color:#993C1D; }
    .ag-badge--muted { background:#f3f4f6; color:#6b7280; }

    .ag-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:500; padding:8px 16px;
        border-radius:8px; text-decoration:none; cursor:pointer; border:none; transition:background .15s, transform .12s; }
    .ag-btn--primary { background:#185FA5; color:#fff; box-shadow:0 2px 8px rgba(24,95,165,.2); }
    .ag-btn--primary:hover { background:#0F4A84; color:#fff !important; transform:translateY(-1px); }
    .ag-btn--ghost { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
    .ag-btn--ghost:hover { background:#e5e7eb; color:#111827 !important; }

    .ag-label { font-size:12px; font-weight:500; color:#374151; margin-bottom:6px; display:block; }
    .ag-hint  { font-size:11.5px; color:#9ca3af; margin:6px 0 0; }

    .ag-sig-tabs { display:inline-flex; background:#eef1f4; border-radius:8px; padding:3px; }
    .ag-sig-tab { background:transparent; border:none; font-size:12px; font-weight:500; color:#6b7280; padding:5px 14px; border-radius:6px; cursor:pointer; }
    .ag-sig-tab.is-active { background:#fff; color:#111827; box-shadow:0 1px 3px rgba(0,0,0,.08); }

    .ag-note { padding:11px 15px; border-radius:10px; font-size:12.5px; line-height:1.5; }
    .ag-note--blue  { background:#E6F1FB; color:#0C447C; border:0.5px solid #B5D4F4; }
    .ag-note--amber { background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8; }
    .ag-note--green { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
    .ag-note--coral { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
</style>
