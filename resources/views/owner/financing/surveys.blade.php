@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('Site surveys') }}</h1>
                <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li><a href="{{ route('owner.financing.index') }}">{{ __('Financing') }}</a></li><li>›</li><li>{{ __('Site surveys') }}</li></ol>
            </div>
        </div>

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <p class="cs-muted" style="max-width:640px;margin:0 0 20px;">{{ __('Some installations — like reticulated gas — are priced per property, so they need a site survey before a quotation. Request one below; our team assesses the property and sends you a quote you can take into financing.') }}</p>

        {{-- Request a survey --}}
        @if ($modules->isEmpty())
            <div class="cs-alert is-info">{{ __('No survey-based modules are available to request right now.') }}</div>
        @else
            <div class="cs-card" style="margin-bottom:24px;"><div class="cs-card__body">
                <h2 class="cs-card__title" style="margin-bottom:14px;">{{ __('Request a site survey') }}</h2>
                <form method="POST" action="{{ route('owner.financing.surveys.request') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 cs-field">
                            <label class="cs-label">{{ __('Installation') }}</label>
                            <select name="module_id" class="cs-input" required>
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($modules as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 cs-field">
                            <label class="cs-label">{{ __('Property') }}</label>
                            <select name="property_id" class="cs-input" required>
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($properties as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 cs-field">
                            <label class="cs-label">{{ __('Note (optional)') }}</label>
                            <input type="text" name="note" class="cs-input" maxlength="1000" placeholder="{{ __('e.g. number of units, access notes') }}">
                        </div>
                    </div>
                    @if ($properties->isEmpty())
                        <p class="cs-muted" style="margin:4px 0 12px;">{{ __('Add a property first to request a survey.') }}</p>
                    @endif
                    <button type="submit" class="cs-btn cs-btn--primary" @if ($properties->isEmpty()) disabled @endif>{{ __('Request survey') }}</button>
                </form>
            </div></div>
        @endif

        {{-- Existing requests --}}
        <h2 class="cs-card__title" style="margin:0 0 12px;">{{ __('Your requests') }}</h2>
        @if ($requests->isEmpty())
            <div class="cs-alert is-info">{{ __('You have not requested any site surveys yet.') }}</div>
        @else
            <div class="table-responsive">
                <table class="table cs-table">
                    <thead><tr>
                        <th>{{ __('Installation') }}</th><th>{{ __('Property') }}</th><th>{{ __('Status') }}</th><th>{{ __('Quote') }}</th><th></th>
                    </tr></thead>
                    <tbody>
                        @foreach ($requests as $r)
                            @php
                                $map = [
                                    'requested' => ['#8a6a1e', '#FBF3E4', __('Requested')],
                                    'surveyed'  => ['#12448f', '#E7EFFF', __('Surveyed')],
                                    'quoted'    => ['#0F6E56', '#EAF7F1', __('Quoted')],
                                    'applied'   => ['#0F2A4A', '#E6EEF8', __('In financing')],
                                    'cancelled' => ['#8A97A8', '#EEF1F5', __('Cancelled')],
                                ];
                                $pill = $map[$r->status] ?? ['#8A97A8', '#EEF1F5', ucfirst($r->status)];
                            @endphp
                            <tr>
                                <td>{{ optional($r->module)->name }}</td>
                                <td>{{ optional($r->property)->name ?? '—' }}</td>
                                <td><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;color:{{ $pill[0] }};background:{{ $pill[1] }};">{{ $pill[2] }}</span></td>
                                <td>{{ $r->quoted_amount !== null ? 'KES ' . number_format((float) $r->quoted_amount, 2) : '—' }}
                                    @if ($r->quote_note)<br><small class="cs-muted">{{ $r->quote_note }}</small>@endif
                                </td>
                                <td style="text-align:right;">
                                    @if ($r->isQuoted())
                                        <form method="POST" action="{{ route('owner.financing.surveys.proceed', $r->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="cs-btn cs-btn--primary cs-btn--sm">{{ __('Accept & finance') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div></div></div>
@endsection
