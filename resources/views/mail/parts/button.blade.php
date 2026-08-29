@php $color = $color ?? '#185FA5'; @endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:2px 0 22px;">
    <tr>
        <td align="center" bgcolor="{{ $color }}" style="border-radius:10px;">
            <a href="{{ $url }}" target="_blank" style="display:inline-block; padding:13px 30px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:14.5px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:10px;">{{ $label }}</a>
        </td>
    </tr>
</table>
