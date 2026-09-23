@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar">
        <div>
            <h1 class="cs-title">{{ $category->name }}</h1>
            <ol class="cs-crumb"><li><a href="{{ route('finance-partner.kb.index') }}">{{ __('Knowledge base') }}</a></li><li>›</li><li>{{ $category->name }}</li></ol>
        </div>
    </div>

    @if ($category->description)<p class="cs-muted" style="margin-bottom:20px;max-width:700px;">{{ $category->description }}</p>@endif

    @forelse ($articles as $a)
        @php $rt = max(1, (int) ceil(str_word_count(strip_tags($a->body)) / 200)); @endphp
        <a href="{{ route('finance-partner.kb.article', $a->slug) }}" class="cs-card" style="text-decoration:none;display:block;margin-bottom:12px;">
            <div class="cs-card__body">
                <div style="font-size:15.5px;font-weight:600;color:var(--gray-900);">{{ $a->title }}</div>
                @if ($a->excerpt)<div class="cs-muted" style="margin-top:7px;font-size:13px;line-height:1.55;">{{ $a->excerpt }}</div>@endif
                <div class="cs-author" style="margin-top:14px;">
                    <div class="cs-author__av" style="width:30px;height:30px;font-size:13px;border-radius:8px;">C</div>
                    <div class="cs-author__meta" style="color:var(--gray-500);opacity:1;">{{ __('Centresidence') }} · {{ optional($a->published_at)->format('M j, Y') }} · {{ $rt }} {{ __('min read') }}</div>
                </div>
            </div>
        </a>
    @empty
        <div class="cs-card"><div class="cs-card__body cs-empty">{{ __('No guides in this topic yet.') }}</div></div>
    @endforelse

    <div style="margin-top:16px;">{{ $articles->links() }}</div>
@endsection
