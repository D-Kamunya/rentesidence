@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'modules'])

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif

        <p class="cs-muted" style="margin-bottom:18px;">
            {{ __('Cost transparency + owner-facing copy: every chargeable module, its cost components, and the marketing content owners see.') }}
        </p>

        @forelse ($modules as $m)
            <div class="cs-card">
                <div class="cs-card__head">
                    <h2 class="cs-card__title">
                        <i class="{{ $m->displayIcon() }}" style="color:{{ $m->displayColor() }};"></i>
                        {{ $m->name }} <span class="cs-muted">({{ $m->key }})</span>
                    </h2>
                    <span style="display:flex;align-items:center;gap:10px;">
                        <span class="cs-badge {{ $m->is_metered ? 'is-blue' : 'is-grey' }}">{{ $m->is_metered ? __('Metered') : __('Non-metered') }}</span>
                        <a href="{{ route('admin.centresidence.modules.edit', $m->id) }}" class="cs-btn cs-btn--ghost cs-btn--sm">{{ __('Edit module') }}</a>
                    </span>
                </div>
                @if ($m->pricingCatalogueItems->isNotEmpty())
                    <div style="padding:12px 1.1rem 0;" class="cs-muted">
                        {{ __('Deployment cost') }}:
                        @foreach ($m->pricingCatalogueItems as $ci)
                            {{ __('hardware') }} KES {{ number_format($ci->unit_price, 2) }} + {{ __('install') }} KES {{ number_format($ci->installation_cost, 2) }}/{{ $ci->unit_label ?? __('unit') }}@if (!$loop->last); @endif
                        @endforeach
                    </div>
                @endif
                <div class="cs-tablewrap">
                    <table class="cs-table">
                        <thead><tr>
                            <th>{{ __('Component') }}</th><th>{{ __('Cost model') }}</th><th>{{ __('Rate') }}</th>
                            <th>{{ __('Gateway') }}</th><th>{{ __('Fallback eligible') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($m->costComponents as $c)
                                <tr>
                                    <td>{{ $c->component_name }}</td>
                                    <td>{{ str_replace('_', ' ', $c->cost_model) }}</td>
                                    <td class="cs-amt">KES {{ number_format($c->rate, 2) }}</td>
                                    <td>{{ $c->requires_gateway ? __('Required') : '—' }}</td>
                                    <td>
                                        <span class="cs-badge {{ $c->is_fallback_eligible ? 'is-paid' : 'is-grey' }}">
                                            {{ $c->is_fallback_eligible ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="cs-empty">{{ __('No cost components configured') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="cs-card"><div class="cs-card__body cs-empty">{{ __('No modules configured yet') }}</div></div>
        @endforelse
    </div>
</div></div></div>
@endsection
