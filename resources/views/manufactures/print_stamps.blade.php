<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Phân Dán Tem - {{ $manufacture->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white;
                color: black;
                margin: 0;
                padding: 10mm;
            }
            .no-print {
                display: none !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-6">

    {{-- Controls --}}
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-gray-800 font-sans">In Phiếu Phân Dán Tem</h1>
            <p class="text-sm text-gray-500 font-sans">
                Mã lệnh: <span class="font-semibold">{{ $manufacture->code }}</span> — 
                Tổng cộng: <span class="font-semibold text-indigo-600">{{ $items->count() }}</span> tấm
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow flex items-center gap-1.5 transition-all font-sans">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                In Phiếu Ngay
            </button>
            <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-4 py-2 rounded-lg text-sm transition-all font-sans">
                Đóng lại
            </button>
        </div>
    </div>

    {{-- Main Sheet --}}
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200 print:border-0 print:shadow-none print:p-0">
        {{-- Header of Sheet --}}
        <div class="flex items-center justify-between border-b-2 border-gray-900 pb-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 uppercase tracking-wide">Phiếu Phân Dán Tem Sản Xuất</h1>
                <p class="text-xs text-gray-500 mt-1">Đơn vị gia công: <span class="font-bold text-gray-800">KB TECH (GERVIN)</span></p>
            </div>
            <div class="text-right">
                <div class="text-sm font-bold text-gray-900">Mã Lệnh: <span class="text-indigo-600">{{ $manufacture->code }}</span></div>
                <div class="text-xs text-gray-500 mt-0.5">Ngày in: {{ date('d/m/Y H:i') }}</div>
            </div>
        </div>

        {{-- Meta Information --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 bg-gray-50 p-4 rounded-lg print:bg-neutral-50 print:border print:border-gray-200">
            <div>
                <span class="block text-[10px] uppercase font-bold text-gray-400">Trạng thái lệnh</span>
                <span class="text-sm font-semibold text-gray-800 text-indigo-600 uppercase">{{ $manufacture->status }}</span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-bold text-gray-400">Tổng số tấm</span>
                <span class="text-sm font-bold text-indigo-600 text-base" id="printTotalCount">{{ $items->count() }}</span>
            </div>
            <div class="col-span-2">
                <span class="block text-[10px] uppercase font-bold text-gray-400">Ghi chú lệnh</span>
                <span class="text-xs text-gray-700 block italic leading-snug">{{ $manufacture->notes ?: 'Không có ghi chú' }}</span>
            </div>
        </div>

        {{-- Phân phát tem cho nhân viên --}}
        @if($manufacture->stampDistributions->count() > 0)
        <div class="mb-6 bg-gray-50 p-4 rounded-lg print:bg-neutral-50 print:border print:border-gray-200 text-xs">
            <span class="block text-[10px] uppercase font-bold text-gray-400 mb-2">Phân phát tem cho nhân viên</span>
            <div class="flex flex-wrap gap-4">
                @foreach($manufacture->stampDistributions as $dist)
                <div class="bg-white px-3 py-1.5 rounded border border-gray-200 flex items-center gap-1.5 shadow-sm">
                    <span class="font-semibold text-gray-700">{{ $dist->worker->name ?? 'Nhân viên' }}:</span>
                    <span class="font-bold text-indigo-600">{{ $dist->quantity }} tem</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Table List of Pieces --}}
        <table class="w-full border-collapse border border-gray-300 text-xs">
            <thead>
                <tr class="bg-gray-100 print:bg-neutral-100 text-gray-800">
                    <th class="border border-gray-300 px-2 py-2 text-center font-bold" style="width: 40px;">STT</th>
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold" style="width: 180px;">Mã Tấm (Mã SP)</th>
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold">Tên Tấm / Sản phẩm</th>
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold" style="width: 140px;">Vật tư</th>
                    <th class="border border-gray-300 px-3 py-2 text-center font-bold" style="width: 100px;">Kích thước (mm)</th>
                    <th class="border border-gray-300 px-2 py-2 text-center font-bold" style="width: 60px;">Tem số</th>
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold">Ghi chú</th>
                    <th class="border border-gray-300 px-2 py-2 text-center font-bold" style="width: 80px;">Đã dán</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                    @php
                        $totalQty = 1;
                        $currentIdx = 1;
                        if (isset($item->raw_item)) {
                            $totalQty = $item->raw_item->quantity ?? $item->raw_item->wing_quantity ?? 1;
                        }
                        $parts = explode('.', $item->product_code);
                        if (count($parts) > 0) {
                            $lastPart = end($parts);
                            if (is_numeric($lastPart)) {
                                $currentIdx = intval($lastPart);
                            }
                        }
                    @endphp
                    <tr class="stamp-row hover:bg-gray-50/50 print:hover:bg-transparent">
                        <td class="border border-gray-300 px-2 py-2 text-center font-medium">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-3 py-2 font-mono font-bold text-gray-900 select-all">{{ $item->product_code }}</td>
                        <td class="border border-gray-300 px-3 py-2">
                            <span class="font-semibold text-gray-800">{{ $item->product_name }}</span>
                            <span class="block text-[10px] text-gray-400">Đơn hàng: {{ $item->order_code }}</span>
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-gray-700">{{ $item->supply_name ?? '—' }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center font-bold text-red-600">{{ $item->dimensions ?: '—' }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-gray-500 font-semibold">{{ $currentIdx }}/{{ $totalQty }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-gray-500 italic">{{ $item->notes ?? '—' }}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center">
                            {{-- Checkbox for physical matching --}}
                            <div class="w-5 h-5 mx-auto border-2 border-gray-400 rounded flex items-center justify-center print:border-gray-900">
                                {{-- Empty block for checkmark signature --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                            Không có sản phẩm nào trong lệnh sản xuất này.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Signatures --}}
        <div class="grid grid-cols-3 gap-6 mt-12 text-center text-xs">
            <div>
                <p class="font-bold text-gray-700">Người lập phiếu</p>
                <p class="text-[10px] text-gray-400 mt-0.5">(Ký, ghi rõ họ tên)</p>
                <div class="h-20 flex items-center justify-center"></div>
                <p class="font-semibold text-gray-800">{{ Auth::user()->name ?? '—' }}</p>
            </div>
            <div>
                <p class="font-bold text-gray-700">Nhân viên dán tem</p>
                <p class="text-[10px] text-gray-400 mt-0.5">(Ký, ghi rõ họ tên)</p>
                <div class="h-20 flex items-center justify-center font-bold text-gray-800"></div>
                <p id="assignedWorkerSignatureName" class="text-gray-400">............................</p>
            </div>
            <div>
                <p class="font-bold text-gray-700">Quản đốc xưởng</p>
                <p class="text-[10px] text-gray-400 mt-0.5">(Ký, ghi rõ họ tên)</p>
                <div class="h-20"></div>
                <p class="text-gray-400">............................</p>
            </div>
        </div>
    </div>

</body>
</html>
