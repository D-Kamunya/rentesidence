{{--
    Renders a Knowledge Base category/article icon.

    The stored value is a Remix Icon CLASS (e.g. `ri-rocket-line`) — icons.min.css
    (loaded via common/layouts/style.blade.php) provides the glyphs. Rendering it raw
    with {!! !!} printed the class name as text (the bug this fixes). A stray emoji or
    plain-text icon is tolerated and printed ESCAPED (never raw — no stored-XSS vector).

    Params: `icon` (string, required) · `size` (int px, optional, default 20).
--}}
@php $kbIcon = trim($icon ?? ''); $kbSize = (int) ($size ?? 20); @endphp
@if($kbIcon !== '')
    @if(\Illuminate\Support\Str::startsWith($kbIcon, 'ri-'))
        <i class="{{ $kbIcon }}" style="font-size: {{ $kbSize }}px; line-height: 1;"></i>
    @else
        <span style="font-size: {{ $kbSize }}px;">{{ $kbIcon }}</span>
    @endif
@endif
