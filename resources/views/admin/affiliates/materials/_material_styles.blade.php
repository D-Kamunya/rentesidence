<style>
    /* ── Shared at- base (mirrors templates) ─────────────── */
    .at-subtitle { font-size:13px;color:#6b7280;margin:0;max-width:600px; }

    .at-btn-primary {
        display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
        background:#185FA5;color:#fff;font-size:13px;font-weight:500;
        border-radius:8px;text-decoration:none;border:none;cursor:pointer;
        transition:background .2s,transform .2s,box-shadow .2s;
    }
    .at-btn-primary:hover { background:#0C447C;color:#fff;transform:translateY(-1px);box-shadow:0 5px 14px rgba(24,95,165,.22); }
    .at-btn-ghost {
        display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
        background:#f3f4f6;color:#6b7280;font-size:13px;font-weight:500;
        border-radius:8px;border:0.5px solid #e5e7eb;cursor:pointer;text-decoration:none;transition:background .15s;
    }
    .at-btn-ghost:hover { background:#e5e7eb;color:#374151; }
    .at-btn-danger {
        display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
        background:#FCEBEB;color:#A32D2D;font-size:13px;font-weight:500;
        border-radius:8px;border:0.5px solid #F7C1C1;cursor:pointer;transition:background .15s;
    }
    .at-btn-danger:hover { background:#fad9d9; }

    .at-back-link { font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:color .15s; }
    .at-back-link:hover { color:#111827; }

    .at-alert { display:flex;align-items:flex-start;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
    .at-alert--success { background:#E1F5EE;color:#0F6E56; }
    .at-alert--danger  { background:#FCEBEB;color:#A32D2D; }

    /* ── Stats ───────────────────────────────────────────── */
    .at-stat {
        background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;
        padding:1rem;height:100%;transition:box-shadow .2s,transform .2s;
    }
    .at-stat:hover { box-shadow:0 4px 14px rgba(0,0,0,.06);transform:translateY(-2px); }
    .at-stat__icon { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;margin-bottom:10px; }
    .at-stat__label { font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:4px; }
    .at-stat__val { font-size:24px;font-weight:500;color:#111827;line-height:1; }

    /* ── Table card ──────────────────────────────────────── */
    .at-card { border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff; }
    .at-th { padding:.8rem 1.1rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;border:none;white-space:nowrap; }
    .at-td { padding:.85rem 1.1rem;border:none;vertical-align:middle; }

    /* ── Action buttons ──────────────────────────────────── */
    .at-action-btn {
        display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:500;
        padding:5px 12px;border-radius:7px;border:0.5px solid transparent;
        text-decoration:none;white-space:nowrap;cursor:pointer;background:transparent;
        transition:background .15s,transform .15s;
    }
    .at-action-btn:hover { transform:translateY(-1px); }
    .at-action-btn--edit   { background:#EEF5FD;border-color:#B5D4F4;color:#185FA5; }
    .at-action-btn--edit:hover { background:#dbeeff;color:#185FA5; }
    .at-action-btn--delete { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
    .at-action-btn--delete:hover { background:#fad9d9;color:#A32D2D; }

    /* ── Badges ──────────────────────────────────────────── */
    .at-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;white-space:nowrap; }
    .at-badge--active   { background:#E1F5EE;color:#0F6E56; }
    .at-badge--inactive { background:#f3f4f6;color:#5F5E5A; }

    /* ── Material type badges ────────────────────────────── */
    .mm-badge--pdf  { background:#FCEBEB;color:#A32D2D; }
    .mm-badge--png  { background:#E6F1FB;color:#185FA5; }
    .mm-badge--link { background:#EEEDFE;color:#534AB7; }
    .mm-badge--text { background:#f3f4f6;color:#5F5E5A; }

    /* ── Type icon in table ──────────────────────────────── */
    .mm-type-icon {
        width:34px;height:34px;border-radius:8px;
        display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .mm-type-icon--pdf  { background:#FCEBEB;color:#A32D2D; }
    .mm-type-icon--png  { background:#E6F1FB;color:#185FA5; }
    .mm-type-icon--link { background:#EEEDFE;color:#534AB7; }
    .mm-type-icon--text { background:#f3f4f6;color:#5F5E5A; }

    /* ── Priority indicator ──────────────────────────────── */
    .mm-priority {
        display:inline-flex;align-items:center;justify-content:center;
        width:24px;height:24px;border-radius:6px;font-size:12px;font-weight:500;
    }
    .mm-priority--high { background:#FCEBEB;color:#A32D2D; }
    .mm-priority--mid  { background:#FAEEDA;color:#854F0B; }
    .mm-priority--low  { background:#f3f4f6;color:#5F5E5A; }

    /* ── Link preview hover ──────────────────────────────── */
    .mm-link-preview:hover { color:#185FA5 !important;text-decoration:underline !important; }

    /* ── Form card ───────────────────────────────────────── */
    .at-form-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
    .at-form-card__head {
        display:flex;align-items:center;gap:8px;padding:.85rem 1.25rem;
        border-bottom:0.5px solid #e5e7eb;background:#fafafa;
        font-size:13px;font-weight:500;color:#374151;
    }
    .at-form-card__hint { font-size:11px;font-weight:400;color:#9ca3af;margin-left:auto; }
    .at-form-card__body { padding:1.25rem; }

    /* ── Fields ──────────────────────────────────────────── */
    .at-field { display:flex;flex-direction:column;gap:6px; }
    .at-label { font-size:12px;font-weight:500;color:#374151;text-transform:uppercase;letter-spacing:.05em; }
    .at-required { color:#A32D2D; }
    .at-field-hint { font-size:12px;color:#9ca3af;margin:0; }
    .at-field-error { font-size:12px;color:#A32D2D;margin:4px 0 0; }

    .at-input, .at-select, .at-textarea {
        width:100%;padding:9px 12px;font-size:14px;color:#111827;
        background:#fff;border:0.5px solid #d1d5db;border-radius:8px;
        outline:none;transition:border-color .15s,box-shadow .15s;appearance:none;
    }
    .at-input:focus, .at-select:focus, .at-textarea:focus {
        border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1);
    }
    .at-input--error { border-color:#F09595; }
    .at-textarea { resize:vertical;min-height:120px;line-height:1.6; }

    /* ── Type toggle buttons ─────────────────────────────── */
    .at-type-group { display:flex;gap:8px;flex-wrap:wrap; }
    .at-type-btn {
        display:inline-flex;align-items:center;gap:6px;padding:8px 14px;
        border-radius:8px;border:0.5px solid #e5e7eb;font-size:13px;font-weight:500;
        cursor:pointer;background:#fafafa;color:#6b7280;transition:background .15s,border-color .15s,color .15s;
    }
    .at-type-btn input[type="radio"] { display:none; }
    .at-type-btn--pdf:hover,   .at-type-btn--pdf.at-type-btn--active   { background:#FCEBEB;border-color:#F09595;color:#A32D2D; }
    .at-type-btn--image:hover, .at-type-btn--image.at-type-btn--active { background:#E6F1FB;border-color:#85B7EB;color:#185FA5; }
    .at-type-btn--link:hover,  .at-type-btn--link.at-type-btn--active  { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
    .at-type-btn--text:hover,  .at-type-btn--text.at-type-btn--active  { background:#f3f4f6;border-color:#d1d5db;color:#374151; }

    /* ── Toggle switch ───────────────────────────────────── */
    .mm-toggle-label { display:inline-flex;align-items:center;gap:10px;cursor:pointer;user-select:none; }
    .mm-toggle-input { display:none; }
    .mm-toggle-track {
        width:38px;height:22px;border-radius:11px;background:#d1d5db;
        position:relative;flex-shrink:0;transition:background .2s;
    }
    .mm-toggle-input:checked + .mm-toggle-track { background:#185FA5; }
    .mm-toggle-thumb {
        position:absolute;top:3px;left:3px;width:16px;height:16px;
        border-radius:50%;background:#fff;transition:left .2s;
        box-shadow:0 1px 3px rgba(0,0,0,.15);
    }
    .mm-toggle-input:checked + .mm-toggle-track .mm-toggle-thumb { left:19px; }
    .mm-toggle-text { font-size:13px;color:#374151; }

    /* ── Upload zone ─────────────────────────────────────── */
    .mm-upload-zone {
        border:1.5px dashed #d1d5db;border-radius:10px;padding:2rem 1.5rem;
        text-align:center;cursor:pointer;transition:border-color .2s,background .2s;
    }
    .mm-upload-zone:hover { border-color:#185FA5;background:#EEF5FD; }
    .mm-upload-zone__text { font-size:13px;font-weight:500;color:#374151;margin:0 0 4px; }
    .mm-upload-zone__hint { font-size:12px;color:#9ca3af;margin:0; }

    /* ── Current file (edit page) ────────────────────────── */
    .mm-current-file {
        display:flex;align-items:center;gap:12px;margin-top:12px;
        background:#fafafa;border:0.5px solid #e5e7eb;border-radius:8px;padding:10px 14px;
    }
    .mm-current-image { width:60px;height:60px;object-fit:cover;border-radius:6px;border:0.5px solid #e5e7eb; }
    .mm-current-file__info { display:flex;flex-direction:column;gap:3px; }
    .mm-current-file__name { font-size:13px;font-weight:500;color:#111827; }
    .mm-current-file__link {
        display:inline-flex;align-items:center;gap:4px;
        font-size:12px;color:#185FA5;text-decoration:none;
    }
    .mm-current-file__link:hover { text-decoration:underline; }

    /* ── Guide card ──────────────────────────────────────── */
    .at-guide-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
    .at-guide-card__head {
        display:flex;align-items:center;gap:8px;padding:.85rem 1.25rem;
        border-bottom:0.5px solid #e5e7eb;background:#fafafa;
        font-size:13px;font-weight:500;color:#374151;
    }
    .at-guide-card__body { padding:1.25rem;display:flex;flex-direction:column;gap:16px; }

    .at-guide-step { display:flex;align-items:flex-start;gap:12px; }
    .at-guide-step__num {
        width:22px;height:22px;border-radius:50%;background:#E6F1FB;color:#185FA5;
        font-size:11px;font-weight:500;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;
    }
    .mm-guide-num--pdf   { background:#FCEBEB;color:#A32D2D; }
    .mm-guide-num--image { background:#E6F1FB;color:#185FA5; }
    .mm-guide-num--link  { background:#EEEDFE;color:#534AB7; }
    .mm-guide-num--text  { background:#f3f4f6;color:#5F5E5A; }
    .at-guide-step__title { font-size:13px;font-weight:500;color:#111827;margin:0 0 3px; }
    .at-guide-step__desc  { font-size:12px;color:#6b7280;margin:0;line-height:1.6; }

    .at-guide-tip {
        display:flex;align-items:flex-start;gap:8px;
        background:#FAEEDA;border:0.5px solid #FAC775;
        border-radius:8px;padding:10px 12px;
        font-size:12px;color:#854F0B;line-height:1.6;
    }
    .at-guide-tip p { margin:0; }

    /* ── Meta rows ───────────────────────────────────────── */
    .at-meta-row { display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:0.5px solid #f3f4f6;font-size:13px; }
    .at-meta-label { color:#9ca3af;font-weight:500; }
    .at-meta-val   { color:#111827;font-weight:500; }

    /* ── Gap utility (Bootstrap gap-10 isn't a thing) ────── */
    .gap-10 { gap:10px; }
</style>