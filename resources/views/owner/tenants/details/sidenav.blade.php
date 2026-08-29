{{-- Tenant-details left rail + shared td-* styles (included by every tab view, so
     the styles ride along once per page). $tenant + $navTenant*ActiveClass come from
     the controller. --}}
<nav class="td-nav">
    <a href="{{ route('owner.tenant.details', [$tenant->id, 'tab' => 'profile']) }}"
       class="td-nav__item {{ @$navTenantProfileActiveClass ? 'is-active' : '' }}">
        <i class="ri-account-circle-line"></i><span>{{ __('Profile Information') }}</span>
    </a>
    <a href="{{ route('owner.tenant.details', [$tenant->id, 'tab' => 'home']) }}"
       class="td-nav__item {{ @$navTenantHomeActiveClass ? 'is-active' : '' }}">
        <i class="ri-home-4-line"></i><span>{{ __('Home Details') }}</span>
    </a>
    <a href="{{ route('owner.tenant.details', [$tenant->id, 'tab' => 'payment']) }}"
       class="td-nav__item {{ @$navTenantPaymentActiveClass ? 'is-active' : '' }}">
        <i class="ri-bank-card-line"></i><span>{{ __('Payment History') }}</span>
    </a>
    <a href="{{ route('owner.tenant.details', [$tenant->id, 'tab' => 'document']) }}"
       class="td-nav__item {{ @$navTenantDocumentActiveClass ? 'is-active' : '' }}">
        <i class="ri-file-text-line"></i><span>{{ __('Documents') }}</span>
    </a>
    @if ($tenant->status == TENANT_STATUS_CLOSE)
        <a href="{{ route('owner.tenant.details', [$tenant->id, 'tab' => 'closing-history']) }}"
           class="td-nav__item {{ @$navTenantClosingHistoryActiveClass ? 'is-active' : '' }}">
            <i class="ri-history-line"></i><span>{{ __('Closing History') }}</span>
        </a>
    @endif

    {{-- Optional extra rail items (e.g. the profile tab's Close-Tenant button) render here. --}}
    {{ $railExtra ?? '' }}
</nav>

<style>
    .td-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:22px; }
    .td-title { font-size:22px; font-weight:600; color:#111827; margin:0; }
    .td-crumb { display:flex; gap:6px; align-items:center; font-size:12px; color:#9ca3af; list-style:none; padding:0; margin:6px 0 0; }
    .td-crumb a { color:#185FA5; font-weight:500; text-decoration:none; }

    .td-layout { display:grid; grid-template-columns:250px 1fr; gap:20px; align-items:start; }

    /* Left rail */
    .td-rail { background:#fff; border:0.5px solid #185ea56e; border-radius:14px; padding:16px; position:sticky; top:16px;
        box-shadow:0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); }
    .td-rail__hero { text-align:center; padding:8px 4px 16px; border-bottom:0.5px solid #e5e7eb; margin-bottom:12px; }
    .td-rail__img { width:72px; height:72px; border-radius:50%; object-fit:cover; border:2px solid #E6F1FB; background:#f3f4f6; }
    .td-rail__name { font-size:15px; font-weight:600; color:#111827; margin:10px 0 2px; }
    .td-rail__meta { font-size:12px; color:#9ca3af; margin:0; }
    .td-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; margin-top:8px; }
    .td-badge--active { background:#E1F5EE; color:#0F6E56; }
    .td-badge--closed { background:#FAECE7; color:#A32D2D; }
    .td-badge--grey   { background:#f3f4f6; color:#6b7280; }

    .td-nav { display:flex; flex-direction:column; gap:2px; }
    .td-nav__item { display:flex; align-items:center; gap:10px; width:100%; text-align:left; border:none; background:transparent; cursor:pointer;
        padding:10px 12px; border-radius:9px; font-size:13px; font-weight:500; color:#374151; text-decoration:none; transition:all .13s; }
    .td-nav__item i { font-size:17px; color:#9ca3af; transition:color .13s; }
    .td-nav__item:hover { background:#f3f4f6; color:#111827; }
    .td-nav__item.is-active { background:#E6F1FB; color:#0C447C; font-weight:600; }
    .td-nav__item.is-active i { color:#185FA5; }
    .td-nav__item--danger { margin-top:8px; padding-top:12px; border-top:0.5px solid #e5e7eb; border-radius:0 0 9px 9px; color:#A32D2D; }
    .td-nav__item--danger i { color:#A32D2D; }
    .td-nav__item--danger:hover { background:#FAECE7; color:#A32D2D; }

    /* Content */
    .td-content { min-width:0; }
    .td-hero { display:flex; align-items:center; gap:16px; background:linear-gradient(135deg,#185FA5,#0F4A84); border-radius:14px; padding:20px 22px; color:#fff; margin-bottom:20px; }
    .td-hero__img { width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,.35); flex:none; }
    .td-hero__name { font-size:19px; font-weight:700; margin:0; }
    .td-hero__sub { font-size:13px; opacity:.9; margin:3px 0 0; }
    .td-hero__spacer { margin-left:auto; }
    .td-hero__edit { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.16); color:#fff; border:0.5px solid rgba(255,255,255,.28);
        font-size:12.5px; font-weight:600; padding:8px 14px; border-radius:8px; text-decoration:none; transition:background .13s; }
    .td-hero__edit:hover { background:rgba(255,255,255,.28); color:#fff; }

    .td-card { background:#fff; border:0.5px solid #185ea56e; border-radius:14px; margin-bottom:18px; overflow:hidden;
        box-shadow:0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); }
    .td-card__head { display:flex; align-items:center; gap:10px; padding:13px 18px; border-bottom:0.5px solid #e5e7eb; background:#fafafa; }
    .td-card__ic { width:32px; height:32px; border-radius:8px; flex:none; display:inline-flex; align-items:center; justify-content:center; background:#E6F1FB; color:#185FA5; font-size:16px; }
    .td-card__title { font-size:14px; font-weight:600; color:#111827; margin:0; }
    .td-card__action { margin-left:auto; font-size:12.5px; font-weight:600; color:#185FA5; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
    .td-card__body { padding:6px 18px 10px; }

    .td-info { display:grid; grid-template-columns:180px 1fr; gap:0; }
    .td-info__row { display:contents; }
    .td-info dt, .td-info__k { padding:11px 0; font-size:12.5px; color:#9ca3af; font-weight:500; border-bottom:0.5px solid #f3f4f6; }
    .td-info dd, .td-info__v { padding:11px 0; font-size:13.5px; color:#374151; margin:0; border-bottom:0.5px solid #f3f4f6; word-break:break-word; }
    .td-info > :nth-last-child(-n+2) { border-bottom:none; }

    @media (max-width: 991px) {
        .td-layout { grid-template-columns:1fr; }
        .td-rail { position:static; }
        .td-nav { flex-direction:row; flex-wrap:wrap; }
        .td-nav__item--danger { border-top:none; margin-top:0; }
    }
    @media (max-width: 560px) {
        .td-info { grid-template-columns:1fr; }
        .td-info dt, .td-info__k { padding-bottom:0; border-bottom:none; color:#6b7280; }
        .td-info dd, .td-info__v { padding-top:2px; }
    }

    /* Shared form/modal/button styles for this page's modals (pf-* names; this page
       never renders alongside the profile-settings page that also defines them). */
    .pf-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
    .pf-field { display:flex; flex-direction:column; margin-bottom:0; }
    .pf-label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:6px; }
    .pf-input { width:100%; border:0.5px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:13.5px; color:#374151; background:#fff; }
    .pf-input:focus { outline:none; border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .pf-btn { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; padding:9px 18px; border-radius:9px; border:none; cursor:pointer; text-decoration:none; transition:all .13s; }
    .pf-btn--primary { background:#185FA5; color:#fff; } .pf-btn--primary:hover { background:#0F4A84; color:#fff; }
    .pf-btn--ghost { background:#f3f4f6; color:#374151; } .pf-btn--ghost:hover { background:#e5e7eb; }
    .pf-btn--danger { background:#A32D2D; color:#fff; } .pf-btn--danger:hover { background:#872323; color:#fff; }
    .pf-modal { border:none; border-radius:14px; }
    .pf-modal__head { border-bottom:0.5px solid #e5e7eb; }
    .pf-modal__warn { font-size:13px; color:#6b7280; line-height:1.6; margin-bottom:16px; }
</style>
