@php
    $appName = getOption('app_name') ?: 'Centresidence';
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <title>@yield('subject', $appName)</title>
</head>
{{-- All styling is INLINE + table-based on purpose: email clients (Outlook, Gmail) routinely
     strip <style> blocks, so this design must not depend on one. --}}
<body style="margin:0; padding:0; background:#EEF1F5; -webkit-text-size-adjust:100%;">
    {{-- Hidden preview text --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; opacity:0; color:transparent;">@yield('preheader', $appName)</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#EEF1F5;">
        <tr>
            <td align="center" style="padding:28px 14px 46px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid rgba(15,42,74,0.06);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="background:#0F2A4A; padding:26px 32px 22px; border-bottom:3px solid #E7A339;">
                            <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:20px; font-weight:700; color:#ffffff; letter-spacing:0.02em;">{{ $appName }}</div>
                            <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:10.5px; letter-spacing:0.12em; text-transform:uppercase; color:#7f9bbd; margin-top:5px; font-weight:600;">{{ __('Real Estate · Simplified · Connected') }}</div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:34px 34px 30px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:22px 34px 30px; border-top:1px solid #E4E9F0;">
                            <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:13px; font-weight:700; color:#0F2A4A;">{{ $appName }}</div>
                            <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:11px; color:#8A97A8; margin:3px 0 12px; letter-spacing:0.03em;">{{ __('Real Estate. Simplified. Connected.') }}</div>
                            <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:11px; line-height:1.6; color:#8A97A8;">@yield('footnote', __('You received this because you have a :app account.', ['app' => $appName]))</div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
