<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Haftalık site özeti</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;padding:28px;">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 6px;font-size:18px;">Haftalık site özeti</h1>
                            <p style="margin:0 0 22px;font-size:13px;color:#6b7280;">
                                {{ $report['from_label'] }} – {{ $report['to_label'] }} · {{ config('app.name') }}
                            </p>

                            <h2 style="margin:0 0 10px;font-size:14px;">Gelen talepler</h2>
                            <p style="margin:0 0 18px;font-size:14px;">
                                Son 7 günde <strong>{{ $report['leads']['new'] }}</strong> yeni mesaj.
                                Şu an <strong>{{ $report['leads']['unread'] }}</strong> okunmamış talep var.
                            </p>

                            <h2 style="margin:0 0 10px;font-size:14px;">Bülten</h2>
                            <p style="margin:0 0 18px;font-size:14px;">
                                Son 7 günde <strong>{{ $report['subscribers']['new'] }}</strong> yeni abone.
                                Toplam aktif abone: <strong>{{ $report['subscribers']['active'] }}</strong>.
                            </p>

                            @if ($report['analytics'])
                                <h2 style="margin:0 0 10px;font-size:14px;">Trafik (Google Analytics, 7 gün)</h2>
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 16px;border-collapse:collapse;">
                                    <tr>
                                        @foreach ($report['analytics']['kpis'] as $kpi)
                                            <td style="padding:10px 8px;background:#f9fafb;border-radius:4px;text-align:center;width:25%;">
                                                <span style="display:block;font-size:11px;color:#6b7280;">{{ $kpi['label'] }}</span>
                                                <span style="display:block;font-size:18px;font-weight:700;margin-top:4px;">
                                                    {{ number_format((int) $kpi['value']) }}
                                                </span>
                                                @if ($kpi['change'] !== null)
                                                    <span style="display:block;font-size:11px;color:{{ $kpi['change'] >= 0 ? '#16a34a' : '#dc2626' }};">
                                                        {{ $kpi['change'] > 0 ? '+' : '' }}{{ $kpi['change'] }}%
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>

                                @if ($report['analytics']['top_pages'] !== [])
                                    <p style="margin:0 0 6px;font-size:12px;color:#6b7280;">En çok görüntülenen sayfalar</p>
                                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 18px;font-size:13px;">
                                        @foreach ($report['analytics']['top_pages'] as $page)
                                            <tr>
                                                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;">
                                                    {{ $page['title'] ?: $page['path'] }}
                                                </td>
                                                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;text-align:right;white-space:nowrap;">
                                                    {{ number_format($page['views']) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            @else
                                <p style="margin:0 0 18px;font-size:13px;color:#6b7280;">
                                    Trafik özeti için Google Analytics bağlantısı kurulmamış veya rapor alınamadı.
                                </p>
                            @endif

                            <p style="margin:22px 0 0;">
                                <a href="{{ $report['dashboard_url'] }}"
                                    style="display:inline-block;background:#605dff;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:6px;font-size:14px;">
                                    Panele git
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
