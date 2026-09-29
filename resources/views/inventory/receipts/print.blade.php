<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Phiếu Nhập Kho - {{ $receipt->code }}</title>
    <style>
        body { font-family: "DejaVu Sans", "Times New Roman", serif; font-size: 13px; color: #111; margin: 0; padding: 25px; line-height: 1.4; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .company-info h3 { margin: 0 0 5px 0; font-size: 16px; text-transform: uppercase; }
        .company-info p { margin: 2px 0; font-size: 12px; color: #444; }
        .voucher-title { text-align: center; margin: 20px 0 25px 0; }
        .voucher-title h2 { margin: 0 0 5px 0; font-size: 20px; text-transform: uppercase; font-weight: bold; }
        .voucher-title p { margin: 0; font-style: italic; color: #555; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 30px; margin-bottom: 20px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th, td { border: 1px solid #333; padding: 6px 8px; font-size: 12px; }
        th { background: #f0f0f0; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .signatures { display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; margin-top: 40px; page-break-inside: avoid; }
        .sig-block p { margin: 3px 0; }
        .sig-space { height: 75px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            In phiếu (Ctrl + P)
        </button>
    </div>

    <div class="header">
        <div class="company-info">
            <h3>CÔNG TY CỔ PHẦN GERVIN VIỆT NAM</h3>
            <p>Địa chỉ: KCN Bình Xuyên, Vĩnh Phúc</p>
            <p>Điện thoại: 0988.xxx.xxx - Email: contact@gervin.vn</p>
        </div>
        <div style="text-align: right; font-size: 12px;">
            <p style="margin: 0;"><strong>Mẫu số: 01-VT</strong></p>
            <p style="margin: 2px 0; font-style: italic;">(Ban hành theo TT 200/2014/TT-BTC)</p>
            <p style="margin: 4px 0 0 0;">Số phiếu: <strong>{{ $receipt->code }}</strong></p>
        </div>
    </div>

    <div class="voucher-title">
        <h2>PHIẾU NHẬP KHO</h2>
        <p>Ngày {{ $receipt->date ? $receipt->date->format('d') : '...' }} tháng {{ $receipt->date ? $receipt->date->format('m') : '...' }} năm {{ $receipt->date ? $receipt->date->format('Y') : '....' }}</p>
    </div>

    <div class="meta-grid">
        <div>Họ tên người giao: <strong>{{ $receipt->deliverer ?: '...................................................' }}</strong></div>
        <div>Nhập tại kho: <strong>{{ $receipt->warehouse->name ?? 'Kho chính' }}</strong></div>
        <div>Theo chứng từ / HĐ: <strong>{{ $receipt->notes ?: 'Nhập theo kế hoạch' }}</strong></div>
        <div>Địa điểm kho: <strong>{{ $receipt->warehouse->address ?? 'Nhà máy Gervin' }}</strong></div>
        <div>Nhà cung cấp / Nguồn: <strong>{{ $receipt->supplier_name ?: 'NCC Trung Quốc' }}</strong></div>
        <div>Người nhận hàng: <strong>{{ $receipt->receiver ?: 'Thủ kho' }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 35px;">STT</th>
                <th style="width: 110px;">Mã SKU</th>
                <th>Tên nhãn hiệu, quy cách vật tư</th>
                <th style="width: 50px;">ĐVT</th>
                <th style="width: 80px;">Số lượng</th>
                <th style="width: 90px;">Đơn giá (đ)</th>
                <th style="width: 100px;">Thành tiền (đ)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $idx => $it)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $it->material->code ?? '-' }}</td>
                    <td>{{ $it->material->name ?? '-' }} ({{ $it->material->origin_code ?: 'QC' }})</td>
                    <td class="text-center">{{ $it->material->unit ?? 'Tấm' }}</td>
                    <td class="text-right font-bold">{{ fmod($it->quantity, 1) == 0 ? number_format($it->quantity, 0, ',', '.') : number_format($it->quantity, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $it->unit_price > 0 ? number_format($it->unit_price, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold">{{ $it->total_price > 0 ? number_format($it->total_price, 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
            <tr class="font-bold">
                <td colspan="4" class="text-center">TỔNG CỘNG</td>
                <td class="text-right">{{ fmod($receipt->total_quantity, 1) == 0 ? number_format($receipt->total_quantity, 0, ',', '.') : number_format($receipt->total_quantity, 2, ',', '.') }}</td>
                <td></td>
                <td class="text-right">{{ $receipt->total_amount > 0 ? number_format($receipt->total_amount, 0, ',', '.') . 'đ' : '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-block">
            <p class="font-bold">Người lập phiếu</p>
            <p style="font-size: 11px; color: #666;">(Ký, ghi rõ họ tên)</p>
            <div class="sig-space"></div>
            <p class="font-bold">{{ $receipt->creator->name ?? 'Người lập' }}</p>
        </div>
        <div class="sig-block">
            <p class="font-bold">Người giao hàng</p>
            <p style="font-size: 11px; color: #666;">(Ký, ghi rõ họ tên)</p>
            <div class="sig-space"></div>
            <p class="font-bold">{{ $receipt->deliverer ?: '.......................' }}</p>
        </div>
        <div class="sig-block">
            <p class="font-bold">Thủ kho</p>
            <p style="font-size: 11px; color: #666;">(Ký, ghi rõ họ tên)</p>
            <div class="sig-space"></div>
            <p class="font-bold">{{ $receipt->receiver ?: 'Thủ kho' }}</p>
        </div>
        <div class="sig-block">
            <p class="font-bold">Kế toán trưởng</p>
            <p style="font-size: 11px; color: #666;">(Ký, ghi rõ họ tên)</p>
            <div class="sig-space"></div>
            <p class="font-bold">.......................</p>
        </div>
    </div>
</body>
</html>
