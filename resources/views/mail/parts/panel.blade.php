@php
    // $rows = [ ['k'=>Label, 'v'=>Value, 'mono'=>bool?, 'amount'=>bool?], ... ]
    // Optional $title renders a small caption above the rows.
    $variant = $variant ?? 'blue';
    $palette = [
        'blue'  => ['bg' => '#F5F8FC', 'line' => '#DCE6F1', 'amount' => '#0F2A4A', 'title' => '#1D4ED8'],
        'green' => ['bg' => '#EAF7F1', 'line' => '#CDEBDD', 'amount' => '#0F6E56', 'title' => '#0F6E56'],
        'amber' => ['bg' => '#FEF9EE', 'line' => '#FAC775', 'amount' => '#854F0B', 'title' => '#854F0B'],
        'red'   => ['bg' => '#FEF2F2', 'line' => '#FCA5A5', 'amount' => '#B42318', 'title' => '#B42318'],
    ];
    $pal         = $palette[$variant] ?? $palette['blue'];
    $bg          = $pal['bg'];
    $line        = $pal['line'];
    $amountColor = $pal['amount'];
    $titleColor  = $pal['title'];
    $font        = "-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
    $rows        = array_values(array_filter($rows ?? [], fn ($r) => isset($r['v']) && $r['v'] !== '' && $r['v'] !== null));
    $n           = count($rows);
@endphp
@if ($n)
    @if (!empty($title))
        <div style="font-family:{{ $font }}; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:{{ $titleColor }}; margin:0 0 8px;">{{ $title }}</div>
    @endif
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:{{ $bg }}; border:1px solid {{ $line }}; border-radius:12px; margin:0 0 24px;">
        <tr>
            <td style="padding:4px 18px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    @foreach ($rows as $i => $r)
                        @php $edge = $i < $n - 1 ? 'border-bottom:1px solid ' . $line . ';' : ''; @endphp
                        <tr>
                            <td style="padding:11px 0; {{ $edge }} font-family:{{ $font }}; font-size:12.5px; color:#8A97A8; vertical-align:middle;">{{ $r['k'] }}</td>
                            <td style="padding:11px 0; {{ $edge }} font-family:{{ !empty($r['mono']) ? 'Consolas,Menlo,monospace' : $font }}; text-align:right; vertical-align:middle; font-size:{{ !empty($r['amount']) ? '18px' : '14px' }}; font-weight:{{ !empty($r['amount']) ? '800' : '600' }}; color:{{ !empty($r['amount']) ? $amountColor : '#1F2A37' }}; {{ !empty($r['mono']) ? 'letter-spacing:0.02em;' : '' }}">{{ $r['v'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
@endif
