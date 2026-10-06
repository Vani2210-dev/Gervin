<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In tem ván ép - {{ $pressingOrder->code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A6 portrait; /* 105mm x 148mm */
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ── Print Toolbar ── */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 900px;
            margin: 0 auto 20px;
            border-radius: 0 0 12px 12px;
        }

        .print-toolbar__info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .print-toolbar__title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .print-toolbar__badge {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            background: #e0e7ff;
            color: #4338ca;
        }

        .print-toolbar__actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        /* ── Container ── */
        .labels-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding-bottom: 40px;
        }

        /* ── Label Card (Khổ chuẩn 100mm x 140mm vừa vặn A6) ── */
        .label-card {
            width: 100mm;
            height: 142mm;
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 4px;
            padding: 8mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
            page-break-after: always;
            page-break-inside: avoid;
        }

        /* ── Label Header ── */
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 4mm;
        }

        .label-header__brand {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .label-header__sub {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .label-header__tag {
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            text-transform: uppercase;
            background: #f8fafc;
        }

        /* ── QR & Piece Code ── */
        .label-code-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border: 1.5px dashed #0f172a;
            border-radius: 6px;
            padding: 3mm 2mm;
            margin: 3mm 0;
        }

        .label-qr {
            width: 38mm;
            height: 38mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-qr svg {
            width: 100%;
            height: 100%;
        }

        .label-piece-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-top: 2mm;
            background: #0f172a;
            color: #ffffff;
            padding: 2px 10px;
            border-radius: 4px;
        }

        /* ── Specs Table ── */
        .label-specs {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 2mm;
        }

        .label-specs tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .label-specs tr:last-child {
            border-bottom: none;
        }

        .label-specs td {
            padding: 2.5mm 1mm;
            vertical-align: top;
        }

        .label-specs__label {
            font-weight: 600;
            color: #64748b;
            width: 32%;
            font-size: 10px;
            text-transform: uppercase;
        }

        .label-specs__value {
            font-weight: 700;
            color: #0f172a;
            font-size: 11.5px;
        }

        .label-specs__value--highlight {
            font-size: 13px;
            color: #1e1b4b;
        }

        /* ── Label Footer ── */
        .label-footer {
            border-top: 1.5px solid #0f172a;
            padding-top: 2.5mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 9px;
            font-weight: 600;
            color: #64748b;
        }

        .label-footer__index {
            font-weight: 800;
            font-size: 11px;
            color: #0f172a;
        }

        /* ── Print Styles ── */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .print-toolbar {
                display: none !important;
            }

            .labels-container {
                padding: 0;
                gap: 0;
            }

            .label-card {
                box-shadow: none;
                margin: 0 auto;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    {{-- Toolbar --}}
    <div class="print-toolbar">
        <div class="print-toolbar__info">
            <h1 class="print-toolbar__title">Lệnh ép: {{ $pressingOrder->code }}</h1>
            <span class="print-toolbar__badge">{{ count($labels) }} Tấm ván</span>
        </div>
        <div class="print-toolbar__actions">
            <button type="button" onclick="window.history.back()" class="btn btn-secondary">
                ← Quay lại
            </button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                🖨️ In {{ count($labels) }} tem dán
            </button>
        </div>
    </div>

    {{-- Container chứa các tem --}}
    <div class="labels-container">
        @foreach($labels as $idx => $label)
            <div class="label-card">
                {{-- Header --}}
                <div class="label-header">
                    <div>
                        <div class="label-header__brand">GERVIN FACTORY</div>
                        <div class="label-header__sub">TEM ĐỊNH DANH VÁN ÉP THÀNH PHẨM</div>
                    </div>
                    <div class="label-header__tag">
                        {{ $label['purpose_label'] }}
                    </div>
                </div>

                {{-- QR & Barcode Section --}}
                <div class="label-code-box">
                    <div class="label-qr">
                        {!! $label['qr_svg'] !!}
                    </div>
                    <div class="label-piece-code">{{ $label['piece_code'] }}</div>
                </div>

                {{-- Specifications --}}
                <table class="label-specs">
                    <tr>
                        <td class="label-specs__label">Loại ván:</td>
                        <td class="label-specs__value label-specs__value--highlight">{{ $label['material_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-specs__label">Cốt ván:</td>
                        <td class="label-specs__value">{{ $label['core_material'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-specs__label">Quy cách:</td>
                        <td class="label-specs__value">{{ $label['dimensions'] }} (Dày: {{ $label['thickness'] }})</td>
                    </tr>
                    <tr>
                        <td class="label-specs__label">Lệnh ép:</td>
                        <td class="label-specs__value">{{ $label['order_code'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-specs__label">Ghi chú:</td>
                        <td class="label-specs__value" style="font-size: 10px;">{{ $label['notes'] }}</td>
                    </tr>
                </table>

                {{-- Footer --}}
                <div class="label-footer">
                    <div>Ngày ép: <strong>{{ $label['date'] }}</strong></div>
                    <div class="label-footer__index">TẤM {{ $idx + 1 }} / {{ count($labels) }}</div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
