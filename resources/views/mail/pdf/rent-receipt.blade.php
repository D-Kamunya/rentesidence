<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; color: #1B2430; font-size: 12px; }
    .sheet { padding: 34px 40px; }

    .head { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .head td { vertical-align: top; }
    .brand { font-size: 19px; font-weight: bold; color: #0F2A52; }
    .brand-sub { font-size: 10px; color: #7C828C; letter-spacing: 1px; text-transform: uppercase; margin-top: 3px; }
    .rc-title { font-size: 13px; font-weight: bold; color: #185FA5; text-align: right; letter-spacing: .5px; }
    .paid {
        display: inline-block; margin-top: 8px; padding: 4px 12px; border-radius: 20px;
        background: #E3F3EC; border: 1px solid #9FE1CB; color: #0B7A55;
        font-size: 10px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase;
    }

    .amount-band { background: #0F2A52; border-radius: 10px; padding: 18px 22px; margin-bottom: 22px; }
    .amount-band .lbl { color: #AFC4E0; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
    .amount-band .val { color: #FFFFFF; font-size: 26px; font-weight: bold; margin-top: 2px; }

    table.facts { width: 100%; border-collapse: collapse; }
    table.facts td { padding: 9px 0; border-bottom: 1px solid #EFEBE3; vertical-align: top; }
    table.facts td.k { color: #7C828C; font-size: 11px; width: 42%; }
    table.facts td.v { color: #1B2430; font-size: 12px; font-weight: bold; text-align: right; }
    table.facts tr:last-child td { border-bottom: none; }
    .mono { font-family: 'Courier New', Courier, monospace; }

    .parties { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .parties td { width: 50%; vertical-align: top; padding-right: 14px; }
    .parties .lbl { color: #7C828C; font-size: 10px; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 3px; }
    .parties .nm { font-size: 12px; font-weight: bold; color: #1B2430; }

    .foot { margin-top: 26px; padding-top: 16px; border-top: 1px solid #EFEBE3; color: #9CA3AF; font-size: 10px; line-height: 1.6; }
</style>
</head>
<body>
<div class="sheet">

    <table class="head">
        <tr>
            <td>
                <div class="brand">{{ $r['appName'] }}</div>
                <div class="brand-sub">Rent Payment Receipt</div>
            </td>
            <td style="text-align:right;">
                <div class="rc-title">RECEIPT</div>
                @if ($r['paid'])<span class="paid">Paid</span>@endif
            </td>
        </tr>
    </table>

    <div class="amount-band">
        <div class="lbl">Amount paid</div>
        <div class="val">{{ $r['amount'] }}</div>
    </div>

    <table class="parties">
        <tr>
            @if ($r['tenantName'])
            <td>
                <div class="lbl">Billed to</div>
                <div class="nm">{{ $r['tenantName'] }}</div>
                @if ($r['propertyLine'])<div style="color:#4A4F57;font-size:11px;margin-top:2px;">{{ $r['propertyLine'] }}</div>@endif
            </td>
            @endif
            @if ($r['landlordName'])
            <td>
                <div class="lbl">Received by</div>
                <div class="nm">{{ $r['landlordName'] }}</div>
            </td>
            @endif
        </tr>
    </table>

    <table class="facts">
        <tr>
            <td class="k">Receipt number</td>
            <td class="v mono">{{ $r['receiptNo'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="k">Date paid</td>
            <td class="v">{{ optional($r['issuedAt'])->format('d M Y, g:i A') }}</td>
        </tr>
        <tr>
            <td class="k">Billing period</td>
            <td class="v">{{ $r['billingMonth'] }}</td>
        </tr>
        <tr>
            <td class="k">Payment method</td>
            <td class="v">{{ $r['method'] }}</td>
        </tr>
        @if ($r['code'])
        <tr>
            <td class="k">Transaction code</td>
            <td class="v mono">{{ $r['code'] }}</td>
        </tr>
        @endif
        <tr>
            <td class="k">Total</td>
            <td class="v" style="color:#0F2A52;font-size:14px;">{{ $r['amount'] }}</td>
        </tr>
    </table>

    <div class="foot">
        This receipt confirms a rent payment on your {{ $r['appName'] }} account. Please keep it for your records.
        This is a computer-generated document and is valid without a signature.
    </div>

</div>
</body>
</html>
