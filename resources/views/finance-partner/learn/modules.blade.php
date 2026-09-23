@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Modules — what you can finance') }}</h1></div>

    <p class="cs-muted" style="margin-bottom:20px;max-width:720px;">
        {{ __('Centresidence modules turn everyday utilities and access into prepaid, rent-secured income for property owners. Open a module to understand the financing opportunity, how repayment is secured, and how to set up a product for it.') }}
    </p>

    @if ($modules->isEmpty())
        <div class="cs-card"><div class="cs-card__body cs-empty">{{ __('No financeable modules are published yet.') }}</div></div>
    @else
        <div class="cs-modgrid">
            @foreach ($modules as $m)
                @php $color = $m->displayColor(); @endphp
                @php $st = $m->stats; @endphp
                <a href="{{ route('finance-partner.learn.module', $m->id) }}" class="cs-modcard">
                    <div class="cs-modcard__media" style="background:linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
                        <i class="{{ $m->displayIcon() }}"></i>
                        <span class="cs-modcard__chip">{{ $m->is_metered ? __('Metered') : __('Smart') }}</span>
                    </div>
                    <div class="cs-modcard__body">
                        @if (!empty($m->leaders))
                            <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:8px;">
                                @foreach ($m->leaders as $lead)
                                    <span class="cs-badge is-blue" style="font-size:10px;"><i class="{{ $lead['icon'] }}"></i> {{ __($lead['label']) }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="cs-modcard__name">{{ $m->name }}</div>
                        <div class="cs-modcard__tag">{{ $m->tagline ?? $m->description }}</div>

                        <div class="cs-ministat">
                            <div><b>{{ $st['applications'] ?? 0 }}</b><span>{{ __('applications') }}</span></div>
                            <div><b>{{ isset($st['uptake_pct']) ? $st['uptake_pct'].'%' : '—' }}</b><span>{{ __('uptake') }}</span></div>
                            <div><b>{{ isset($st['repayment_health']) ? $st['repayment_health'].'%' : '—' }}</b><span>{{ __('repaying') }}</span></div>
                        </div>

                        <div class="cs-modcard__meta" style="margin-top:4px;">
                            @if ($m->you_finance)
                                <span class="cs-badge is-paid">{{ __('You finance this') }}</span>
                            @else
                                <span class="cs-badge is-grey">{{ __('Not offered yet') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
