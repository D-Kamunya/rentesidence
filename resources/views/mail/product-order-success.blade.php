@extends('mail.layouts.cs')

@section('subject', $content['subject'])
@section('preheader', $content['title'])

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Order confirmed'), 'eyebrowColor' => '#0F6E56', 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])

    @php $items = $content['items'] ?? []; @endphp
    @if (!empty($items))
        <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#0F6E56; margin:0 0 8px;">{{ __('Your items') }}</div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#EAF7F1; border:1px solid #CDEBDD; border-radius:12px; margin:0 0 24px;">
            <tr><td style="padding:6px 18px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    @foreach ($items as $i => $it)
                        <tr>
                            <td width="52" style="padding:10px 12px 10px 0; {{ $i < count($items) - 1 ? 'border-bottom:1px solid #CDEBDD;' : '' }} vertical-align:middle;">
                                @if (!empty($it['image']))
                                    <img src="{{ $it['image'] }}" alt="" width="44" height="44" style="width:44px; height:44px; border-radius:8px; object-fit:cover; display:block; border:1px solid #CDEBDD;">
                                @else
                                    <div style="width:44px; height:44px; border-radius:8px; background:#D6EEE2; text-align:center; line-height:44px; font-size:18px; color:#0F6E56;">📦</div>
                                @endif
                            </td>
                            <td style="padding:10px 0; {{ $i < count($items) - 1 ? 'border-bottom:1px solid #CDEBDD;' : '' }} font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:14px; font-weight:600; color:#1F2A37; vertical-align:middle;">{{ $it['name'] }}</td>
                            <td style="padding:10px 0; {{ $i < count($items) - 1 ? 'border-bottom:1px solid #CDEBDD;' : '' }} font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:13px; color:#8A97A8; text-align:right; vertical-align:middle;">{{ __('Qty') }} {{ $it['qty'] ?? 1 }}</td>
                        </tr>
                    @endforeach
                </table>
            </td></tr>
        </table>
    @endif

    @include('mail.parts.panel', ['variant' => 'green', 'rows' => [
        ['k' => __('Amount paid'), 'v' => currencyPrice($content['amount']), 'amount' => true],
        ['k' => __('Method'),      'v' => !empty($content['method']) ? Str::ucfirst($content['method']) : ''],
        ['k' => __('Status'),      'v' => !empty($content['status']) ? Str::ucfirst($content['status']) : ''],
    ]])

    @include('mail.parts.button', ['url' => route('tenant.order.index'), 'label' => __('View my orders'), 'color' => '#0F6E56'])
@endsection

@section('footnote', __('You received this because you placed an order on :app.', ['app' => getOption('app_name') ?: 'Centresidence']))
