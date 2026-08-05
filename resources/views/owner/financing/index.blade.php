@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')

        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('Grow your property cashflow') }}</h1>
                <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ __('Financing') }}</li></ol>
            </div>
            <a href="{{ route('owner.financing.mine') }}" class="cs-btn cs-btn--ghost">{{ __('My Financing') }}</a>
        </div>

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <p class="cs-muted" style="margin-bottom:20px;max-width:680px;">
            {{ __('Smart modules turn everyday utilities into reliable, prepaid income. Explore a module to see how it works and how it lifts your cashflow — then finance it through a partner or fund it yourself.') }}
        </p>

        @if ($modules->isEmpty())
            <div class="cs-card"><div class="cs-card__body cs-empty">{{ __('No modules are available right now. Check back soon.') }}</div></div>
        @else
            <div class="cs-modgrid">
                @foreach ($modules as $row)
                    @php $m = $row['module']; $color = $m->displayColor(); @endphp
                    <a href="{{ route('owner.financing.module', $m->id) }}" class="cs-modcard">
                        <div class="cs-modcard__media" style="background:linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
                            <i class="{{ $m->displayIcon() }}"></i>
                            <span class="cs-modcard__chip">{{ $m->is_metered ? __('Metered') : __('Smart') }}</span>
                        </div>
                        <div class="cs-modcard__body">
                            <div class="cs-modcard__name">{{ $m->name }}</div>
                            <div class="cs-modcard__tag">{{ $m->tagline ?? $m->description }}</div>
                            <div class="cs-modcard__meta">
                                <span>
                                    @if ($row['financiers'] > 0)
                                        {{ $row['financiers'] }} {{ trans_choice('financier|financiers', $row['financiers']) }}
                                    @elseif ($row['catalogue'])
                                        {{ __('Self-finance') }}
                                    @else
                                        {{ __('Coming soon') }}
                                    @endif
                                </span>
                                <span class="cs-modcard__cta" style="color:{{ $color }};">{{ __('Explore') }} <i class="ri-arrow-right-line"></i></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div></div></div>
@endsection
