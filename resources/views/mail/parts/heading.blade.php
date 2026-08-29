@php $eyebrowColor = $eyebrowColor ?? '#185FA5'; @endphp
@if (!empty($eyebrow))
    <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:11px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:{{ $eyebrowColor }};">{{ $eyebrow }}</div>
@endif
<h1 style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:22px; font-weight:700; color:#1F2A37; margin:8px 0 14px; line-height:1.28;">{{ $title }}</h1>
