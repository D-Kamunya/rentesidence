<style>
    /* ── Shared at- base ─────────────────────────────────── */
    .at-subtitle { font-size:13px;color:#6b7280;margin:0;max-width:600px; }
    .at-back-link { font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:color .15s; }
    .at-back-link:hover { color:#111827; }
    .at-alert { display:flex;align-items:flex-start;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
    .at-alert--success { background:#E1F5EE;color:#0F6E56; }

    .at-stat {
        background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;
        padding:1rem;height:100%;transition:box-shadow .2s,transform .2s;
    }
    .at-stat:hover { box-shadow:0 4px 14px rgba(0,0,0,.06);transform:translateY(-2px); }
    .at-stat__icon { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;margin-bottom:10px; }
    .at-stat__label { font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:4px; }
    .at-stat__val { font-size:24px;font-weight:500;color:#111827;line-height:1; }

    .at-card { border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff; }
    .at-th { padding:.8rem 1.1rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;border:none;white-space:nowrap; }
    .at-td { padding:.85rem 1.1rem;border:none;vertical-align:middle; }

    .at-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;white-space:nowrap; }
    .at-badge--active   { background:#E1F5EE;color:#0F6E56; }
    .at-badge--whatsapp { background:#E1F5EE;color:#0F6E56; }
    .at-badge--email    { background:#E6F1FB;color:#185FA5; }
    .at-badge--call     { background:#EEEDFE;color:#534AB7; }

    .at-action-btn {
        display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:500;
        padding:5px 12px;border-radius:7px;border:0.5px solid transparent;
        text-decoration:none;white-space:nowrap;cursor:pointer;background:transparent;
        transition:background .15s,transform .15s;
    }
    .at-action-btn:hover { transform:translateY(-1px); }
    .at-action-btn--view   { background:#EEF5FD;border-color:#B5D4F4;color:#185FA5; }
    .at-action-btn--view:hover { background:#dbeeff;color:#185FA5; }
    .at-action-btn--delete { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
    .at-action-btn--delete:hover { background:#fad9d9;color:#A32D2D; }

    /* ── Generate button ─────────────────────────────────── */
    .sg-generate-btn {
        display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
        background:#111827;color:#fff;font-size:13px;font-weight:500;
        border-radius:8px;border:none;cursor:pointer;
        transition:background .2s,transform .2s,box-shadow .2s;
    }
    .sg-generate-btn:hover { background:#374151;transform:translateY(-1px);box-shadow:0 5px 14px rgba(0,0,0,.2); }

    /* ── Urgent row tint ─────────────────────────────────── */
    .sg-row--urgent { background:#FDF9F8; }
    .sg-row--urgent:hover { background:#FDF4F1 !important; }

    /* ── Priority badges ─────────────────────────────────── */
    .sg-priority { display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:500;padding:3px 8px;border-radius:99px;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap; }
    .sg-priority--high   { background:#FCEBEB;color:#A32D2D; }
    .sg-priority--medium { background:#FAEEDA;color:#854F0B; }
    .sg-priority--low    { background:#E6F1FB;color:#185FA5; }

    /* ── Lead status mini badges ─────────────────────────── */
    .sg-lead-status { display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:500;padding:2px 8px;border-radius:99px;white-space:nowrap; }
    .sg-lead-status--active             { background:#E1F5EE;color:#0F6E56; }
    .sg-lead-status--demo_scheduled     { background:#E6F1FB;color:#185FA5; }
    .sg-lead-status--demo_completed     { background:#EEEDFE;color:#534AB7; }
    .sg-lead-status--pending_conversion { background:#EEEDFE;color:#3C3489; }
    .sg-lead-status--trial              { background:#EEEDFE;color:#534AB7; }
    .sg-lead-status--converted          { background:#E1F5EE;color:#085041; }
    .sg-lead-status--rejected           { background:#FCEBEB;color:#A32D2D; }
    .sg-lead-status--expired            { background:#f3f4f6;color:#5F5E5A; }
    .sg-lead-status--lost               { background:#FAECE7;color:#993C1D; }

    /* ── Temperature badges ──────────────────────────────── */
    .sg-temp-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;white-space:nowrap; }
    .sg-temp-badge--hot  { background:#FAECE7;color:#993C1D; }
    .sg-temp-badge--warm { background:#FAEEDA;color:#854F0B; }
    .sg-temp-badge--cold { background:#E6F1FB;color:#185FA5; }

    /* ── Avatar ──────────────────────────────────────────── */
    .sg-avatar {
        width:36px;height:36px;border-radius:9px;background:#E6F1FB;color:#185FA5;
        font-size:12px;font-weight:500;display:inline-flex;align-items:center;
        justify-content:center;flex-shrink:0;
    }
    .sg-avatar--sm { width:30px;height:30px;border-radius:7px;font-size:11px; }

    /* ── Hero card (lead detail) ─────────────────────────── */
    .sg-hero {
        background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;
        padding:1.25rem 1.5rem;display:flex;align-items:flex-start;
        justify-content:space-between;flex-wrap:wrap;gap:1rem;
    }
    .sg-hero__left { display:flex;align-items:flex-start;gap:14px; }
    .sg-company-avatar {
        width:48px;height:48px;border-radius:12px;background:#E6F1FB;color:#185FA5;
        font-size:15px;font-weight:500;display:inline-flex;align-items:center;
        justify-content:center;flex-shrink:0;
    }
    .sg-company-name { font-size:17px;font-weight:500;color:#111827;margin:0 0 2px; }
    .sg-company-meta { font-size:13px;color:#9ca3af;margin:0; }

    .sg-affiliate-chip {
        display:flex;align-items:center;gap:10px;
        background:#fafafa;border:0.5px solid #e5e7eb;border-radius:10px;
        padding:.65rem 1rem;
    }

    /* ── Summary strip ───────────────────────────────────── */
    .sg-summary-strip {
        display:flex;align-items:center;
        background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;
        padding:.75rem 1.5rem;gap:0;flex-wrap:wrap;
    }
    .sg-summary-item { flex:1;text-align:center;padding:.5rem 1rem; }
    .sg-summary-val { font-size:22px;font-weight:500;color:#111827;line-height:1; }
    .sg-summary-label { font-size:11px;color:#9ca3af;font-weight:500;text-transform:uppercase;letter-spacing:.05em;margin-top:4px; }
    .sg-summary-divider { width:0.5px;background:#e5e7eb;align-self:stretch;margin:.5rem 0; }

    /* ── Section cards ───────────────────────────────────── */
    .sg-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
    .sg-card__head {
        display:flex;align-items:center;gap:8px;padding:.8rem 1.1rem;
        border-bottom:0.5px solid #e5e7eb;background:#fafafa;
        font-size:13px;font-weight:500;color:#374151;
    }
    .sg-card__body { padding:1.1rem; }
    .sg-head-count {
        margin-left:auto;font-size:10px;font-weight:500;padding:2px 8px;
        border-radius:99px;background:#FAEEDA;color:#854F0B;text-transform:uppercase;letter-spacing:.5px;
    }

    /* ── Suggestion blocks ───────────────────────────────── */
    .sg-suggestion-block {
        background:#fafafa;border:0.5px solid #e5e7eb;border-radius:10px;
        padding:1rem;margin-bottom:12px;transition:box-shadow .2s,border-color .2s;
    }
    .sg-suggestion-block:last-child { margin-bottom:0; }
    .sg-suggestion-block:hover { box-shadow:0 2px 8px rgba(0,0,0,.06);border-color:#d1d5db; }
    .sg-suggestion-block--urgent { background:#FDF4F1;border-color:#F5C4B3; }

    .sg-suggestion-msg { font-size:14px;font-weight:500;color:#111827;line-height:1.5;margin:0 0 8px; }
    .sg-suggestion-meta { display:flex;align-items:center;gap:12px;font-size:11px;color:#9ca3af; }

    .sg-executed-info {
        display:flex;align-items:center;gap:8px;margin-top:10px;
        background:#E1F5EE;border:0.5px solid #9FE1CB;border-radius:8px;
        padding:8px 12px;font-size:12px;color:#0F6E56;
    }

    /* ── Linked activities inside suggestion ─────────────── */
    .sg-activities { margin-top:12px;padding-top:12px;border-top:0.5px solid #e5e7eb; }
    .sg-activities__label { font-size:11px;font-weight:500;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin:0 0 8px; }
    .sg-activity-item { display:flex;gap:10px;padding-bottom:8px; }
    .sg-activity-item:last-child { padding-bottom:0; }
    .sg-activity-dot { width:8px;height:8px;border-radius:50%;background:#185FA5;border:2px solid #E6F1FB;flex-shrink:0;margin-top:4px; }
    .sg-activity-content { flex:1; }
    .sg-activity-type { font-size:12px;font-weight:500;color:#374151; }
    .sg-activity-time { font-size:11px;color:#9ca3af; }
    .sg-activity-desc { font-size:12px;color:#6b7280;margin:3px 0 0;line-height:1.5; }

    /* ── Lead detail rows ────────────────────────────────── */
    .sg-detail-row { display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:0.5px solid #f3f4f6;font-size:13px; }
    .sg-detail-label { color:#9ca3af;font-weight:500; }
    .sg-detail-val   { color:#111827;font-weight:500;text-align:right; }

    /* ── Activity timeline (right col) ──────────────────── */
    .sg-timeline-item { display:flex;gap:12px;padding-bottom:1rem;position:relative; }
    .sg-timeline-item:last-child { padding-bottom:0; }
    .sg-timeline-item:not(:last-child)::before { content:'';position:absolute;left:4px;top:14px;bottom:0;width:0.5px;background:#e5e7eb; }
    .sg-timeline-dot { width:9px;height:9px;border-radius:50%;background:#185FA5;border:2px solid #E6F1FB;flex-shrink:0;margin-top:4px; }
    .sg-timeline-content { flex:1; }
    .sg-timeline-time { font-size:11px;color:#9ca3af;font-weight:500;margin:0 0 2px; }
    .sg-timeline-desc { font-size:12px;color:#374151;margin:3px 0 0;line-height:1.5; }
</style>