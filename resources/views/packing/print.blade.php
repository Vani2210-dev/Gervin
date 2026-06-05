<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tem dán kiện - {{ $package->name }}</title>
    <style>
        @page {
            size: A6 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            background: #f3f4f6;
            color: #000000;
            font-family: Tahoma, Arial, Helvetica, sans-serif;
        }

        body {
            padding: 16px;
        }

        .print-toolbar {
            width: 105mm;
            margin: 0 auto 12px;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        }

        .print-toolbar__title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }

        .print-toolbar__actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-toolbar__button {
            border: 0;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            color: #ffffff;
            background: #2563eb;
            font-size: 12px;
            font-weight: 700;
        }

        .print-toolbar__button--light {
            color: #374151;
            background: #e5e7eb;
        }

        .label-page {
            width: 105mm;
            height: 148mm;
            margin: 0 auto;
            background: #ffffff;
            page-break-after: avoid;
            page-break-before: avoid;
            page-break-inside: avoid;
            padding: 10px;
        }

        .label {
            width: 100%;
            height: 100%;
            border: 1.5px solid #111827;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .label__top {
            display: table;
            width: 100%;
            border-bottom: 1.5px solid #111827;
            padding: 3mm 3mm 2mm;
            flex: 0 0 auto;
        }

        .label__brand,
        .label__top-meta {
            display: table-cell;
            vertical-align: top;
        }

        .label__brand {
            width: 66%;
            padding-right: 3mm;
        }

        .label__brand-name {
            margin-bottom: 1mm;
            color: #ef4444;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .label__title {
            margin: 0;
            font-size: 16px;
            line-height: 1.12;
            font-weight: 800;
        }

        .label__subtitle {
            margin: 1.5mm 0 0;
            color: #4b5563;
            font-size: 8.5px;
            line-height: 1.35;
        }

        .label__top-meta {
            width: 34%;
            text-align: right;
            vertical-align: middle;
        }

        .label__logo {
            max-width: 28mm;
            max-height: 14mm;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .label__route {
            display: table;
            width: 100%;
            border-bottom: 1px dashed #111827;
            flex: 0 0 25mm;
        }

        .label__route-cell {
            display: table-cell;
            width: 50%;
            min-height: 23mm;
            padding: 2mm;
            vertical-align: top;
            border-right: 1px dashed #111827;
        }

        .label__route-cell:last-child {
            border-right: 0;
        }

        .label__section-title {
            margin-bottom: 1mm;
            font-size: 8.5px;
            font-weight: 700;
        }

        .label__section-value {
            font-size: 10.5px;
            line-height: 1.35;
            font-weight: 700;
            word-break: break-word;
        }

        .label__section-note {
            margin-top: 1mm;
            color: #4b5563;
            font-size: 8px;
            line-height: 1.3;
        }

        .label__content {
            display: flex;
            width: 100%;
            border-bottom: 1px dashed #111827;
            flex: 1 1 auto;
            min-height: 0;
            align-items: stretch;
        }

        .label__content-left,
        .label__content-right {
            min-height: 100%;
        }

        .label__content-left {
            flex: 0 0 63%;
            height: 100%;
            padding: 2.5mm;
            border-right: 1px dashed #111827;
            display: flex;
            flex-direction: column;
            gap: 1mm;
        }

        .label__content-right {
            flex: 0 0 37%;
            padding: 2.5mm;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label__content-title {
            margin: 0 0 2mm;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .label__content-line {
            margin-bottom: 1mm;
            font-size: 10px;
            line-height: 1.25;
            word-break: break-word;
        }

        .label__info-group {
            padding: 0;
        }

        .label__info-group--package {
            flex: 0 0 auto;
        }

        .label__info-group--customer {
            position: relative;
            flex: 0 0 auto;
            margin-top: 1mm;
            margin-right: 0;
            margin-left: 0;
            padding: 2.5mm 0 0;
            border-top: 0;
        }

        .label__info-group--customer::before {
            content: "";
            position: absolute;
            top: 0;
            right: -2.5mm;
            left: -2.5mm;
            border-top: 1px dashed #111827;
        }

        .label__info-group-title {
            display: block;
            width: 100%;
            margin-bottom: 1.5mm;
            padding-bottom: 0.8mm;
            color: #111827;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .label__qr {
            width: 33mm;
            height: 33mm;
            margin: 0 auto;
            /* Nhích QR lên nhẹ để khớp nhịp tem mẫu mà không phá khung */
            transform: translateY(-4mm);
        }

        .label__qr svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .label__bottom {
            display: table;
            width: 100%;
            flex: 0 0 26mm;
        }

        .label__bottom-cell {
            display: table-cell;
            width: 50%;
            padding: 2mm;
            vertical-align: top;
            border-right: 1px dashed #111827;
        }

        .label__bottom-cell:last-child {
            border-right: 0;
            text-align: center;
        }

        .label__meta-line {
            margin-bottom: 1mm;
            font-size: 8.5px;
            line-height: 1.35;
        }

        .label__signature-title {
            margin-bottom: 1mm;
            font-size: 9px;
            font-weight: 800;
        }

        .label__signature-note {
            font-size: 8px;
            line-height: 1.3;
        }

        .label__signature-space {
            height: 12mm;
        }

        @media print {
            html,
            body {
                width: 105mm;
                height: 148mm;
                background: #ffffff;
            }

            body {
                padding: 0;
            }

            .print-toolbar {
                display: none !important;
            }

            .label-page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <p class="print-toolbar__title">Tem dán kiện: {{ $package->name }}</p>
        <div class="print-toolbar__actions">
            <button type="button" class="print-toolbar__button" onclick="window.print()">In tem</button>
            <button type="button" class="print-toolbar__button print-toolbar__button--light" onclick="window.close()">Đóng</button>
        </div>
    </div>

    <main class="label-page">
        <section class="label">
            <div class="label__top">
                <div class="label__brand">
                    <div class="label__brand-name">Gervin Wood</div>
                    <h1 class="label__title">Tem dán kiện đóng gói</h1>
                    <p class="label__subtitle">Dán ngoài kiện để nhận diện khi lưu kho, bàn giao hoặc quét lại.</p>
                </div>
                <div class="label__top-meta">
                    <img src="{{ asset('logo.png') }}" alt="Gervin Wood" class="label__logo">
                </div>
            </div>

            <div class="label__content">
                <div class="label__content-left">
                    <div class="label__info-group label__info-group--package">
                        <div class="label__info-group-title">Thông tin đóng gói</div>
                        <div class="label__content-line"><strong>Mã kiện:</strong> {{ $packageCode }}</div>
                        <div class="label__content-line"><strong>Người đóng gói:</strong> {{ $package->packer?->name ?? '—' }}</div>
                        <div class="label__content-line"><strong>Mã đơn:</strong> {{ $orderCode }}</div>
                        <div class="label__content-line"><strong>Loại:</strong> {{ $typeSummary }}</div>
                        <div class="label__content-line"><strong>Tổng SL linh kiện:</strong> {{ $totalItems }}</div>
                    </div>

                    <div class="label__info-group label__info-group--customer">
                        <div class="label__info-group-title">Thông tin khách hàng</div>
                        <div class="label__content-line"><strong>Tên khách hàng:</strong> {{ $customerName }}</div>
                        <div class="label__content-line"><strong>Số điện thoại:</strong> {{ $customerPhone }}</div>
                        <div class="label__content-line"><strong>Địa chỉ:</strong> {{ $deliveryAddress }}</div>
                    </div>

                    <div class="label__section-note">Kiện đã hoàn tất. Tem này dùng để dán ngoài thùng và quét lại khi cần.</div>
                </div>
                <div class="label__content-right">
                    {{-- QR dùng trực tiếp mã kiện trong database để máy quét trả về đúng text đang lưu. --}}
                    <div class="label__qr">{!! $qrSvg !!}</div>
                </div>
            </div>

            <div class="label__bottom">
                <div class="label__bottom-cell">
                    <div class="label__meta-line"><strong>Trạng thái:</strong> Đã hoàn tất</div>
                    <div class="label__meta-line"><strong>Ngày hoàn tất:</strong> {{ optional($package->updated_at)->format('H:i:s d/m/Y') ?? '—' }}</div>
                </div>
                <div class="label__bottom-cell">
                    <div class="label__signature-title">Người đóng gói</div>
                    <div class="label__signature-note">Ký xác nhận kiện không móp, méo, bể, vỡ.</div>
                    <div class="label__signature-space"></div>
                    <div class="label__section-value">{{ $package->packer?->name ?? '—' }}</div>
                </div>
            </div>
        </section>
    </main>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
