{{--
    Shared ow-* modal + form styling (buttons, modal chrome, inputs). These rules were defined
    inline on individual owner pages (billing center, tenants list, etc.); this partial lets any
    page that uses the ow-modal markup — e.g. the tenant-details Payments & Deposits tab — pick up
    the same look without duplicating the block. Include once per page that needs it.
--}}
<style>
    .ow-btn {
        display:inline-flex; align-items:center; gap:6px;
        font-size:12px; font-weight:500; padding:7px 15px;
        border-radius:7px; cursor:pointer; border:none;
        white-space:nowrap; text-decoration:none;
        transition:background .13s, transform .12s, box-shadow .12s;
    }
    .ow-btn--primary { background:#185FA5; color:#fff; box-shadow:0 2px 8px rgba(24,95,165,.2); }
    .ow-btn--primary:hover { background:#0F4A84; color:#fff; transform:translateY(-1px); box-shadow:0 4px 12px rgba(24,95,165,.3); text-decoration:none; }
    .ow-btn--purple { background:#534AB7; color:#fff; box-shadow:0 2px 8px rgba(83,74,183,.2); }
    .ow-btn--purple:hover { background:#3C3489; color:#fff; transform:translateY(-1px); text-decoration:none; }
    .ow-btn--green { background:#0F6E56; color:#fff; }
    .ow-btn--green:hover { background:#085041; color:#fff; text-decoration:none; }
    .ow-btn--ghost { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
    .ow-btn--ghost:hover { background:#e5e7eb; color:#111827; text-decoration:none; }

    .ow-modal { border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; }
    .ow-modal__header {
        display:flex; align-items:center; justify-content:space-between;
        padding:.9rem 1.25rem; border-bottom:0.5px solid #e5e7eb;
        background:#fafafa;
    }
    .ow-modal__header .modal-title { font-size:15px; font-weight:500; color:#111827; margin:0; }
    .ow-modal__close {
        display:inline-flex; align-items:center; justify-content:center;
        width:30px; height:30px; border-radius:7px; border:none;
        background:transparent; color:#6b7280; cursor:pointer;
        transition:background .13s, color .13s;
    }
    .ow-modal__close:hover { background:#f3f4f6; color:#111827; }
    .ow-modal__body { padding:1.25rem; }
    .ow-modal__footer {
        display:flex; align-items:center; gap:8px;
        padding:.9rem 1.25rem; border-top:0.5px solid #e5e7eb;
        background:#fafafa; justify-content:flex-start;
    }

    /* ── Form elements ───────────────────────────────────────── */
    .ow-form-section {
        background:#f9fafb; border:0.5px solid #e5e7eb;
        border-radius:10px; padding:1.1rem;
    }
    .ow-label {
        display:block; font-size:12px; font-weight:500;
        color:#374151; margin-bottom:5px;
    }
    .ow-label-hint { font-weight:400; color:#9ca3af; font-size:11px; }
    .ow-input {
        font-size:13px !important; border:0.5px solid #e5e7eb !important;
        border-radius:7px !important; padding:7px 10px !important;
        transition:border-color .15s, box-shadow .15s !important;
    }
    .ow-input:focus {
        border-color:#185FA5 !important;
        box-shadow:0 0 0 3px rgba(24,95,165,.1) !important;
        outline:none !important;
    }

    .ow-link-btn {
        font-size:11px; font-weight:500; color:#185FA5;
        background:none; border:none; cursor:pointer; padding:0;
        text-decoration:none; transition:color .13s;
    }
    .ow-link-btn:hover { color:#0F4A84; }

    .ow-remove-btn {
        font-size:11px; font-weight:500; color:#993C1D;
        background:none; border:none; cursor:pointer; padding:0;
        transition:color .13s;
    }
    .ow-remove-btn:hover { color:#712B13; }

    /* Status badges — shared with the Billing Center so paid/unpaid/overdue read the same everywhere. */
    .ow-badge {
        display:inline-flex; align-items:center; gap:4px;
        font-size:11px; font-weight:500; padding:3px 9px;
        border-radius:99px; white-space:nowrap;
    }
    .ow-badge--paid    { background:#E1F5EE; color:#0F6E56; }
    .ow-badge--pending { background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8; }
    .ow-badge--overdue { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
    .ow-badge--bank    { background:#EEEDFE; color:#534AB7; border:0.5px solid #CECBF6; }

    /* Status-tinted rows (matches the Billing Center) — applies wherever a DataTable sets these
       row classes on a page that loads this partial (the tenant-profile payment list). */
    tbody tr.ow-row--paid    > td { background:#F0FAF5 !important; }
    tbody tr.ow-row--overdue > td { background:#FDF3F0 !important; }
</style>
