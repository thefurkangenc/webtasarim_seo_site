<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Teklif talebi: {{ $lead->name }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;padding:28px;">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 16px;font-size:18px;">Yeni teklif talebi</h1>
                            <p style="margin:0 0 8px;"><strong>Firma:</strong> {{ $lead->name }}</p>
                            <p style="margin:0 0 8px;"><strong>Telefon:</strong> {{ $lead->phone }}</p>
                            <p style="margin:0 0 8px;"><strong>Hizmet:</strong> {{ $lead->subject }}</p>
                            @if (filled($lead->page_url))
                                <p style="margin:0 0 8px;"><strong>Sayfa:</strong> {{ $lead->page_url }}</p>
                            @endif
                            <p style="margin:16px 0 8px;"><strong>Not:</strong></p>
                            <p style="margin:0;white-space:pre-wrap;">{{ $lead->message }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
