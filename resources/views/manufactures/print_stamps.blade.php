<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Tem QR - {{ $manufacture->code }}</title>
    <!-- Include basic tailwind or styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white;
                color: black;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-page-break {
                page-break-after: always;
            }
            .stamp-card {
                page-break-inside: avoid;
                border: 1.5px solid #000 !important;
                margin-bottom: 10px;
            }
        }
        /* Style for thermal tag/label printers */
        .stamp-card {
            width: 100%;
            max-width: 400px;
            height: 180px;
            border: 1px solid #e2e8f0;
            padding: 8px;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 6px;
            font-family: system-ui, -apple-system, sans-serif;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    {{-- Controls --}}
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-800">In Tem Lệnh Sản Xuất</h1>
            <p class="text-sm text-gray-500">Mã lệnh: <span class="font-semibold">{{ $manufacture->code }}</span> — Tổng số tem: <span class="font-semibold">{{ $items->sum('quantity') }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow flex items-center gap-1.5 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                In Tem Ngay
            </button>
            <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-4 py-2 rounded-lg text-sm transition-all">
                Đóng lại
            </button>
        </div>
    </div>

    {{-- Stamps Grid --}}
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-4 justify-items-center">
        @foreach($items as $item)
            @for($q = 0; $q < $item->quantity; $q++)
            <div class="stamp-card shadow-sm border border-gray-200">
                {{-- QR code side --}}
                <div class="w-1/3 flex flex-col items-center justify-center border-r border-dashed border-gray-300 pr-3 mr-3 h-full">
                    @if($item->product_code)
                        <img src="{{ route('manufactures.qr', $item->product_code) }}" class="w-24 h-24 object-contain">
                    @else
                        <div class="w-20 h-20 bg-gray-100 flex items-center justify-center text-xs text-gray-400">No QR</div>
                    @endif
                    <span class="text-[8px] font-bold text-gray-500 mt-1 select-all">{{ $item->product_code }}</span>
                </div>
                {{-- Metadata side --}}
                <div class="w-2/3 flex flex-col justify-between h-full py-0.5 leading-tight">
                    <div>
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wide bg-indigo-50 px-1.5 py-0.5 rounded">{{ $manufacture->code }}</span>
                            <span class="text-[9px] font-semibold text-gray-400">Tem: {{ $q + 1 }}/{{ $item->quantity }}</span>
                        </div>
                        <h2 class="text-xs font-bold text-gray-800 mt-1 line-clamp-2" title="{{ $item->product_name }}">{{ $item->product_name }}</h2>
                    </div>
                    
                    <div class="text-[10px] text-gray-600 space-y-0.5">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-400">Vật tư:</span>
                            <span class="font-semibold text-gray-700 truncate max-w-[150px]">{{ $item->supply_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-400">Kích thước:</span>
                            <span class="font-bold text-red-600">{{ $item->dimensions ?: '—' }} mm</span>
                        </div>
                        @if($item->notes)
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-400">Ghi chú:</span>
                            <span class="font-semibold text-gray-700 truncate max-w-[150px]">{{ $item->notes }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="border-t border-gray-100 pt-1 flex justify-between items-center text-[9px] text-gray-400">
                        <span>Đơn: {{ $item->order_code }}</span>
                        <span class="font-bold text-gray-800">KB TECH</span>
                    </div>
                </div>
            </div>
            @endfor
        @endforeach
    </div>

</body>
</html>
