@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Search') }}</h1></div>

    <form method="GET" action="{{ route('finance-partner.kb.search') }}" style="margin-bottom:18px;max-width:520px;">
        <input type="text" name="q" class="cs-input" placeholder="{{ __('Search guides…') }}" value="{{ $search }}">
    </form>

    <p class="cs-muted" style="margin-bottom:14px;">{{ $articles->total() }} {{ trans_choice('result|results', $articles->total()) }} {{ __('for') }} “{{ $search }}”</p>

    @forelse ($articles as $a)
        @php $rt = max(1, (int) ceil(str_word_count(strip_tags($a->body)) / 200)); @endphp
        <a href="{{ route('finance-partner.kb.article', $a->slug) }}" class="cs-card" style="text-decoration:none;display:block;margin-bottom:12px;">
            <div class="cs-card__body">
                <div style="font-size:15.5px;font-weight:600;color:var(--gray-900);">{{ $a->title }}</div>
                @if ($a->excerpt)<div class="cs-muted" style="margin-top:7px;font-size:13px;line-height:1.55;">{{ $a->excerpt }}</div>@endif
                <div class="cs-author" style="margin-top:14px;">
                    <div class="cs-author__av" style="width:30px;height:30px;font-size:13px;border-radius:8px;">C</div>
                    <div class="cs-author__meta" style="color:var(--gray-500);opacity:1;">{{ __('Centresidence') }} · {{ optional($a->category)->name }} · {{ $rt }} {{ __('min read') }}</div>
                </div>
            </div>
        </a>
    @empty
        <div class="cs-card"><div class="cs-card__body cs-empty">{{ __('No matching guides.') }}</div></div>
    @endforelse

    <div style="margin-top:16px;">{{ $articles->appends(['q' => $search])->links() }}</div>
@endsection
