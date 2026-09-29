<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cau_Hinh_May_Quet_QR_Gervin</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
            size: A4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .sub {
            font-size: 9.5px;
            color: #64748b;
            margin: 0;
        }
        .banner {
            background-color: #fef3c7;
            border: 1.5px solid #f59e0b;
            border-radius: 6px;
            padding: 7px 12px;
            text-align: center;
            margin-bottom: 12px;
        }
        .banner-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #92400e;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }
        .banner-desc {
            font-size: 9.5px;
            color: #78350f;
            margin: 0;
        }
        .commands-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .command-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            background-color: #f8fafc;
        }
        .step-num-cell {
            width: 48px;
            text-align: center;
            vertical-align: middle;
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
            border-top-left-radius: 7px;
            border-bottom-left-radius: 7px;
        }
        .qr-cell {
            width: 115px;
            text-align: center;
            vertical-align: middle;
            padding: 6px;
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
        }
        .qr-cell img {
            width: 95px;
            height: 95px;
            display: block;
            margin: 0 auto;
        }
        .info-cell {
            padding: 8px 12px;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            background-color: #e0e7ff;
            color: #3730a3;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 4px;
            margin-bottom: 3px;
        }
        .cmd-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 2px 0;
        }
        .cmd-desc {
            font-size: 9.5px;
            color: #64748b;
            margin: 0 0 6px 0;
        }
        .cmd-box {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            padding: 4px 8px;
            font-family: monospace;
            font-size: 10.5px;
            font-weight: bold;
            color: #4338ca;
            word-break: break-all;
        }
        .footer {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
        }
        .footer table {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CÔNG TY TNHH GERVIN - HƯỚNG DẪN CẤU HÌNH MÁY QUÉT QR</h1>
        <p class="sub">Thiết bị: Rakinda RK80ER & Đầu đọc mã vạch / QR | Hệ thống: https://gervinwood.vn/scan</p>
    </div>

    <div class="banner">
        <div class="banner-title">★ QUÉT TỪ TRÊN XUỐNG ĐỂ CẤU HÌNH MÁY QUÉT ★</div>
        <div class="banner-desc">Đưa mắt quét qua lần lượt từng mã QR từ <strong>Bước 1</strong> đến <strong>Bước {{ count($commands) }}</strong> theo đúng thứ tự từ trên xuống dưới.</div>
    </div>

    <table class="commands-table">
        @foreach($commands as $idx => $cmd)
            @php
                $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(130)
                    ->margin(1)
                    ->generate($cmd->cmd);
                $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
            @endphp
            <tr>
                <td class="command-card">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td class="step-num-cell">
                                #{{ $cmd->step ?? ($idx + 1) }}
                            </td>
                            <td class="qr-cell">
                                <img src="{{ $qrDataUri }}" alt="QR Code #{{ $cmd->step ?? ($idx + 1) }}"/>
                            </td>
                            <td class="info-cell">
                                <span class="badge">{{ $cmd->badge ?? ('Bước ' . ($idx + 1)) }}</span>
                                <div class="cmd-title">{{ $cmd->title }}</div>
                                @if(!empty($cmd->desc))
                                    <div class="cmd-desc">{{ $cmd->desc }}</div>
                                @endif
                                <div class="cmd-box">{{ $cmd->cmd }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach
    </table>

    <div class="footer">
        <table>
            <tr>
                <td>Hệ thống ERP GervinWood.vn — Bộ mã QR cấu hình thiết bị xưởng</td>
                <td style="text-align: right;">Ngày xuất bản: {{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
