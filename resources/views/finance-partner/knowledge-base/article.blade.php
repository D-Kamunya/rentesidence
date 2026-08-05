@extends('finance-partner.layouts.app')

@php $readingTime = max(1, (int) ceil(str_word_count(strip_tags($article->body)) / 200)); @endphp

@section('content')
    <div class="cs-kbhero">
        <ol class="cs-crumb" style="margin-bottom:4px;">
            <li><a href="{{ route('finance-partner.kb.index') }}">{{ __('Knowledge base') }}</a></li><li>›</li>
            @if ($article->category)<li><a href="{{ route('finance-partner.kb.category', $article->category->slug) }}">{{ $article->category->name }}</a></li><li>›</li>@endif
            <li>{{ __('Article') }}</li>
        </ol>
        @if ($article->category)<span class="cs-chip">{{ $article->category->name }}</span>@endif
        <h1 class="cs-kbhero__title">{{ $article->title }}</h1>
        @if ($article->excerpt)<p class="cs-kbhero__excerpt">{{ $article->excerpt }}</p>@endif
        <div class="cs-author">
            <div class="cs-author__av">C</div>
            <div>
                <div class="cs-author__name">{{ __('Centresidence') }}</div>
                <div class="cs-author__meta">{{ optional($article->published_at)->format('M j, Y') }} · {{ $readingTime }} {{ __('min read') }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="cs-card"><div class="cs-card__body" style="padding:30px 32px;">
                @if ($article->type === 'video' && $article->video_url)
                    <a href="{{ $article->video_url }}" target="_blank" rel="noopener" class="cs-btn cs-btn--primary" style="margin-bottom:18px;"><i class="ri-play-circle-line"></i> {{ __('Watch video') }}</a>
                @elseif ($article->type === 'document' && $article->document_path)
                    <a href="{{ route('finance-partner.kb.document.download', $article->id) }}" class="cs-btn cs-btn--primary" style="margin-bottom:18px;"><i class="ri-download-line"></i> {{ __('Download') }} {{ $article->document_original_name }}</a>
                @elseif ($article->type === 'link' && $article->external_url)
                    <a href="{{ $article->external_url }}" target="_blank" rel="noopener" class="cs-btn cs-btn--primary" style="margin-bottom:18px;"><i class="ri-external-link-line"></i> {{ __('Open link') }}</a>
                @endif

                @if ($article->body)
                    <div class="cs-kb-body">{!! $article->body !!}</div>
                @endif

                <div style="margin-top:26px;padding-top:18px;border-top:0.5px solid var(--gray-200);">
                    <a href="{{ route('finance-partner.kb.index') }}" class="cs-btn cs-btn--ghost cs-btn--sm"><i class="ri-arrow-left-line"></i> {{ __('Back to knowledge base') }}</a>
                </div>
            </div></div>
        </div>

        <div class="col-lg-4">
            @if ($relatedArticles->isNotEmpty())
                <div class="cs-section__label">{{ __('Related') }}</div>
                <div class="cs-card"><div class="cs-card__body" style="padding:0;">
                    @foreach ($relatedArticles as $rel)
                        <a href="{{ route('finance-partner.kb.article', $rel->slug) }}" class="cs-kbitem">
                            <span style="font-size:13px;"><i class="ri-article-line" style="color:var(--gray-400);"></i> {{ $rel->title }}</span>
                        </a>
                    @endforeach
                </div></div>
            @endif
        </div>
    </div>
@endsection
