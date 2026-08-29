{{-- Shared server-side report PDF (dompdf). Renders any report as a full-width,
     landscape table so NO rows are dropped (server-side export = the complete
     dataset) and NO columns are clipped (widths distribute + text wraps). --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22px 20px 40px 20px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 9px; }

        .rpt-head { width: 100%; border-bottom: 1.5px solid #185FA5; padding-bottom: 8px; margin-bottom: 12px; }
        .rpt-head td { vertical-align: middle; }
        .rpt-logo { width: 54px; }
        .rpt-title { font-size: 15px; font-weight: bold; color: #111827; }
        .rpt-sub { font-size: 10px; color: #6b7280; }
        .rpt-meta { text-align: right; font-size: 9px; color: #6b7280; }

        table.rpt { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.rpt thead th {
            background: #185FA5; color: #fff; font-size: 9px; font-weight: bold;
            text-align: left; padding: 6px 6px; border: 0.5px solid #185FA5;
            word-wrap: break-word;
        }
        table.rpt tbody td {
            padding: 5px 6px; border: 0.5px solid #e5e7eb; font-size: 9px;
            word-wrap: break-word; overflow-wrap: break-word;
        }
        table.rpt tbody tr:nth-child(even) td { background: #f7f9fc; }
        table.rpt tfoot td {
            padding: 6px; border: 0.5px solid #e5e7eb; font-weight: bold;
            background: #eef4fb; font-size: 9px;
        }
        .num { text-align: right; }

        .rpt-foot {
            position: fixed; bottom: -26px; left: 0; right: 0;
            text-align: center; font-size: 8px; color: #9ca3af;
        }
        .rpt-empty { text-align: center; color: #9ca3af; padding: 24px; font-size: 11px; }
    </style>
</head>
<body>
    <table class="rpt-head">
        <tr>
            <td style="width:60px;">
                @if (!empty($logo))
                    <img src="{{ $logo }}" class="rpt-logo" alt="">
                @endif
            </td>
            <td>
                <div class="rpt-title">{{ $title }}</div>
                <div class="rpt-sub">{{ $ownerName }}@if(!empty($appName)) · {{ $appName }}@endif</div>
            </td>
            <td class="rpt-meta">
                {{ __('Generated') }}: {{ now()->format('d M Y H:i') }}<br>
                {{ __('Records') }}: {{ count($rows) }}
            </td>
        </tr>
    </table>

    @if (empty($rows))
        <div class="rpt-empty">{{ __('No records found for the selected filters.') }}</div>
    @else
        <table class="rpt">
            <thead>
                <tr>
                    @foreach ($headers as $h)
                        <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            @if (!empty($totals))
                <tfoot>
                    <tr>
                        @foreach ($totals as $t)
                            <td>{{ $t }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    <div class="rpt-foot">{{ __('Confidential') }} {{ $appName }} {{ __('Report') }} — {{ __('Do Not Share') }}</div>
</body>
</html>
