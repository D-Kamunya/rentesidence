<style>
    .sup-wrap { }
    .sup-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
    .sup-head h1 { font-size:22px; font-weight:700; color:#1b1e22; margin:0; }
    .sup-head p { font-size:13.5px; color:#6b7280; margin:3px 0 0; }
    .sup-btn { display:inline-flex; align-items:center; gap:7px; border:none; border-radius:10px; padding:10px 16px;
        font-size:13.5px; font-weight:650; cursor:pointer; text-decoration:none; transition:.15s; }
    .sup-btn--primary { background:#185FA5; color:#fff !important; box-shadow:0 6px 16px -8px rgba(24,95,165,.7); }
    .sup-btn--primary:hover { background:#0F4A84; color:#fff !important; transform:translateY(-1px); }
    .sup-btn--ghost { background:#fff; border:1px solid #e6e1d8; color:#4a4f57 !important; }
    .sup-btn--ghost:hover { border-color:#185FA5; color:#185FA5 !important; }

    /* Ticket list */
    .sup-list { display:flex; flex-direction:column; gap:10px; }
    .sup-item { display:flex; align-items:center; gap:14px; background:#fff; border:1px solid #ececec; border-radius:14px;
        padding:15px 18px; text-decoration:none; transition:.15s; box-shadow:0 1px 2px rgba(16,24,40,.03); }
    .sup-item:hover { border-color:#cbddf1; box-shadow:0 6px 18px rgba(24,95,165,.07); transform:translateY(-1px); }
    .sup-item__body { flex:1; min-width:0; }
    .sup-item__subject { font-size:14.5px; font-weight:650; color:#1b1e22; display:flex; align-items:center; gap:8px; }
    .sup-item__meta { font-size:12px; color:#9aa2ad; margin-top:3px; }
    .sup-unread-dot { width:8px; height:8px; border-radius:50%; background:#185FA5; flex:none; box-shadow:0 0 0 3px rgba(24,95,165,.15); }
    .sup-item__go { color:#c3c6cb; flex:none; }

    /* Status pill */
    .sup-pill { display:inline-block; font-size:11px; font-weight:650; padding:3px 10px; border-radius:999px; white-space:nowrap; text-transform:capitalize; }
    .sup-pill--open     { background:#FEF3E7; color:#B45309; }
    .sup-pill--answered { background:#E6F1FB; color:#185FA5; }
    .sup-pill--resolved { background:#E1F5EE; color:#0F6E56; }
    .sup-pill--closed   { background:#f3f4f6; color:#6b7280; }

    .sup-empty { padding:40px 20px; text-align:center; color:#9aa2ad; font-size:14px; background:#fff; border:1px solid #ececec; border-radius:14px; }

    /* Thread */
    .sup-card { background:#fff; border:1px solid #ececec; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(16,24,40,.04); }
    .sup-thread { padding:20px; display:flex; flex-direction:column; gap:16px; max-height:60vh; overflow-y:auto; }
    .sup-msg { display:flex; gap:11px; max-width:82%; }
    .sup-msg--mine { align-self:flex-end; flex-direction:row-reverse; }
    .sup-msg__av { width:34px; height:34px; border-radius:50%; flex:none; display:grid; place-items:center; font-size:12px; font-weight:700; }
    .sup-msg__av--admin { background:#E6F1FB; color:#185FA5; }
    .sup-msg__av--user  { background:#EEEDFE; color:#534AB7; }
    .sup-msg__bubble { background:#f6f7f9; border:1px solid #eef0f3; border-radius:14px; padding:11px 14px; }
    .sup-msg--mine .sup-msg__bubble { background:#EAF2FB; border-color:#d3e4f6; }
    .sup-msg__who { font-size:11.5px; font-weight:650; color:#4a4f57; margin-bottom:3px; }
    .sup-msg__text { font-size:14px; color:#1b1e22; line-height:1.55; white-space:pre-wrap; word-break:break-word; }
    .sup-msg__time { font-size:11px; color:#9aa2ad; margin-top:4px; }

    .sup-reply { border-top:1px solid #eef0f3; padding:16px 20px; }
    .sup-reply textarea { width:100%; border:1px solid #e6e1d8; border-radius:12px; padding:12px 14px; font-size:14px; color:#1b1e22; resize:vertical; min-height:80px; outline:none; }
    .sup-reply textarea:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.12); }
    .sup-reply__row { display:flex; justify-content:flex-end; gap:8px; margin-top:12px; align-items:center; flex-wrap:wrap; }
    .sup-closed-note { padding:16px 20px; border-top:1px solid #eef0f3; font-size:13px; color:#9aa2ad; text-align:center; }

    .sup-flash { border-radius:10px; padding:11px 14px; font-size:13.5px; margin-bottom:16px; }
    .sup-flash--ok { background:#E1F5EE; border:1px solid #9ad9c4; color:#0F6E56; }
    .sup-flash--err { background:#FBE9E7; border:1px solid #f0b8b0; color:#B42318; }

    /* Modal (new ticket) */
    .sup-modal { display:none; position:fixed; inset:0; z-index:1050; background:rgba(20,23,28,.5); align-items:center; justify-content:center; padding:20px; }
    .sup-modal.is-open { display:flex; }
    .sup-modal__card { background:#fff; border-radius:16px; max-width:480px; width:100%; padding:24px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
    .sup-modal__head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
    .sup-modal__head h3 { margin:0; font-size:17px; font-weight:700; color:#1b1e22; }
    .sup-modal__x { border:none; background:transparent; font-size:22px; line-height:1; color:#9aa2ad; cursor:pointer; }
    .sup-field { margin-bottom:13px; }
    .sup-field label { display:block; font-size:12.5px; font-weight:600; color:#4a4f57; margin-bottom:5px; }
    .sup-field input, .sup-field textarea, .sup-field select { width:100%; border:1px solid #e6e1d8; border-radius:10px; padding:10px 12px; font-size:14px; color:#1b1e22; outline:none; }
    .sup-field input:focus, .sup-field textarea:focus, .sup-field select:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.12); }
    .sup-field .err { color:#B42318; font-size:12px; margin-top:5px; }
</style>
