<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Phiếu Chuyển Kho - {{ $transfer->code }}</title>
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
        .signatures { display: grid; grid-template-columns: repeat(5, 1fr); text-align: center; margin-top: 40px; page-break-inside: avoid; }
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
        <button onclick="window.print()" style="padding: 8px 16px; background: #9333ea; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            In phiếu chuyển kho (Ctrl + P)
        </button>
    </div>

    <div class="header">
        <div class="company-info">
            <h3>CÔNG TY CỔ PHẦN GERVIN VIỆT NAM</h3>
            <p>Địa chỉ: KCN Bình Xuyên, Vĩnh Phúc</p>
            <p>Điện thoại: 0988.xxx.xxx - Email: contact@gervin.vn</p>
        </div>
        <div style="text-align: right; font-size: 12px;">
            <p style="margin: 0;"><strong>Mẫu số: 03-VT</strong></p>
            <p style="margin: 2px 0; font-style: italic;">(Phiếu xuất kho kiêm vận chuyển nội bộ)</p>
            <p style="margin: 4px 0 0 0;">Số phiếu: <strong>{{ $transfer->code }}</strong></p>
        </div>
    </div>

    <div class="voucher-title">
        <h2>PHIẾU CHUYỂN KHO NỘI BỘ</h2>
        <p>Ngày {{ $transfer->date ? $transfer->date->format('d') : '...' }} tháng {{ $transfer->date ? $transfer->date->format('m') : '...' }} năm {{ $transfer->date ? $transfer->date->format('Y') : '....' }}</p>
    </div>

    <div class="meta-grid">
        <div>Kho xuất (Nguồn): <strong>{{ $transfer->fromWarehouse->name ?? 'Kho xuất' }}</strong></div>
        <div>Kho nhận (Đích): <strong>{{ $transfer->toWarehouse->name ?? 'Kho nhận' }}</strong></div>
        <div>Người lập / giao hàng: <strong>{{ $transfer->deliverer ?: ($transfer->creator->name ?? '—') }}</strong></div>
        <div>Người nhận tại kho đích: <strong>{{ $transfer->receiver ?: 'Thủ kho' }}</strong></div>
        <div style="grid-column: span 2;">Lý do điều chuyển: <strong>{{ $transfer->notes ?: 'Điều chuyển hàng hóa nội bộ Gervin' }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 35px;">STT</th>
                <th style="width: 120px;">Mã SKU</th>
                <th>Tên nhãn hiệu, quy cách vật tư</th>
                <th style="width: 80px;">Kích thước</th>
                <th style="width: 50px;">ĐVT</th>
                <th style="width: 90px;">Số lượng chuyển</th>
                <th style="width: 140px;">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item->code ?: ($item->fromMaterial->code ?? '—') }}</td>
                    <td>{{ $item->name ?: ($item->fromMaterial->name ?? '—') }}</td>
                    <td class="text-center">{{ $item->dimensions }}</td>
                    <td class="text-center">{{ $item->unit ?: ($item->fromMaterial->unit ?? 'Tấm') }}</td>
                    <td class="text-right font-bold">{{ floatval($item->quantity) == intval($item->quantity) ? number_format($item->quantity, 0, ',', '.') : number_format($item->quantity, 2, ',', '.') }}</td>
                    <td>{{ $item->notes ?: '' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #fdfdfd;">
                <td colspan="5" class="text-right" style="text-transform: uppercase;">Tổng cộng:</td>
                <td class="text-right">{{ floatval($transfer->total_quantity) == intval($transfer->total_quantity) ? number_format($transfer->total_quantity, 0, ',', '.') : number_format($transfer->total_quantity, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="sig-block">
            <p class="font-bold">Người lập phiếu</p>
            <p style="font-style: italic; font-size: 11px;">(Ký, họ tên)</p>
            <div class="sig-space"></div>
            <p>{{ $transfer->creator->name ?? 'Người lập' }}</p>
        </div>
        <div class="sig-block">
            <p class="font-bold">Người vận chuyển</p>
            <p style="font-style: italic; font-size: 11px;">(Ký, họ tên)</p>
            <div class="sig-space"></div>
            <p>{{ $transfer->deliverer ?: '.......................' }}</p>
        </div>
        <div class="sig-block">
            <p class="font-bold">Thủ kho xuất</p>
            <p style="font-style: italic; font-size: 11px;">(Ký, họ tên)</p>
            <div class="sig-space"></div>
            <p>.......................</p>
        </div>
        <div class="sig-block">
            <p class="font-bold">Thủ kho nhận</p>
            <p style="font-style: italic; font-size: 11px;">(Ký, họ tên)</p>
            <div class="sig-space"></div>
            <p>{{ $transfer->receiver ?: '.......................' }}</p>
        </div>
        <div class="sig-block">
            <p class="font-bold">Kế toán / Giám đốc</p>
            <p style="font-style: italic; font-size: 11px;">(Ký, họ tên)</p>
            <div class="sig-space"></div>
            <p>.......................</p>
        </div>
    </div>
</body>
</html>
