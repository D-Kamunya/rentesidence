@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'partners'])

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif
        @if ($errors->any()) <div class="cs-alert is-danger">{{ $errors->first() }}</div> @endif

        {{-- Register a finance partner (creates a role-6 login they sign in with) --}}
        <div class="cs-card">
            <div class="cs-card__head">
                <h2 class="cs-card__title">{{ __('Add a finance partner') }}</h2>
                <button type="button" class="cs-btn cs-btn--primary cs-btn--sm" onclick="document.getElementById('addPartner').style.display = (document.getElementById('addPartner').style.display==='none'?'block':'none')">{{ __('+ New partner') }}</button>
            </div>
            <div class="cs-card__body" id="addPartner" style="{{ $errors->any() ? '' : 'display:none;' }}">
                <form method="POST" action="{{ route('admin.centresidence.partners.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Company name') }}</label><input name="company_name" class="cs-input" value="{{ old('company_name') }}" required></div>
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Trading name') }} ({{ __('optional') }})</label><input name="trading_name" class="cs-input" value="{{ old('trading_name') }}"></div>
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Contact person') }} ({{ __('optional') }})</label><input name="contact_person" class="cs-input" value="{{ old('contact_person') }}"></div>
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Phone') }} ({{ __('optional') }})</label><input name="phone" class="cs-input" value="{{ old('phone') }}"></div>
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Login email') }}</label><input type="email" name="email" class="cs-input" value="{{ old('email') }}" required></div>
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Login password') }}</label><input type="text" name="password" class="cs-input" minlength="6" required placeholder="{{ __('Share with the partner') }}"></div>
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Origination fee %') }} ({{ __('optional') }})</label><input type="number" step="0.01" min="0" max="100" name="origination_fee_percentage" class="cs-input" value="{{ old('origination_fee_percentage') }}" placeholder="{{ __('default :d%', ['d' => config('centresidence.partner_fees.origination_percentage', 2)]) }}"></div>
                        <div class="col-md-6 cs-field"><label class="cs-label">{{ __('Servicing fee %') }} ({{ __('optional') }})</label><input type="number" step="0.01" min="0" max="100" name="servicing_fee_percentage" class="cs-input" value="{{ old('servicing_fee_percentage') }}" placeholder="{{ __('default :d%', ['d' => config('centresidence.partner_fees.servicing_percentage', 1)]) }}"></div>
                    </div>
                    <small class="cs-muted">{{ __('Creates a finance-partner account (role 6). They sign in with this email & password, then set up their own financing products.') }}</small>
                    <div style="margin-top:12px;"><button type="submit" class="cs-btn cs-btn--primary">{{ __('Create partner') }}</button></div>
                </form>
            </div>
        </div>

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Finance partners') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Company') }}</th><th>{{ __('Trading name') }}</th><th>{{ __('Contact') }}</th>
                        <th>{{ __('Products') }}</th><th>{{ __('Fees (orig / svc)') }}</th><th>{{ __('API') }}</th><th>{{ __('Status') }}</th>
                    </tr></thead>
                    <tbody>
                        @php $defOrig = config('centresidence.partner_fees.origination_percentage', 2); $defSvc = config('centresidence.partner_fees.servicing_percentage', 1); @endphp
                        @forelse ($partners as $p)
                            <tr>
                                <td style="font-weight:600;color:var(--blue);">{{ $p->company_name }}</td>
                                <td>{{ $p->trading_name ?? '—' }}</td>
                                <td>{{ $p->email ?? $p->phone ?? '—' }}</td>
                                <td>{{ $p->products_count ?? 0 }}</td>
                                <td>
                                    <span>{{ $p->origination_fee_percentage !== null ? $p->origination_fee_percentage.'%' : $defOrig.'%*' }}</span>
                                    /
                                    <span>{{ $p->servicing_fee_percentage !== null ? $p->servicing_fee_percentage.'%' : $defSvc.'%*' }}</span>
                                    <button type="button" class="cs-btn cs-btn--ghost cs-btn--sm" onclick="var f=document.getElementById('fees{{ $p->id }}');f.style.display=(f.style.display==='none'?'table-row':'none')">{{ __('edit') }}</button>
                                </td>
                                <td>{{ $p->api_enabled ? __('Enabled') : __('Manual') }}</td>
                                <td>@include('admin.centresidence._status', ['status' => $p->status])</td>
                            </tr>
                            <tr id="fees{{ $p->id }}" style="display:none;background:#F8FAFC;">
                                <td colspan="7">
                                    <form method="POST" action="{{ route('admin.centresidence.partners.fees', $p->id) }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                                        @csrf @method('PUT')
                                        <div class="cs-field" style="margin:0;"><label class="cs-label">{{ __('Origination fee %') }}</label><input type="number" step="0.01" min="0" max="100" name="origination_fee_percentage" class="cs-input" value="{{ $p->origination_fee_percentage }}" placeholder="{{ __('default :d%', ['d' => $defOrig]) }}" style="width:150px;"></div>
                                        <div class="cs-field" style="margin:0;"><label class="cs-label">{{ __('Servicing fee %') }}</label><input type="number" step="0.01" min="0" max="100" name="servicing_fee_percentage" class="cs-input" value="{{ $p->servicing_fee_percentage }}" placeholder="{{ __('default :d%', ['d' => $defSvc]) }}" style="width:150px;"></div>
                                        <button type="submit" class="cs-btn cs-btn--primary cs-btn--sm">{{ __('Save fees') }}</button>
                                        <small class="cs-muted" style="align-self:center;">{{ __('Leave blank to use the platform default (marked *).') }}</small>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cs-empty">{{ __('No finance partners yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($partners, 'links')) <div class="cs-card__body">{!! $partners->links() !!}</div> @endif
        </div>
    </div>
</div></div></div>
@endsection
