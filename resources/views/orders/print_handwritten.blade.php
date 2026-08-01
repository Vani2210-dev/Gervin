<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lệnh viết tay - {{ $order->order_code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #000000;
            line-height: 1.5;
        }

        .print-toolbar {
            padding: 10px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 6px 12px;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            background-color: #fff;
        }

        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header .date {
            margin-top: 5px;
            font-size: 14px;
            font-style: italic;
        }

        .info-section {
            margin-bottom: 25px;
            font-size: 15px;
        }

        .info-grid {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .info-col {
            flex: 1;
        }

        .info-row {
            margin-bottom: 6px;
            display: flex;
            align-items: flex-start;
        }

        .info-label {
            font-weight: bold;
            width: 150px;
            flex-shrink: 0;
        }

        .info-val {
            flex-grow: 1;
        }

        .items-section {
            margin-top: 30px;
        }

        .items-title {
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .item-card {
            border-bottom: 1px dashed #999;
            padding: 12px 0;
            font-size: 15px;
        }

        .item-card:last-child {
            border-bottom: none;
        }

        .item-header {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-details {
            margin-left: 20px;
        }

        .item-notes {
            margin-top: 5px;
            font-style: italic;
            color: #333;
        }

        .footer-signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            text-align: center;
            font-size: 15px;
        }

        .signature-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-title {
            font-weight: bold;
            font-size: 16px;
        }

        .signature-subtitle {
            font-style: italic;
            color: #555;
            font-size: 13px;
            margin-top: 2px;
        }

        .signature-space {
            height: 90px;
        }

        .stamp-box {
            border: 2px solid #dc3545;
            color: #dc3545;
            padding: 8px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            transform: rotate(-5deg);
            display: inline-block;
            margin-top: 15px;
        }

        @media print {
            .print-toolbar {
                display: none !important;
            }
            .container {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            body {
                background-color: #fff;
            }
        }
    </style>
</head>
<body>

    <div class="print-toolbar">
        <div>
            <button onclick="window.print()" class="btn btn-primary">In phiếu</button>
            <button onclick="window.close()" class="btn">Đóng</button>
        </div>
        <div style="font-size: 14px; font-weight: bold; color: #555;">
            Lệnh viết tay - {{ $order->order_code }}
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h1>LỆNH VIẾT TAY</h1>
            <div class="date">Ngày {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') : now()->format('d/m/Y') }}</div>
        </div>

        <div class="info-section">
            <div class="info-grid">
                <div class="info-col">
                    <div class="info-row">
                        <span class="info-label">Mã đơn hàng:</span>
                        <span class="info-val" style="font-weight: bold;">{{ $order->order_code }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Khách hàng:</span>
                        <span class="info-val">{{ $customer ? '[' . $customer->customer_code . '] ' . $customer->name : $order->customer_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số điện thoại:</span>
                        <span class="info-val">{{ $order->phone ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Địa chỉ giao:</span>
                        <span class="info-val">{{ $order->address ?? '—' }}</span>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <span class="info-label">Phân loại:</span>
                        <span class="info-val" style="font-weight: bold; text-transform: uppercase;">
                            {{ $order->type === 'min_late' ? 'Min/Late' : ($order->type === 'glass' ? 'Đơn kính' : 'Acrylic') }}
                            (Sửa tấm)
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Đơn gốc liên kết:</span>
                        <span class="info-val">
                            @if($order->parent)
                                {{ $order->parent->order_code }}
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày chốt đơn:</span>
                        <span class="info-val">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Hạn giao hàng:</span>
                        <span class="info-val" style="font-weight: bold; color: #dc3545;">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('H:i d/m/Y') : '—' }}</span>
                    </div>
                </div>
            </div>
            
            @if($order->notes)
                <div class="info-row-full" style="margin-top: 12px;">
                    <span class="info-label" style="display: block; width: auto; margin-bottom: 4px;">Ghi chú đơn hàng:</span>
                    <div style="font-style: italic; background-color: #f9f9f9; border: 1px solid #ddd; padding: 8px 12px; border-radius: 4px; font-size: 14px; color: #333;">
                        {{ $order->notes }}
                    </div>
                </div>
            @endif
        </div>

        <div class="items-section">
            <div class="items-title">DANH SÁCH CHI TIẾT SỬA TẤM</div>
            
            @php $stt = 1; @endphp
            @foreach($order->supplies as $supply)
                @php
                    if ($order->type === 'min_late') {
                        $items = $supply->minLateItems;
                    } elseif ($order->type === 'glass') {
                        $items = $supply->glassItems;
                    } else {
                        $items = $supply->items;
                    }
                @endphp
                @foreach($items as $item)
                    @php
                        $notes = $item->notes ?? '';
                        $oldSize = $item->old_size ?? '';
                        $userNotes = $notes;
                        
                        // Fallback: Regex to parse: [Sửa từ tấm {sourceInfo}, KT cũ: {oldSize}] {userNotes}
                        if (empty($oldSize) && preg_match('/\[Sửa từ tấm (.*?),\s*KT cũ:\s*([\d\sx\?\*]+)\](.*)/i', $notes, $matches)) {
                            $rawOldSize = trim($matches[2]);
                            $userNotes = trim($matches[3]);
                            
                            // Swap W x H to H x W (Cao x Rộng)
                            $parts = array_map('trim', explode('x', $rawOldSize));
                            if (count($parts) === 2) {
                                $oldSize = $parts[1] . ' x ' . $parts[0];
                            } else {
                                $oldSize = $rawOldSize;
                            }
                        }

                        // Determine new size
                        $newSize = '';
                        if ($order->type === 'min_late') {
                            $sizes = $item->size ?? [];
                            if (is_string($sizes)) {
                                $sizes = json_decode($sizes, true) ?? [];
                            }
                            $newSize = ($sizes['height'] ?? '—') . ' x ' . ($sizes['width'] ?? '—');
                            $qty = $item->quantity;
                        } elseif ($order->type === 'glass') {
                            $newSize = ($item->height ?? '—') . ' x ' . ($item->width ?? '—');
                            $qty = $item->wing_quantity;
                        } else {
                            $newSize = ($item->height ?? '—') . ' x ' . ($item->width ?? '—');
                            $qty = $item->quantity;
                        }

                        // Combine vát and notes for acrylic order type
                        $extraInfo = [];
                        if ($order->type === 'acrylic' && !empty($item->bevel) && $item->bevel !== '—') {
                            $extraInfo[] = 'vát ' . $item->bevel;
                        }
                        if ($userNotes) {
                            $extraInfo[] = $userNotes;
                        }
                        $extraText = !empty($extraInfo) ? ' (' . implode(', ', $extraInfo) . ')' : '';
                    @endphp

                    <div class="item-card">
                        <div class="item-header">
                            {{ $stt++ }}) {{ $item->product_name ?? $item->name }} 
                            @if($supply->supply_name)
                                <span style="font-weight: normal; font-size: 13px;">({{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name }})</span>
                            @endif
                        </div>
                        <div class="item-details">
                            @if($oldSize)
                                <div>- Kích thước cũ: <span style="text-decoration: line-through; color: #666;">{{ $oldSize }}</span></div>
                                <div>- Sửa thành kích thước: <strong>{{ $newSize }}</strong> = <strong>{{ $qty }}</strong> tấm{!! $extraText !!}</div>
                            @else
                                <div>- Sửa thành kích thước: <strong>{{ $newSize }}</strong> = <strong>{{ $qty }}</strong> tấm{!! $extraText !!}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        <div class="footer-signatures">
            <div class="signature-col">
                <span class="signature-title">Sales</span>
                <span class="signature-subtitle">(Ký & ghi rõ họ tên)</span>
                <div class="signature-space"></div>
            </div>
            <div class="signature-col">
                <span class="signature-title">Quản đốc</span>
                <span class="signature-subtitle">(Ký & ghi rõ họ tên)</span>
                <div class="signature-space"></div>
            </div>
            <div class="signature-col">
                <span class="signature-title">Kế toán</span>
                <span class="signature-subtitle">(Ký & ghi rõ họ tên)</span>
                <div class="signature-space"></div>
            </div>
        </div>
    </div>

</body>
</html>
