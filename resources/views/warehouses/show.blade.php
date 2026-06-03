@extends('layout.layout')
@php
    $title    = 'Chi tiết kho';
    $subTitle = $warehouse->name;
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        {{-- Header Card --}}
        <div class="card p-0 rounded-xl border-0 mb-6 bg-white shadow-sm">
            <div class="card-body p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-xl font-bold text-neutral-800">{{ $warehouse->name }}</h3>
                            <span class="bg-primary-50 text-primary-600 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                {{ $warehouse->item_name ?: 'Chưa cấu hình hàng hóa' }}
                            </span>
                        </div>
                        <p class="text-secondary-light text-sm">Theo dõi chi tiết lịch sử giao dịch nhập, xuất và tồn kho vật tư.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Xóa kho này sẽ mất vĩnh viễn toàn bộ lịch sử nhập xuất? Bạn có chắc chắn?')">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn border border-danger-300 text-danger-600 hover:bg-danger-600 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                                <iconify-icon icon="fluent:delete-24-regular" class="inline-block align-middle me-1 text-lg"></iconify-icon> Xóa kho
                            </button>
                        </form>
                        <button type="button" onclick="openModal('configWarehouseModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <iconify-icon icon="solar:settings-outline" class="inline-block align-middle me-1 text-lg"></iconify-icon> Cấu hình hàng hóa
                        </button>
                        @if($warehouse->item_name)
                            <button type="button" onclick="openModal('addRecordModal')" class="btn btn-primary px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-all hover:opacity-90">
                                <iconify-icon icon="ic:baseline-plus" class="inline-block align-middle me-1 text-lg"></iconify-icon> Thêm phiếu mới
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-6 bg-danger-100 border border-danger-300 text-danger-700 rounded-lg px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!$warehouse->item_name)
            <div class="bg-white rounded-2xl border border-neutral-100 shadow-sm p-12 text-center">
                <iconify-icon icon="solar:info-circle-outline" class="text-5xl text-primary-500 mb-4"></iconify-icon>
                <h5 class="text-neutral-800 font-bold mb-2 text-base">Kho chưa được cấu hình!</h5>
                <p class="text-secondary-light text-sm mb-6 max-w-md mx-auto">Vui lòng nhấn vào nút <strong>"Cấu hình hàng hóa"</strong> ở trên để thiết lập tên sản phẩm và các kích thước (size) cần quản lý.</p>
                <button type="button" onclick="openModal('configWarehouseModal')" class="btn btn-primary px-5 py-2.5 rounded-lg text-sm font-medium">
                    Cấu hình ngay &rarr;
                </button>
            </div>
        @else
            {{-- Main Warehouse Inventory Log --}}
            <div class="card p-0 rounded-xl border-0 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-neutral-200 py-4 px-6 flex flex-wrap items-center justify-between gap-4">
                    <h6 class="font-bold text-neutral-800 text-base mb-0">Bảng theo dõi xuất nhập tồn</h6>
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Search Input --}}
                        <div class="relative">
                            <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                            <input type="text" id="warehouseSearch" class="form-control form-control-sm rounded-lg pl-9 pr-4 py-2 border-neutral-200 text-xs w-64 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" 
                                placeholder="Tìm kiếm phiếu, nội dung..." onkeyup="filterWarehouseRecords()">
                        </div>
                        
                        {{-- Excel Actions --}}
                        <button class="bg-neutral-100 text-neutral-700 hover:bg-neutral-200 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5 transition-all" type="button" onclick="openModal('importWarehouseModal')">
                            <iconify-icon icon="solar:import-outline" class="text-base"></iconify-icon> Nhập Excel
                        </button>
                        
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="bg-neutral-100 text-neutral-700 hover:bg-neutral-200 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5 transition-all" type="button">
                                <iconify-icon icon="solar:download-outline" class="text-base"></iconify-icon> Xuất dữ liệu
                            </button>
                            <div x-show="open" class="origin-top-right absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20" style="display: none;">
                                <div class="py-1">
                                    <a class="flex items-center gap-2 px-4 py-2 text-xs text-neutral-700 hover:bg-neutral-100" href="{{ route('warehouses.export-template', $warehouse) }}">
                                        <iconify-icon icon="solar:file-text-outline" class="text-base text-info-500"></iconify-icon> Tải file mẫu
                                    </a>
                                    <hr class="border-neutral-100 my-1">
                                    <a class="flex items-center gap-2 px-4 py-2 text-xs text-neutral-700 hover:bg-neutral-100" href="{{ route('warehouses.export-data', $warehouse) }}">
                                        <iconify-icon icon="solar:file-excel-outline" class="text-base text-success-500"></iconify-icon> Export Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Area --}}
                <div class="card-body p-0">
                    <div class="overflow-x-auto w-full max-h-[70vh]">
                        <table class="w-full border-collapse border border-neutral-200 text-xs text-center min-w-[1600px]">
                            <thead>
                                {{-- Row 1: Headers --}}
                                <tr class="bg-primary-50 text-neutral-700 uppercase font-bold border-b border-neutral-300">
                                    <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-10">STT</th>
                                    <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-20">Số phiếu</th>
                                    <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-24">Ngày</th>
                                    <th rowspan="3" class="border border-neutral-200 px-3 py-3 w-48">Nội dung</th>
                                    
                                    @php $sizeCount = count($allSizes); @endphp
                                    <th colspan="{{ $sizeCount ?: 1 }}" class="border border-neutral-200 bg-success-50 text-success-700 px-2 py-1">NHẬP {{ $warehouse->item_name }}</th>
                                    <th colspan="{{ $sizeCount ?: 1 }}" class="border border-neutral-200 bg-danger-50 text-danger-700 px-2 py-1">XUẤT {{ $warehouse->item_name }}</th>
                                    <th colspan="{{ $sizeCount ?: 1 }}" class="border border-neutral-200 bg-warning-50 text-warning-700 px-2 py-1">TỒN {{ $warehouse->item_name }}</th>

                                    <th rowspan="3" class="border border-neutral-200 px-3 py-3 w-32">Người xuất/nhập</th>
                                    <th rowspan="3" class="border border-neutral-200 px-3 py-3 w-32">Người nhận</th>
                                    <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-20"><iconify-icon icon="solar:menu-dots-outline" class="text-base"></iconify-icon></th>
                                </tr>
                                
                                {{-- Row 2: Size Groups --}}
                                <tr class="bg-white border-b border-neutral-200">
                                    @foreach(['in', 'out', 'stock'] as $type)
                                        @php
                                            $groupThClass = 'bg-success-50 text-success-800';
                                            if ($type === 'out') $groupThClass = 'bg-danger-50 text-danger-800';
                                            if ($type === 'stock') $groupThClass = 'bg-warning-50 text-warning-800';
                                        @endphp
                                        @foreach($warehouse->sizes_config ?? [] as $group)
                                            <th colspan="{{ count($group['sizes']) ?: 1 }}" 
                                                rowspan="{{ count($group['sizes']) ? 1 : 2 }}"
                                                class="border border-neutral-200 py-1 font-bold text-[10px] {{ $groupThClass }}">
                                                <div>{{ $group['name'] }}{{ !empty($group['code']) ? ' (' . $group['code'] . ')' : '' }}</div>
                                                @if(!empty($group['price']) && $type === 'stock')
                                                    <div class="text-[9px] text-primary-600 font-semibold mt-0.5">({{ number_format($group['price'], 0, ',', '.') }}đ)</div>
                                                @endif
                                            </th>
                                        @endforeach
                                    @endforeach
                                </tr>
                                
                                {{-- Row 3: Sizes --}}
                                <tr class="border-b border-neutral-300">
                                    @foreach(['in', 'out', 'stock'] as $type)
                                         @php
                                             $sizeThClass = 'bg-success-50 bg-opacity-60 text-success-700';
                                             if ($type === 'out') $sizeThClass = 'bg-danger-50 bg-opacity-60 text-danger-700';
                                             if ($type === 'stock') $sizeThClass = 'bg-warning-50 bg-opacity-60 text-warning-700';
                                         @endphp
                                        @foreach($warehouse->sizes_config ?? [] as $group)
                                            @foreach($group['sizes'] as $size)
                                                @php
                                                    $sizePrice = !empty($size['price']) ? $size['price'] : (!empty($group['price']) ? $group['price'] : null);
                                                    $sizeName = is_array($size) ? ($size['name'] ?? '') : $size;
                                                    $sizeCode = is_array($size) ? ($size['code'] ?? '') : '';
                                                @endphp
                                                <th class="border border-neutral-200 py-1 font-semibold text-[9px] w-12 {{ $sizeThClass }}">
                                                    <div>{{ $sizeName }}{{ $sizeCode ? ' [' . $sizeCode . ']' : '' }}</div>
                                                    @if(!empty($sizePrice) && $type === 'stock')
                                                        <div class="text-[8px] text-primary-500 font-normal mt-0.5">({{ number_format($sizePrice, 0, ',', '.') }}đ)</div>
                                                    @endif
                                                </th>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </tr>
                                
                                {{-- Summary row (TỔNG CỘNG) at the TOP --}}
                                @if($records->count() > 0)
                                    <tr class="bg-neutral-100 font-bold border-b-2 border-primary-600 text-neutral-800">
                                        <td colspan="4" class="border border-neutral-200 py-2 px-3 text-right text-primary-700">TỔNG CỘNG:</td>
                                        
                                        {{-- Total In --}}
                                        @foreach($allSizes as $s)
                                            <td class="border border-neutral-200 bg-success-100 bg-opacity-60 text-success-800 py-2 font-bold">
                                                {{ number_format($records->sum(fn($r) => $r->in_data[$s['key']] ?? 0)) }}
                                            </td>
                                        @endforeach

                                        {{-- Total Out --}}
                                        @foreach($allSizes as $s)
                                            <td class="border border-neutral-200 bg-danger-100 bg-opacity-60 text-danger-800 py-2 font-bold">
                                                {{ number_format($records->sum(fn($r) => $r->out_data[$s['key']] ?? 0)) }}
                                            </td>
                                        @endforeach

                                        {{-- Current Stock (Latest record) --}}
                                        @php
                                            $latest = $records->first();
                                        @endphp
                                        @foreach($allSizes as $s)
                                            <td class="border border-neutral-200 text-warning-900 bg-warning-100 py-2 font-bold">
                                                {{ number_format($latest->stock_data[$s['key']] ?? 0) }}
                                            </td>
                                        @endforeach

                                        <td colspan="3" class="border border-neutral-200 bg-neutral-50"></td>
                                    </tr>
                                @endif
                            </thead>
                            <tbody id="warehouseTableBody" class="divide-y divide-neutral-100 bg-white">
                                @forelse($records as $index => $record)
                                    <tr class="hover:bg-primary-50 transition-colors">
                                        <td class="border border-neutral-200 font-bold text-neutral-500 py-2.5">{{ $records->count() - $index }}</td>
                                        <td class="border border-neutral-200 font-semibold text-neutral-800">{{ $record->voucher_no }}</td>
                                        <td class="border border-neutral-200 text-neutral-600">{{ $record->date ? $record->date->format('d/m/Y') : '-' }}</td>
                                        <td class="border border-neutral-200 text-left px-3 text-neutral-700 max-w-xs truncate" title="{{ $record->content }}">{{ $record->content }}</td>
                                        
                                        {{-- In Data --}}
                                        @foreach($allSizes as $s)
                                            <td class="border border-neutral-200 bg-success-50 bg-opacity-35 text-success-800 font-semibold">
                                                {{ $record->in_data[$s['key']] ?? '' }}
                                            </td>
                                        @endforeach
                                        
                                        {{-- Out Data --}}
                                        @foreach($allSizes as $s)
                                            <td class="border border-neutral-200 bg-danger-50 bg-opacity-35 text-danger-800 font-semibold">
                                                {{ $record->out_data[$s['key']] ?? '' }}
                                            </td>
                                        @endforeach
                                        
                                        {{-- Stock Data --}}
                                        @foreach($allSizes as $s)
                                            <td class="border border-neutral-200 bg-warning-50 bg-opacity-35 text-warning-900 font-bold">
                                                {{ $record->stock_data[$s['key']] ?? 0 }}
                                            </td>
                                        @endforeach

                                        <td class="border border-neutral-200 text-neutral-600">{{ $record->exporter }}</td>
                                        <td class="border border-neutral-200 text-neutral-600">{{ $record->receiver }}</td>
                                        <td class="border border-neutral-200 text-center">
                                            <div class="flex items-center gap-2 justify-center">
                                                <button type="button" class="text-warning-600 hover:text-warning-800 text-lg transition-colors p-1" title="Chỉnh sửa"
                                                    onclick='editRecord(@json($record))'>
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </button>
                                                @if((!empty($record->out_data) && collect($record->out_data)->filter()->sum() > 0) || (!empty($record->in_data) && collect($record->in_data)->filter()->sum() > 0))
                                                    <a href="{{ route('warehouses.records.print', [$warehouse, $record]) }}" target="_blank" class="text-info-600 hover:text-info-800 text-lg transition-colors p-1" title="In phiếu">
                                                        <iconify-icon icon="solar:printer-outline"></iconify-icon>
                                                    </a>
                                                @endif
                                                <form action="{{ route('warehouses.records.destroy', [$warehouse, $record]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu này? Hệ thống sẽ tự động tính lại tồn kho cho các phiếu sau.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-danger-600 hover:text-danger-800 text-lg transition-colors p-1" title="Xóa">
                                                        <iconify-icon icon="fluent:delete-24-regular"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 7 + (count($allSizes) * 3) }}" class="py-12 text-neutral-500 text-center font-medium bg-neutral-50">
                                            Chưa có dữ liệu giao dịch hóa đơn.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modals configuration and add/edit records --}}
