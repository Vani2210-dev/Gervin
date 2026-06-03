<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $isExport = collect($record->out_data)->sum() > 0;
        $title = $isExport ? 'Phiếu Xuất Kho' : 'Phiếu Nhập Kho';
    @endphp
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            color: #000;
            padding: 14px 20px;
            background: #fff;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .company-info {
            font-size: 9.5px;
            line-height: 1.4;
            max-width: 170px;
        }

        .company-name {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .title-block {
            text-align: center;
            flex: 1;
        }

        .title-block .dept {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title-block h2 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0 2px;
        }

        .title-block .subtitle {
            font-size: 9.5px;
        }

        .form-no {
            font-size: 9.5px;
            text-align: right;
            min-width: 120px;
        }

        .info-section {
            margin: 8px 0 6px;
            line-height: 1.8;
        }

        .info-row {
            display: flex;
            gap: 20px;
        }

        .info-row .field {
            display: flex;
            align-items: baseline;
            gap: 4px;
            flex: 1;
        }

        .info-row .field label {
            white-space: nowrap;
            font-weight: normal;
        }

        .info-row .field .line {
            flex: 1;
            border-bottom: 1px solid #000;
            min-width: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            font-size: 10.5px;
        }

        th {
            font-weight: bold;
            background: #f5f5f5;
        }

        td:nth-child(2) {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .sig-row {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            text-align: center;
        }

        .sig-block {
            width: 30%;
        }

        .sig-block .title {
            font-weight: bold;
            font-size: 10.5px;
            margin-bottom: 1px;
        }

        .sig-block .sub {
            font-style: italic;
            font-size: 9.5px;
            color: #444;
        }

        .sig-space {
            height: 40px;
        }

        @media print {
            body {
                padding: 8px 12px;
            }

            @page {
                size: A5;
                margin: 8mm 10mm;
            }
        }
    </style>
</head>

<body>

    <div class="header-row">
        <div class="company-info">
            <div class="company-name">Hệ Thống Gervin Wood<br>Quản Lý Kho Vật Tư</div>
        </div>

        <div class="title-block">
            <div class="dept">Bộ Phận Quản Lý Kho</div>
            <h2>{{ $title }}</h2>
            <div class="subtitle" style="margin-top:4px">
                Ngày {{ $record->date?->day ?? date('d') }}, tháng {{ $record->date?->month ?? date('m') }}, năm {{ $record->date?->year ?? date('Y') }}
            </div>
        </div>

        <div class="form-no">
            <div>Mẫu số 02 - VT</div>
            <div style="margin-top:6px">Số phiếu: <strong>{{ $record->voucher_no }}</strong></div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="field">
                <label>Họ tên {{ $isExport ? 'người nhận' : 'người giao' }}:</label>
                <span class="line" style="min-width:200px">{{ $isExport ? $record->receiver : $record->exporter }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="field">
                <label>Nội dung:</label>
                <span class="line">{{ $record->content }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:35px">STT</th>
                <th>Nội dung (Nhãn vật tư & kích cỡ)</th>
                <th style="width:60px">Số lượng</th>
                <th style="width:90px">Đơn giá (VNĐ)</th>
                <th style="width:100px">Thành tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalQty = 0;
                $totalVal = 0;
            @endphp
            @foreach ($items as $i => $item)
                @php
                    $content = '';
                    if (!empty($item['item_name'])) {
                        $content .= $item['item_name'];
                    }
                    if (!empty($item['group'])) {
                        $content .= ($content ? ' - ' : '') . $item['group'];
                        if (!empty($item['group_code'])) {
                            $content .= ' (' . $item['group_code'] . ')';
                        }
                    }
                    if (!empty($item['size'])) {
                        $content .= ' (' . $item['size'] . ')';
                        if (!empty($item['size_code'])) {
                            $content .= ' [' . $item['size_code'] . ']';
                        }
                    }
                    if (!$content) {
                        $content = $item['label'] ?? '';
                    }
                    $subTotal = $item['qty'] * $item['price'];
                    $totalQty += $item['qty'];
                    $totalVal += $subTotal;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align:left">{{ $content }}</td>
                    <td>{{ $item['qty'] }}</td>
                    <td class="text-right">{{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($subTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            {{-- Pad empty rows up to 6 rows to look professional --}}
            @for ($e = count($items); $e < 6; $e++)
                <tr>
                    <td>{{ $e + 1 }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor

            {{-- Summary Row --}}
            <tr class="font-bold">
                <td colspan="2" style="text-align:right">TỔNG CỘNG:</td>
                <td>{{ $totalQty }}</td>
                <td>—</td>
                <td class="text-right">{{ number_format($totalVal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="sig-row">
        <div class="sig-block">
            <div class="title">Người xuất/giao</div>
            <div class="sub">(Ký, họ tên)</div>
            <br>
            <br>
            <div class="sig-space">
                <div style="margin-top:10px; font-style:italic; font-size:11px">{{ $record->exporter }}</div>
            </div>
        </div>
        <div class="sig-block">
            <div class="title">Kế toán</div>
            <div class="sub">(Ký, họ tên)</div>
            <br>
            <br>
            <div class="sig-space"></div>
        </div>
        <div class="sig-block">
            <div class="title">Người nhận</div>
            <div class="sub">(Ký, họ tên)</div>
            <br>
            <br>
            <div class="sig-space">
                <div style="margin-top:10px; font-style:italic; font-size:11px">{{ $record->receiver }}</div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
