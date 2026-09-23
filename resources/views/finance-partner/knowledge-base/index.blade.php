@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-kbhero">
        <h1 class="cs-kbhero__title" style="margin-top:0;">{{ __('Partner knowledge base') }}</h1>
        <p class="cs-kbhero__excerpt" style="margin-bottom:18px;">{{ __('Everything you need to finance with confidence — what the modules are, how facilities work, interest, repayment and settlement.') }}</p>
        <form method="GET" action="{{ route('finance-partner.kb.search') }}" style="max-width:480px;">
            <input type="text" name="q" placeholder="{{ __('Search guides…') }}" value="{{ request('q') }}"
                   style="width:100%;border:none;border-radius:9px;padding:11px 14px;font-size:14px;color:var(--gray-800);">
        </form>
    </div>

    @if ($categories->isEmpty() && $recentArticles->isEmpty())
        <div class="cs-card"><div class="cs-card__body cs-empty">{{ __('No partner guides published yet.') }}</div></div>
    @else
        @if ($categories->isNotEmpty())
            <div class="cs-section__label">{{ __('Browse by topic') }}</div>
            <div class="cs-modgrid" style="margin-bottom:26px;">
                @foreach ($categories as $cat)
                    <a href="{{ route('finance-partner.kb.category', $cat->slug) }}" class="cs-topic">
                        <div class="cs-topic__ic"><i class="{{ $cat->icon ?: 'ri-folder-3-line' }}"></i></div>
                        <div style="font-size:15px;font-weight:600;color:var(--gray-900);">{{ $cat->name }}</div>
                        @if ($cat->description)<div class="cs-muted" style="margin-top:5px;">{{ $cat->description }}</div>@endif
                        <div class="cs-muted" style="margin-top:10px;font-size:11.5px;">{{ $cat->articles_count }} {{ trans_choice('guide|guides', $cat->articles_count) }} →</div>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($recentArticles->isNotEmpty())
            <div class="cs-section__label">{{ __('Latest guides') }}</div>
            <div class="cs-card"><div class="cs-card__body" style="padding:0;">
                @foreach ($recentArticles as $a)
                    <a href="{{ route('finance-partner.kb.article', $a->slug) }}" class="cs-kbitem">
                        <span><i class="ri-article-line" style="color:var(--gray-400);margin-right:6px;"></i>{{ $a->title }}</span>
                        <span class="cs-muted" style="white-space:nowrap;">{{ optional($a->category)->name }}</span>
                    </a>
                @endforeach
            </div></div>
        @endif
    @endif
@endsection
