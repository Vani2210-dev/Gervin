<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tem dán kiện - {{ $package->name }}</title>
    <style>
        @page {
            size: 100mm 100mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            color: #000000;
            font-family: Tahoma, Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            padding: 16px;
        }

        .print-toolbar {
            width: 100mm;
            margin: 0 auto 12px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }

        .print-toolbar__title {
            margin: 0;
            font-size: 13px;
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
            padding: 6px 12px;
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
            width: 100mm;
            height: 100mm;
            margin: 0 auto;
            background: #ffffff;
            page-break-after: avoid;
            page-break-before: avoid;
            page-break-inside: avoid;
            padding: 2mm;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border-bottom: 1.5px solid #111827;
            padding: 2mm 2.5mm 1.5mm;
            flex: 0 0 auto;
        }

        .label__brand {
            flex: 1 1 auto;
            padding-right: 2mm;
        }

        .label__brand-name {
            margin-bottom: 0.5mm;
            color: #ef4444;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .label__title {
            margin: 0;
            font-size: 13px;
            line-height: 1.15;
            font-weight: 800;
            text-transform: uppercase;
            color: #111827;
        }

        .label__top-meta {
            flex: 0 0 auto;
            text-align: right;
        }

        .label__logo {
            max-width: 24mm;
            max-height: 10mm;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .label__content {
            display: flex;
            width: 100%;
            flex: 1 1 auto;
            min-height: 0;
            align-items: stretch;
        }

        .label__content-left {
            flex: 0 0 63%;
            height: 100%;
            padding: 1.5mm 2mm;
            border-right: 1px dashed #111827;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1mm;
            overflow: hidden;
        }

        .label__content-right {
            flex: 0 0 37%;
            padding: 1.5mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .label__info-group-title {
            display: block;
            width: 100%;
            margin-bottom: 1mm;
            color: #111827;
            font-size: 8.5px;
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .label__info-group--customer {
            border-top: 1px dashed #111827;
            padding-top: 1.2mm;
            margin-top: 1mm;
        }

        .label__content-line {
            margin-bottom: 0.8mm;
            font-size: 9px;
            line-height: 1.25;
            word-break: break-word;
            color: #1f2937;
        }

        .label__content-line strong {
            color: #111827;
            font-weight: 700;
        }

        .label__package-code {
            font-size: 12px;
            font-weight: 900;
            color: #111827;
        }

        .label__qr {
            width: 28mm;
            height: 28mm;
            margin: 0 auto;
        }

        .label__qr svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .label__qr-code-text {
            font-size: 9.5px;
            font-weight: 800;
            font-family: monospace;
            color: #111827;
            margin-top: 1mm;
            word-break: break-all;
        }

        .label__qr-hint {
            font-size: 7px;
            color: #6b7280;
            margin-top: 0.5mm;
        }

        .label__bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border-top: 1px dashed #111827;
            padding: 1.5mm 2.5mm;
            flex: 0 0 auto;
            background: #fafafa;
        }

        .label__bottom-left {
            flex: 1 1 auto;
        }

        .label__bottom-right {
            flex: 0 0 auto;
            text-align: right;
        }

        .label__meta-line {
            font-size: 8px;
            line-height: 1.3;
            color: #374151;
        }

        .label__meta-line strong {
            color: #111827;
        }

        @media print {
            html,
            body {
                width: 100mm;
                height: 100mm;
                background: #ffffff;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .print-toolbar {
                display: none !important;
            }

            .label-page {
                width: 100mm;
                height: 100mm;
                margin: 0;
                padding: 1.5mm;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <p class="print-toolbar__title">Tem dán kiện: {{ $package->name }} (100x100mm)</p>
        <div class="print-toolbar__actions">
            <button type="button" class="print-toolbar__button" onclick="window.print()">In tem</button>
            <button type="button" class="print-toolbar__button print-toolbar__button--light" onclick="window.close()">Đóng</button>
        </div>
    </div>

    <main class="label-page">
        <section class="label">
            {{-- Header --}}
            <div class="label__top">
                <div class="label__brand">
                    <div class="label__brand-name">Gervin Wood</div>
                    <h1 class="label__title">Tem dán kiện đóng gói</h1>
                </div>
                <div class="label__top-meta">
                    <img src="{{ asset('logo.png') }}" alt="Gervin Wood" class="label__logo">
                </div>
            </div>

            {{-- Main Content --}}
            <div class="label__content">
                <div class="label__content-left">
                    {{-- Thông tin đóng gói --}}
                    <div class="label__info-group">
                        <div class="label__info-group-title">Thông tin đóng gói</div>
                        <div class="label__content-line">
                            <strong>Mã kiện:</strong> <span class="label__package-code">{{ $packageCode }}</span>
                        </div>
                        <div class="label__content-line">
                            <strong>Mã đơn:</strong> {{ $orderCode }} &nbsp;|&nbsp; <strong>Loại:</strong> {{ $typeSummary }}
                        </div>
                        <div class="label__content-line">
                            <strong>Tổng SL:</strong> {{ $totalItems }} linh kiện
                        </div>
                        @if(!empty($plateSttString))
                            <div class="label__content-line" style="font-size: 8px;">
                                <strong>Mã STT:</strong> <span style="font-weight: 700;">{{ $plateSttString }}</span>
                            </div>
                        @endif
                        <div class="label__content-line">
                            <strong>Người đóng:</strong> {{ $package->packer?->name ?? '—' }}
                        </div>
                    </div>

                    {{-- Thông tin khách hàng --}}
                    <div class="label__info-group label__info-group--customer">
                        <div class="label__info-group-title">Thông tin giao hàng</div>
                        <div class="label__content-line">
                            <strong>Khách hàng:</strong> {{ $customerName }}
                        </div>
                        <div class="label__content-line">
                            <strong>SĐT:</strong> {{ $customerPhone }}
                        </div>
                        <div class="label__content-line" style="font-size: 8px; line-height: 1.2;">
                            <strong>Địa chỉ:</strong> {{ $deliveryAddress }}
                        </div>
                    </div>
                </div>

                {{-- QR code & Code --}}
                <div class="label__content-right">
                    <div class="label__qr">{!! $qrSvg !!}</div>
                    <div class="label__qr-code-text">{{ $packageCode }}</div>
                    <div class="label__qr-hint">Quét mã nhận diện kiện</div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="label__bottom">
                <div class="label__bottom-left">
                    <div class="label__meta-line"><strong>Trạng thái:</strong> Đã hoàn tất</div>
                    <div class="label__meta-line"><strong>Ngày:</strong> {{ optional($package->updated_at)->format('H:i d/m/Y') ?? '—' }}</div>
                </div>
                <div class="label__bottom-right">
                    <div class="label__meta-line"><strong>Đóng gói:</strong> {{ $package->packer?->name ?? '—' }}</div>
                    <div class="label__meta-line" style="font-size: 7.5px; color: #6b7280;">Xác nhận kiện nguyên vẹn</div>
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
