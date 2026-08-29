{{-- Reusable avatar: shows the uploaded image when present, otherwise a clean
     initials circle (no broken placeholder box). Props: $name, $image, $avatarClass. --}}
@php
    $isPlaceholder = ! $image || \Illuminate\Support\Str::contains($image, ['no-image', 'empty-user']);
    $parts    = preg_split('/\s+/', trim((string) ($name ?: 'U')));
    $initials = \Illuminate\Support\Str::upper(
        \Illuminate\Support\Str::substr($parts[0] ?? 'U', 0, 1)
        . (count($parts) > 1 ? \Illuminate\Support\Str::substr(end($parts), 0, 1) : '')
    );
    $palette = ['#185FA5', '#1D9E75', '#854F0B', '#534AB7', '#993C1D', '#0F6E56'];
    $color   = $palette[abs(crc32((string) ($name ?: 'U'))) % count($palette)];
@endphp
@if ($isPlaceholder)
    <span class="cs-avatar {{ $avatarClass ?? '' }}" style="background: {{ $color }};" title="{{ $name }}">{{ $initials }}</span>
@else
    <img src="{{ $image }}" class="rounded-circle avatar-xs fit-image {{ $avatarClass ?? '' }}" alt="{{ $name }}">
@endif
