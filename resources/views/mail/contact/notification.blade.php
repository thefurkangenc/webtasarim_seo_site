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
                            <h1 style="margin:0 0 16px;font-size:18px;">Yeni iletişim formu mesajı</h1>
                            <p style="margin:0 0 8px;"><strong>Ad:</strong> {{ $submission->name }}</p>
                            <p style="margin:0 0 8px;"><strong>E-posta:</strong> {{ $submission->email }}</p>
                            @if (filled($submission->phone))
                                <p style="margin:0 0 8px;"><strong>Telefon:</strong> {{ $submission->phone }}</p>
                            @endif
                            <p style="margin:16px 0 8px;"><strong>Mesaj:</strong></p>
                            <p style="margin:0;white-space:pre-wrap;">{{ $submission->message }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
