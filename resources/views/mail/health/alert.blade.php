<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sistem sağlığı uyarısı</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;padding:28px;">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 8px;font-size:18px;">Sistemde {{ $report['counts']['critical'] }} kritik sorun var</h1>
                            <p style="margin:0 0 20px;font-size:13px;color:#6b7280;">
                                {{ config('app.name') }} · kontrol zamanı
                                {{ \Illuminate\Support\Carbon::parse($report['checked_at'])->translatedFormat('d F Y H:i') }}
                            </p>

                            @foreach ($checks as $check)
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                    style="margin:0 0 12px;border-left:4px solid {{ $check['status'] === 'critical' ? '#dc2626' : '#d97706' }};background:#f9fafb;border-radius:4px;">
                                    <tr>
                                        <td style="padding:12px 14px;">
                                            <strong style="display:block;margin:0 0 4px;">
                                                {{ $check['label'] }}
                                                <span style="font-weight:normal;color:{{ $check['status'] === 'critical' ? '#dc2626' : '#d97706' }};">
                                                    · {{ config('health.statuses.'.$check['status'].'.label') }}
                                                </span>
                                            </strong>
                                            <span style="display:block;font-size:14px;">{{ $check['message'] }}</span>
                                            @if ($check['hint'])
                                                <span style="display:block;margin-top:6px;font-size:12px;color:#6b7280;">{{ $check['hint'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <p style="margin:22px 0 0;">
                                <a href="{{ $panelUrl }}"
                                    style="display:inline-block;background:#605dff;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:6px;font-size:14px;">
                                    Sistem sağlığı panelini aç
                                </a>
                            </p>

                            <p style="margin:18px 0 0;font-size:12px;color:#9ca3af;">
                                Bu e-posta kritik bir sorun sürdüğü sürece günde bir kez gönderilir.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
