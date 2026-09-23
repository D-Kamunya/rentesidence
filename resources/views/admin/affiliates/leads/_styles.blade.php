<style>
    /* ── Summary Stat Cards ──────────────────────────────── */
    .admin-stat-card { background:#fafafa; border:0.5px solid #e5e7eb; border-radius:12px; padding:1rem; height:100%; transition:box-shadow .2s, transform .2s; }
    .admin-stat-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.06); transform:translateY(-2px); }
    .admin-stat-card__icon { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; margin-bottom:10px; }
    .admin-stat-card__label { font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:4px; }
    .admin-stat-card__val { font-size:26px; font-weight:600; color:#111827; line-height:1; }

    /* ── Filters Bar ─────────────────────────────────────── */
    .admin-filters-bar { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; padding:1rem; background:#fafafa; border:0.5px solid #e5e7eb; border-radius:12px; }
    .admin-filters-bar form { flex:1; }
    .admin-filter-group { position:relative; }
    .admin-filter-input, .admin-filter-select { width:100%; padding:8px 12px; font-size:13px; color:#374151; background:#fff; border:0.5px solid #d1d5db; border-radius:8px; outline:none; transition:border-color .15s, box-shadow .15s; }
    .admin-filter-input:focus, .admin-filter-select:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .admin-filter-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:32px; cursor:pointer; }
    .admin-btn-filter { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#185FA5; color:#fff; font-size:13px; font-weight:500; border:none; border-radius:8px; cursor:pointer; white-space:nowrap; transition:background .15s, transform .15s; }
    .admin-btn-filter:hover { background:#0C447C; transform:translateY(-1px); }
    .admin-btn-clear { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#fff; color:#6b7280; font-size:13px; font-weight:500; border:0.5px solid #d1d5db; border-radius:8px; text-decoration:none; white-space:nowrap; transition:background .15s; }
    .admin-btn-clear:hover { background:#f3f4f6; color:#374151; }
    .admin-pending-badge { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#FAEEDA; color:#854F0B; font-size:13px; font-weight:500; border:0.5px solid #FAC775; border-radius:8px; text-decoration:none; white-space:nowrap; transition:background .15s; }
    .admin-pending-badge:hover { background:#fde9b8; color:#854F0B; }

    /* ── Table Card ──────────────────────────────────────── */
    .admin-table-card { border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; background:#fff; }
    .admin-th { padding:.8rem 1.1rem; font-size:11px; font-weight:500; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; border:none; white-space:nowrap; }
    .admin-td { padding:.85rem 1.1rem; border:none; vertical-align:middle; }

    /* ── Company / Affiliate Cell ────────────────────────── */
    .admin-company-cell { display:flex; align-items:center; gap:10px; }
    .admin-company-avatar { width:36px; height:36px; border-radius:8px; background:#E6F1FB; color:#185FA5; font-size:12px; font-weight:500; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .admin-company-name { font-size:14px; font-weight:500; color:#111827; }
    .admin-company-meta { font-size:12px; color:#9ca3af; }
    .admin-aff-avatar { border-radius:50%; background:#EEEDFE; color:#534AB7; }

    /* ── Lead-count pills (master list) ──────────────────── */
    .aff-counts { display:flex; flex-wrap:wrap; gap:6px; }
    .aff-count { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:500; padding:3px 9px; border-radius:99px; white-space:nowrap; }
    .aff-count b { font-weight:600; }
    .aff-count--total     { background:#f3f4f6; color:#374151; }
    .aff-count--pending   { background:#FAEEDA; color:#854F0B; }
    .aff-count--trial     { background:#EEEDFE; color:#534AB7; }
    .aff-count--converted { background:#E1F5EE; color:#0F6E56; }
    .aff-count--zero      { background:#fafafa; color:#c3c6cb; }

    /* ── Status Badges ───────────────────────────────────── */
    .admin-status-badge { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:500; padding:3px 10px; border-radius:99px; border:0.5px solid transparent; white-space:nowrap; }
    .admin-status-badge--active         { background:#E1F5EE;border-color:#9FE1CB;color:#0F6E56; }
    .admin-status-badge--demo_scheduled { background:#E6F1FB;border-color:#B5D4F4;color:#185FA5; }
    .admin-status-badge--demo_completed { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
    .admin-status-badge--converted      { background:#E1F5EE;border-color:#9FE1CB;color:#085041; }
    .admin-status-badge--rejected       { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
    .admin-status-badge--expired        { background:#f3f4f6;border-color:#e5e7eb;color:#5F5E5A; }
    .admin-status-badge--lost           { background:#FAECE7;border-color:#F5C4B3;color:#993C1D; }
    .admin-status-badge--trial          { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }

    /* ── Temperature Badges ──────────────────────────────── */
    .admin-temp-badge { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:500; padding:3px 10px; border-radius:99px; }
    .admin-temp-badge--hot    { background:#FAECE7;color:#993C1D; }
    .admin-temp-badge--warm   { background:#FAEEDA;color:#854F0B; }
    .admin-temp-badge--cold   { background:#E6F1FB;color:#185FA5; }
    .admin-temp-badge--closed { background:#f3f4f6;color:#9ca3af; }

    /* ── Expiry Badges ───────────────────────────────────── */
    .admin-expiry-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 10px; border-radius:99px; white-space:nowrap; }
    .admin-expiry-badge--active    { background:#E1F5EE;color:#0F6E56; }
    .admin-expiry-badge--warning   { background:#FAEEDA;color:#854F0B; }
    .admin-expiry-badge--expired   { background:#FCEBEB;color:#A32D2D; }
    .admin-expiry-badge--converted { background:#E1F5EE;color:#085041; }
    .admin-expiry-badge--rejected  { background:#FCEBEB;color:#A32D2D; }
    .admin-expiry-badge--lost      { background:#FAECE7;color:#993C1D; }
    .admin-expiry-badge--trial     { background:#EEEDFE;color:#534AB7; }

    /* ── Action Button ───────────────────────────────────── */
    .admin-action-btn { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:500; padding:5px 12px; border-radius:7px; border:0.5px solid #d1d5db; background:#f3f4f6; color:#5F5E5A; text-decoration:none; white-space:nowrap; transition:background .15s, color .15s, transform .15s; }
    .admin-action-btn:hover { background:#e5e7eb; color:#111827; transform:translateY(-1px); }
    .admin-action-btn--primary { background:#185FA5; border-color:#185FA5; color:#fff; }
    .admin-action-btn--primary:hover { background:#0C447C; color:#fff; }

    /* ── Back link ───────────────────────────────────────── */
    .admin-back-link { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:500; color:#6b7280; text-decoration:none; margin-bottom:14px; transition:color .15s; }
    .admin-back-link:hover { color:#185FA5; }
</style>
