<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tem dán kiện - {{ $package->name }}</title>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
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
            background: #ffffff;
            color: #000000;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            padding: 16px;
            background: #f3f4f6;
        }

        .print-toolbar {
            width: 100mm;
            margin: 0 auto 12px;
            padding: 8px 12px;
            border: 1px solid #000000;
            border-radius: 8px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .print-toolbar__title {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #000000;
        }

        .print-toolbar__actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-toolbar__button {
            border: 0;
            border-radius: 6px;
            padding: 6px 14px;
            cursor: pointer;
            color: #ffffff;
            background: #000000;
            font-size: 12px;
            font-weight: 800;
        }

        .print-toolbar__button--light {
            color: #000000;
            background: #e5e7eb;
            border: 1px solid #000000;
        }

        .label-page {
            width: 100mm;
            height: 100mm;
            margin: 0 auto;
            background: #ffffff;
            page-break-after: avoid;
            page-break-before: avoid;
            page-break-inside: avoid;
            padding: 1.5mm;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        }

        .label {
            width: 100%;
            height: 100%;
            border: 2px solid #000000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            page-break-inside: avoid;
            background: #ffffff;
        }

        /* ── Header ── */
        .label__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border-bottom: 2px solid #000000;
            padding: 1.5mm 2.5mm;
            flex: 0 0 auto;
        }

        .label__brand {
            flex: 1 1 auto;
            padding-right: 2mm;
        }

        .label__brand-name {
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #000000;
            line-height: 1.1;
        }

        .label__title {
            margin: 0.5mm 0 0;
            font-size: 15px;
            line-height: 1.15;
            font-weight: 900;
            text-transform: uppercase;
            color: #000000;
        }

        .label__top-meta {
            flex: 0 0 auto;
            text-align: right;
        }

        .label__logo {
            max-width: 26mm;
            max-height: 11mm;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: grayscale(100%) contrast(200%);
        }

        /* ── Body ── */
        .label__content {
            display: flex;
            width: 100%;
            flex: 1 1 auto;
            min-height: 0;
            align-items: stretch;
        }

        .label__content-left {
            flex: 0 0 62%;
            height: 100%;
            padding: 2mm;
            border-right: 1.5px solid #000000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            gap: 1mm;
        }

        .label__content-right {
            flex: 0 0 38%;
            padding: 2mm 1mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .label__package-code {
            font-size: 18px;
            font-weight: 900;
            line-height: 1.1;
            color: #000000;
            display: block;
            margin-bottom: 1.5mm;
        }

        .label__content-line {
            margin-bottom: 1mm;
            font-size: 11px;
            line-height: 1.25;
            word-break: break-word;
            color: #000000;
            font-weight: 700;
        }

        .label__content-line strong {
            color: #000000;
            font-weight: 900;
        }

        .label__section-divider {
            border-top: 1.5px solid #000000;
            margin: 1.5mm 0 1mm;
            padding-top: 1mm;
        }

        .label__section-title {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            color: #000000;
            margin-bottom: 1mm;
            display: block;
        }

        /* ── QR Code ── */
        .label__qr {
            width: 32mm;
            height: 32mm;
            margin: 0 auto;
        }

        .label__qr svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .label__qr-code-text {
            font-size: 12px;
            font-weight: 900;
            font-family: monospace;
            color: #000000;
            margin-top: 1.5mm;
            word-break: break-all;
            line-height: 1.1;
        }

        .label__qr-hint {
            font-size: 8.5px;
            font-weight: 800;
            color: #000000;
            margin-top: 0.8mm;
            text-transform: uppercase;
        }

        /* ── Footer ── */
        .label__bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border-top: 1.5px solid #000000;
            padding: 1.5mm 2.5mm;
            flex: 0 0 auto;
            background: #ffffff;
        }

        .label__bottom-left {
            flex: 1 1 auto;
        }

        .label__bottom-right {
            flex: 0 0 auto;
            text-align: right;
        }

        .label__meta-line {
            font-size: 10px;
            line-height: 1.3;
            color: #000000;
            font-weight: 700;
        }

        .label__meta-line strong {
            color: #000000;
            font-weight: 900;
        }

        @media print {
            html,
            body {
                width: 100mm;
                height: 100mm;
                background: #ffffff !important;
                color: #000000 !important;
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
                padding: 1mm;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <p class="print-toolbar__title">Tem dán kiện: {{ $package->name }} (100x100mm)</p>
        <div class="print-toolbar__actions">
            <button type="button" class="print-toolbar__button" style="background:#059669; border:1px solid #047857;" onclick="ZebraPacking.openModal(@json($zebraPrintData))">In máy Zebra (60x60mm)</button>
            <button type="button" class="print-toolbar__button" onclick="window.print()">In tem (A4/100x100)</button>
            <button type="button" class="print-toolbar__button print-toolbar__button--light" onclick="window.close()">Đóng</button>
        </div>
    </div>

    <main class="label-page">
        <section class="label">
            {{-- Header --}}
            <div class="label__top">
                <div class="label__brand">
                    <div class="label__brand-name">GERVIN WOOD</div>
                    <h1 class="label__title">TEM KIỆN ĐÓNG GÓI</h1>
                </div>
                <div class="label__top-meta">
                    <img src="{{ asset('logo.png') }}" alt="Gervin Wood" class="label__logo">
                </div>
            </div>

            {{-- Main Content --}}
            <div class="label__content">
                <div class="label__content-left">
                    {{-- Thông tin đóng gói --}}
                    <div>
                        <span class="label__package-code">{{ $packageCode }}</span>
                        <div class="label__content-line">
                            <strong>Đơn:</strong> {{ $orderCode }} &nbsp;|&nbsp; <strong>Loại:</strong> {{ $typeSummary }}
                        </div>
                        <div class="label__content-line">
                            <strong>Tổng SL:</strong> <span style="font-size: 13px; font-weight: 900;">{{ $totalItems }}</span> linh kiện
                        </div>
                        @if(!empty($plateSttString))
                            <div class="label__content-line" style="font-size: 10px; line-height: 1.2;">
                                <strong>STT tấm:</strong> {{ $plateSttString }}
                            </div>
                        @endif
                    </div>

                    {{-- Thông tin khách hàng & giao hàng --}}
                    <div class="label__section-divider">
                        <span class="label__section-title">GIAO HÀNG</span>
                        <div class="label__content-line" style="font-size: 12px; font-weight: 800;">
                            <strong>KH:</strong> {{ $customerName }}
                        </div>
                        <div class="label__content-line" style="font-size: 12px;">
                            <strong>SĐT:</strong> <span style="font-weight: 900;">{{ $customerPhone }}</span>
                        </div>
                        <div class="label__content-line" style="font-size: 10px; line-height: 1.25;">
                            <strong>Đ/c:</strong> {{ $deliveryAddress }}
                        </div>
                    </div>
                </div>

                {{-- QR code & Code --}}
                <div class="label__content-right">
                    <div class="label__qr">{!! $qrSvg !!}</div>
                    <div class="label__qr-code-text">{{ $packageCode }}</div>
                    <div class="label__qr-hint">Quét mã kiện</div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="label__bottom">
                <div class="label__bottom-left">
                    <div class="label__meta-line">
                        <strong>Ngày:</strong> {{ optional($package->updated_at)->format('H:i d/m/Y') ?? '—' }}
                    </div>
                </div>
                <div class="label__bottom-right">
                    <div class="label__meta-line">
                        <strong>Đóng gói:</strong> {{ $package->packer?->name ?? '—' }}
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('assets/js/qrcode.js') }}"></script>
    <script src="{{ asset('assets/js/zebra-packing-label.js') }}"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('zebra') === '1') {
            window.onload = function () {
                ZebraPacking.openModal(@json($zebraPrintData));
            };
        } else {
            window.onload = function () {
                window.print();
            };
        }
    </script>
</body>
</html>
