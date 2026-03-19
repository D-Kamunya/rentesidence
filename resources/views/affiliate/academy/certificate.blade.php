<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400;1,600&family=Cinzel:wght@400;600;700&display=swap');

        @page { margin: 10px 14px 10px 10px; padding: 0; size: a4 landscape; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { margin: 0; padding: 0; width: 100%; }

        body {
            font-family: 'Cormorant Garamond', 'DejaVu Serif', Georgia, serif;
            background-color: #0f0f1a;
        }

        /* ── Nested frame borders ── */
        .outer-frame  { width: 98%; padding: 5px; background-color: #c9a84c; }
        .middle-frame { padding: 4px; background-color: #0f0f1a; }
        .inner-frame  { padding: 3px; background-color: #9c7a2e; }

        /* ── Main card ── */
        .certificate {
            background-color: #0f0f1a;
            padding: 44px 60px 40px;
            text-align: center;
            position: relative;
        }

        /* ── Corner marks ── */
        .corner-mark {
            position: absolute;
            width: 26px;
            height: 26px;
            border-color: #c9a84c;
            border-style: solid;
        }
        .corner-mark.tl { top: 26px;    left: 26px;    border-width: 2px 0 0 2px; }
        .corner-mark.tr { top: 26px;    right: 26px;   border-width: 2px 2px 0 0; }
        .corner-mark.bl { bottom: 20px; left: 26px;    border-width: 0 0 2px 2px; }
        .corner-mark.br { bottom: 20px; right: 26px;   border-width: 0 2px 2px 0; }

        /* ── Watermark ── */
        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            font-family: 'Cinzel', serif;
            font-size: 90px;
            font-weight: 700;
            color: rgba(201,168,76,0.04);
            transform: translate(-50%, -50%) rotate(-25deg);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
        }

        .cert-body-wrap { position: relative; z-index: 1; }

        .brand {
            font-family: 'Cinzel', 'DejaVu Sans', serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 7px;
            color: #c9a84c;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .brand-rule {
            width: 180px;
            height: 1px;
            background-color: #9c7a2e;
            margin: 0 auto 20px;
        }
        .eyebrow {
            font-family: 'Cinzel', 'DejaVu Sans', serif;
            font-size: 9px;
            letter-spacing: 5px;
            color: #9c7a2e;
            text-transform: uppercase;
            margin-bottom: 7px;
        }
        .cert-title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 46px;
            font-weight: 300;
            color: #f0e8d0;
            line-height: 1.1;
            margin-bottom: 20px;
        }
        .cert-title em { font-style: italic; color: #e8c97e; }

        /* ── Dividers ── */
        .divider-table {
            width: 55%;
            margin: 0 auto 18px;
            border-collapse: collapse;
        }
        .divider-table td { padding: 0; vertical-align: middle; }
        .div-line { height: 1px; background-color: #9c7a2e; }
        .div-shapes { white-space: nowrap; padding: 0 8px; text-align: center; width: 1%; }
        .d-dot {
            display: inline-block;
            width: 4px; height: 4px;
            background-color: #9c7a2e;
            transform: rotate(45deg);
            margin: 0 2px;
            vertical-align: middle;
        }
        .d-diamond {
            display: inline-block;
            width: 8px; height: 8px;
            background-color: #c9a84c;
            transform: rotate(45deg);
            margin: 0 3px;
            vertical-align: middle;
        }

        .presented-to {
            font-size: 13px;
            font-style: italic;
            color: #9e9080;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .recipient {
            font-size: 42px;
            font-weight: 400;
            font-style: italic;
            color: #f0e8d0;
            line-height: 1.1;
            margin-bottom: 5px;
        }
        .name-rule {
            width: 260px;
            height: 1px;
            background-color: #c9a84c;
            margin: 0 auto 20px;
        }
        .cert-body {
            font-size: 15px;
            font-weight: 300;
            color: #b8ac98;
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto 20px;
        }
        .cert-body strong { font-weight: 600; color: #f0e8d0; }

        /* ── Footer ──
             Float left + float right, seal is a normal centered block between.
             DomPDF handles float: left and float: right reliably.
             The seal sits in the remaining middle space via text-align: center on the wrap.
        ── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        .footer-td-left {
            width: 220px;
            text-align: left;
            vertical-align: bottom;
            padding: 0;
        }
        .footer-td-center {
            text-align: center;
            vertical-align: bottom;
            padding: 0;
        }
        .footer-td-right {
            width: 220px;
            text-align: right;
            vertical-align: bottom;
            padding: 0;
        }
        .footer-rule-left {
            width: 100%;
            height: 1px;
            background-color: #9c7a2e;
            margin-bottom: 6px;
        }
        .footer-rule-right {
            width: 100%;
            height: 1px;
            background-color: #9c7a2e;
            margin-bottom: 6px;
        }
        .footer-label {
            font-family: 'Cinzel', 'DejaVu Sans', serif;
            font-size: 8px;
            letter-spacing: 3px;
            color: #9c7a2e;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .footer-value {
            font-size: 14px;
            font-style: italic;
            color: #f0e8d0;
        }
        .seal { width: 62px; height: 62px; }
    </style>
</head>
<body>

<div class="outer-frame">
  <div class="middle-frame">
    <div class="inner-frame">
      <div class="certificate">

        <div class="watermark">Centresidence</div>

        <div class="corner-mark tl"></div>
        <div class="corner-mark tr"></div>
        <div class="corner-mark bl"></div>
        <div class="corner-mark br"></div>

        <div class="cert-body-wrap">

            <div class="brand">Centresidence</div>
            <div class="brand-rule"></div>

            <div class="eyebrow">Official Recognition</div>
            <div class="cert-title">Certificate of <em>Achievement</em></div>

            <table class="divider-table"><tr>
                <td><div class="div-line"></div></td>
                <td class="div-shapes">
                    <span class="d-dot"></span><span class="d-diamond"></span><span class="d-dot"></span>
                </td>
                <td><div class="div-line"></div></td>
            </tr></table>

            <div class="presented-to">This is to certify that</div>
            <div class="recipient">{{ $user->name }}</div>
            <div class="name-rule"></div>

            <div class="cert-body">
                has successfully completed the <strong>Centresidence Marketing Partner Academy</strong>
                and is officially recognized as a certified partner authorized to promote and represent
                Centresidence Real Estate Technologies with professionalism and integrity
            </div>

            <table class="divider-table"><tr>
                <td><div class="div-line"></div></td>
                <td class="div-shapes"><span class="d-diamond"></span></td>
                <td><div class="div-line"></div></td>
            </tr></table>

            <table class="footer-table"><tr>
                <td class="footer-td-left">
                    <div class="footer-rule-left"></div>
                    <div class="footer-label">Date of Issue</div>
                    <div class="footer-value">{{ $date }}</div>
                </td>
                <td class="footer-td-center">
                    <svg class="seal" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="47" stroke="#c9a84c" stroke-width="2"/>
                        <circle cx="50" cy="50" r="40" stroke="#9c7a2e" stroke-width="1" stroke-dasharray="3 2.5"/>
                        <circle cx="50" cy="50" r="33" stroke="#c9a84c" stroke-width="1"/>
                        <path d="M50 18 L53 30 L64 22 L57 33 L70 34 L59 41 L65 53 L52 47 L50 60 L48 47 L35 53 L41 41 L30 34 L43 33 L36 22 L47 30 Z" fill="#c9a84c"/>
                        <circle cx="50" cy="50" r="5" fill="#e8c97e"/>
                        <text x="50" y="76" text-anchor="middle" font-family="'Cinzel', serif" font-size="6.5" class="brand" letter-spacing="2">VERIFIED</text>
                    </svg>
                </td>
                <td class="footer-td-right">
                    <div class="footer-rule-right"></div>
                    <div class="footer-label">Authorized By</div>
                    <div class="footer-value">Centresidence</div>
                </td>
            </tr></table>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>