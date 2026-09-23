<div class="col-md-12 col-lg-12 col-xl-4 col-xxl-3">
    <div class="cs-card stg-nav">
        <a href="{{ route('owner.setting.gateway.index') }}"
            class="stg-nav__item {{ @$subGatewaySettingActiveClass ? 'is-active' : '' }}">
            <i class="ri-bank-card-line"></i><span>{{ __('Payment Gateway') }}</span>
        </a>
        <a href="{{ route('owner.setting.expense-type.index') }}"
            class="stg-nav__item {{ @$subExpenseTypeActiveClass ? 'is-active' : '' }}">
            <i class="ri-file-list-3-line"></i><span>{{ __('Expense Type') }}</span>
        </a>
        <a href="{{ route('owner.setting.ticket-topic.index') }}"
            class="stg-nav__item {{ @$subTicketTopicActiveClass ? 'is-active' : '' }}">
            <i class="ri-price-tag-3-line"></i><span>{{ __('Tickets Topic') }}</span>
        </a>
        <a href="{{ route('owner.setting.tax-setting') }}"
            class="stg-nav__item {{ @$subTaxSettingActiveClass ? 'is-active' : '' }}">
            <i class="ri-percent-line"></i><span>{{ __('Tax Setting') }}</span>
        </a>
        <a href="{{ route('owner.setting.invoice-type.index') }}"
            class="stg-nav__item {{ @$subInvoiceTypeActiveClass ? 'is-active' : '' }}">
            <i class="ri-file-text-line"></i><span>{{ __('Invoice Type') }}</span>
        </a>
        <a href="{{ route('owner.setting.document-config.index') }}"
            class="stg-nav__item {{ @$subDocumentConfigActiveClass ? 'is-active' : '' }}">
            <i class="ri-folder-shield-2-line"></i><span>{{ __('Document Config') }}</span>
        </a>
        <a href="{{ route('owner.setting.maintenance-issue.index') }}"
            class="stg-nav__item {{ @$subMaintenanceIssueActiveClass ? 'is-active' : '' }}">
            <i class="ri-tools-line"></i><span>{{ __('Maintenance Issue') }}</span>
        </a>
    </div>
</div>

<style>
    .stg-nav { padding:8px; margin-bottom:18px; }
    .stg-nav__item { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px;
        font-size:13px; font-weight:500; color:var(--gray-700); text-decoration:none; transition:all .13s; }
    .stg-nav__item + .stg-nav__item { margin-top:2px; }
    .stg-nav__item i { font-size:17px; color:var(--gray-400); flex:none; }
    .stg-nav__item:hover { background:var(--gray-50); color:var(--gray-900); }
    .stg-nav__item:hover i { color:var(--gray-700); }
    .stg-nav__item.is-active { background:var(--blue-light); color:#0C447C; }
    .stg-nav__item.is-active i { color:var(--blue); }
</style>
