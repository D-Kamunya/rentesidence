{{--
    Reusable CS lifecycle email body. Renders a structured $m array through the shared
    CS parts so every lifecycle mail is on-brand by construction (no hand-rolled HTML).

    $m = [
        'subject'      => string,               // <title> + preheader fallback
        'preheader'    => string,               // hidden inbox preview line
        'footnote'     => string,               // footer line (optional; layout default otherwise)
        'eyebrow'      => string,               // small uppercase label above the title
        'eyebrowColor' => '#185FA5',            // CS action blue by default
        'title'        => string,               // H1
        'blocks'       => [                      // ordered body blocks:
            ['type' => 'text',   'html' => '...'],                                  // {!! !!} — escape interpolated user data
            ['type' => 'panel',  'variant' => 'blue|green|amber|red', 'title' => '...', 'rows' => [['k'=>..,'v'=>..,'mono'=>bool,'amount'=>bool]]],
            ['type' => 'button', 'url' => '...', 'label' => '...', 'color' => '#185FA5'],
            ['type' => 'note',   'text' => '...'],                                   // amber callout, {!! !!}
        ],
    ]
--}}
@extends('mail.layouts.cs')

@section('subject', $m['subject'] ?? (getOption('app_name') ?: 'Centresidence'))
@section('preheader', $m['preheader'] ?? ($m['title'] ?? ($m['subject'] ?? '')))

@section('content')
    @include('mail.parts.heading', [
        'eyebrow'      => $m['eyebrow'] ?? '',
        'eyebrowColor' => $m['eyebrowColor'] ?? '#185FA5',
        'title'        => $m['title'] ?? '',
    ])

    @foreach (($m['blocks'] ?? []) as $block)
        @php $type = $block['type'] ?? 'text'; @endphp
        @if ($type === 'text')
            @include('mail.parts.text', ['html' => $block['html'] ?? ''])
        @elseif ($type === 'panel')
            @include('mail.parts.panel', [
                'variant' => $block['variant'] ?? 'blue',
                'title'   => $block['title'] ?? null,
                'rows'    => $block['rows'] ?? [],
            ])
        @elseif ($type === 'button')
            @include('mail.parts.button', [
                'url'   => $block['url'] ?? '#',
                'label' => $block['label'] ?? '',
                'color' => $block['color'] ?? '#185FA5',
            ])
        @elseif ($type === 'note')
            @include('mail.parts.note', ['text' => $block['text'] ?? ''])
        @endif
    @endforeach
@endsection

@if (!empty($m['footnote']))
    @section('footnote', $m['footnote'])
@endif
