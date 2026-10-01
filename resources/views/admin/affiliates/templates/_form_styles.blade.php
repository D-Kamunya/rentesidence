<style>
    /* ── Back link ───────────────────────────────────────── */
    .at-back-link { font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:color .15s; }
    .at-back-link:hover { color:#111827; }

    /* ── Alerts ──────────────────────────────────────────── */
    .at-alert { display:flex;align-items:flex-start;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
    .at-alert--success { background:#E1F5EE;color:#0F6E56; }
    .at-alert--danger  { background:#FCEBEB;color:#A32D2D; }

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
    .at-textarea { resize:vertical;min-height:140px;line-height:1.6; }
    .at-select { background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6l4 4 4-4' stroke='%239ca3af' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;padding-right:32px; }

    .at-code { font-family:monospace;font-size:12px;background:#f3f4f6;border:0.5px solid #e5e7eb;border-radius:4px;padding:1px 5px;color:#374151; }

    /* ── Type toggle buttons ─────────────────────────────── */
    .at-type-group { display:flex;gap:8px;flex-wrap:wrap; }
    .at-type-btn {
        display:inline-flex;align-items:center;gap:6px;padding:8px 14px;
        border-radius:8px;border:0.5px solid #e5e7eb;font-size:13px;font-weight:500;
        cursor:pointer;background:#fafafa;color:#6b7280;transition:background .15s,border-color .15s,color .15s;
    }
    .at-type-btn input[type="radio"] { display:none; }
    .at-type-btn--whatsapp:hover, .at-type-btn--whatsapp.at-type-btn--active { background:#E1F5EE;border-color:#5DCAA5;color:#0F6E56; }
    .at-type-btn--email:hover,    .at-type-btn--email.at-type-btn--active    { background:#E6F1FB;border-color:#85B7EB;color:#185FA5; }
    .at-type-btn--call:hover,     .at-type-btn--call.at-type-btn--active     { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }

    /* ── Materials grid ──────────────────────────────────── */
    .at-materials-grid { display:flex;flex-direction:column;gap:8px; }
    .at-material-item {
        display:flex;align-items:center;gap:10px;padding:9px 12px;
        border:0.5px solid #e5e7eb;border-radius:8px;cursor:pointer;
        font-size:13px;color:#374151;background:#fafafa;
        transition:background .15s,border-color .15s;
    }
    .at-material-item:has(input:checked) { background:#EEF5FD;border-color:#B5D4F4;color:#185FA5; }
    .at-material-item input[type="checkbox"] { accent-color:#185FA5;width:15px;height:15px;flex-shrink:0; }
    .at-material-info { display:flex;align-items:center;gap:8px; }

    /* ── Buttons ─────────────────────────────────────────── */
    .at-btn-primary {
        display:inline-flex;align-items:center;gap:7px;padding:9px 20px;
        background:#185FA5;color:#fff;font-size:13px;font-weight:500;
        border-radius:8px;border:none;cursor:pointer;text-decoration:none;
        transition:background .2s,transform .2s,box-shadow .2s;
    }
    .at-btn-primary:hover { background:#0C447C;color:#fff;transform:translateY(-1px);box-shadow:0 5px 14px rgba(24,95,165,.22); }

    .at-btn-ghost {
        display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
        background:#f3f4f6;color:#6b7280;font-size:13px;font-weight:500;
        border-radius:8px;border:0.5px solid #e5e7eb;cursor:pointer;text-decoration:none;
        transition:background .15s;
    }
    .at-btn-ghost:hover { background:#e5e7eb;color:#374151; }

    .at-btn-danger {
        display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
        background:#FCEBEB;color:#A32D2D;font-size:13px;font-weight:500;
        border-radius:8px;border:0.5px solid #F7C1C1;cursor:pointer;
        transition:background .15s;
    }
    .at-btn-danger:hover { background:#fad9d9; }

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
    .at-guide-step__title { font-size:13px;font-weight:500;color:#111827;margin:0 0 3px; }
    .at-guide-step__desc  { font-size:12px;color:#6b7280;margin:0;line-height:1.6; }

    .at-guide-tip {
        display:flex;align-items:flex-start;gap:8px;
        background:#FAEEDA;border:0.5px solid #FAC775;
        border-radius:8px;padding:10px 12px;
        font-size:12px;color:#854F0B;line-height:1.6;
    }
    .at-guide-tip p { margin:0; }

    /* ── Meta rows (edit page) ───────────────────────────── */
    .at-meta-row { display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:0.5px solid #f3f4f6;font-size:13px; }
    .at-meta-label { color:#9ca3af;font-weight:500; }
    .at-meta-val   { color:#111827;font-weight:500; }
    /* ── Placeholder reference ───────────────────────────── */
    .at-placeholder-ref {
        border: 0.5px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .at-placeholder-ref__toggle {
        display: flex;
        align-items: center;
        gap: 7px;
        width: 100%;
        padding: 9px 12px;
        background: #fafafa;
        border: none;
        font-size: 12px;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        text-align: left;
        transition: background .15s;
    }
    .at-placeholder-ref__toggle:hover { background: #f3f4f6; }
    .at-placeholder-ref__body { padding: 12px; border-top: 0.5px solid #e5e7eb; }
    .at-placeholder-group { margin-bottom: 12px; }
    .at-placeholder-group:last-child { margin-bottom: 0; }
    .at-placeholder-group__label {
        font-size: 11px;
        font-weight: 500;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin: 0 0 7px;
    }
    .at-placeholder-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .at-placeholder-chip {
        font-size: 11px;
        font-weight: 500;
        font-family: monospace;
        background: #f3f4f6;
        border: 0.5px solid #e5e7eb;
        color: #374151;
        border-radius: 5px;
        padding: 4px 9px;
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s;
    }
    .at-placeholder-chip:hover {
        background: #E6F1FB;
        border-color: #B5D4F4;
        color: #185FA5;
    }
    .at-placeholder-ref__hint {
        font-size: 11px;
        color: #9ca3af;
        margin: 10px 0 0;
    }
</style>