@include('warehouses.partials.modals')
@if($warehouse->item_name)
    @include('warehouses.partials.import_modal')
@endif

<script>
    function filterWarehouseRecords() {
        const input = document.getElementById('warehouseSearch');
        const filter = input.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const tbody = document.getElementById('warehouseTableBody');
        if(!tbody) return;
        const rows = tbody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            if(cells.length < 2) continue; // Skip empty rows or loaders
            
            let found = false;
            // Track columns to search: Số phiếu (1), Nội dung (3)
            const searchCols = [1, 3];
            
            for (let j of searchCols) {
                if (cells[j]) {
                    const txtValue = cells[j].textContent || cells[j].innerText;
                    const normalizedValue = txtValue.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    if (normalizedValue.indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            rows[i].style.display = found ? "" : "none";
        }
    }

    function editRecord(record) {
        document.getElementById('editRecordForm').action = `/warehouses/{{ $warehouse->id }}/records/${record.id}`;

        // Fill meta data
        document.getElementById('edit_voucher_no').value = record.voucher_no || '';
        document.getElementById('edit_date').value = record.date ? record.date.split('T')[0] : '';
        document.getElementById('edit_content').value = record.content || '';
        document.getElementById('edit_exporter').value = record.exporter || '';
        document.getElementById('edit_receiver').value = record.receiver || '';

        // Set In/Out data
        const inData = record.in_data || {};
        const outData = record.out_data || {};

        // Clear values first
        const allInputs = document.querySelectorAll('#editRecordModal input[type="number"]');
        allInputs.forEach(input => {
            input.value = '';
        });

        for (const key in inData) {
            const input = document.getElementById(`edit_in_${key}`);
            if (input) input.value = inData[key] || '';
        }
        for (const key in outData) {
            const input = document.getElementById(`edit_out_${key}`);
            if (input) input.value = outData[key] || '';
        }

        // Show Modal
        openModal('editRecordModal');
    }
</script>

@endsection
