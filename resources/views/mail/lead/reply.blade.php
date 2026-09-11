<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;padding:28px;">
                    <tr>
                        <td>
                            <p style="margin:0 0 16px;">Sayın {{ $lead->name }},</p>
                            <div style="margin:0 0 24px;white-space:pre-wrap;">{{ $bodyText }}</div>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="border-top:1px solid #e5e7eb;margin-top:8px;">
                                <tr>
                                    <td style="padding-top:16px;font-size:12px;color:#6b7280;">
                                        <strong>Bize gönderdiğiniz mesaj</strong>
                                        ({{ $lead->created_at?->format('d.m.Y H:i') }}):<br>
                                        <span style="white-space:pre-wrap;">{{ \Illuminate\Support\Str::limit($lead->message, 600) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
