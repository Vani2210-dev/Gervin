@extends('layout.layout')
@php
    $title    = 'Sắp xếp đơn hàng';
    $subTitle = 'Theo dõi tiến độ tổng quan và sắp xếp đơn hàng';
@endphp

@section('content')

<style>
    .bg-gray-light {
        background-color: #f8f9fa !important;
    }
    .bg-grand-total {
        background-color: #fffbeb !important;
    }
</style>

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-sm">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                {{-- Bên trái: Ô tìm kiếm thay thế cho tiêu đề --}}
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('manufactures.sequence') }}" class="navbar-search">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="completion_status" value="{{ $completionStatus }}">
                        <div class="relative">
                            <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                            <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-64 md:w-80" 
                                placeholder="Tìm khách hàng, số phiếu, mã màu..." value="{{ $search }}">
                        </div>
                    </form>
                </div>

                {{-- Bên phải: Nút Lọc (mở modal), Nút Xóa lọc, Nút Xuất Excel --}}
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('sequence-filter-modal')"
                        class="btn bg-light-600 text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-2 font-medium hover:bg-neutral-100 transition-all">
                        <iconify-icon icon="solar:filter-outline" class="text-xl leading-none"></iconify-icon>
                        Lọc
                    </button>

                    @if(!empty($completionStatus) || !empty($date))
                    <a href="{{ route('manufactures.sequence', ['search' => $search]) }}" class="btn text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 text-danger-600 hover:bg-danger-50 transition-all font-medium" title="Xóa bộ lọc">
                        <iconify-icon icon="solar:close-circle-outline" class="text-xl leading-none"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif

                    <a href="{{ route('manufactures.sequence.export', ['date' => $date, 'search' => $search, 'completion_status' => $completionStatus]) }}" 
                        class="btn btn-sm bg-success-600 hover:bg-success-700 text-white px-4 py-2 flex items-center gap-1.5 rounded-lg shadow-sm font-semibold transition-all">
                        <iconify-icon icon="lucide:file-spreadsheet" class="text-lg"></iconify-icon>
                        Xuất Excel
                    </a>
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="alert alert-success bg-success-50 text-success-600 border border-success-200 rounded-lg p-4 mx-6 mt-4">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-4 mx-6 mt-4">
                {{ session('error') }}
            </div>
            @endif

            {{-- Table --}}
            <div class="card-body p-6">
                <div class="table-responsive scroll-sm overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 w-full min-w-[1600px] border-collapse text-xs">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700">
                                <th scope="col" class="w-12 text-center py-3 px-2 border border-neutral-200 font-bold">STT</th>
                                <th scope="col" class="text-left py-3 px-3 border border-neutral-200 font-bold">Khách hàng</th>
                                <th scope="col" class="w-28 text-center py-3 px-2 border border-neutral-200 font-bold">Số phiếu</th>
                                <th scope="col" class="text-left py-3 px-3 border border-neutral-200 font-bold">Mã màu</th>
                                <th scope="col" class="w-20 text-center py-3 px-2 border border-neutral-200 font-bold">Tổng tấm</th>
                                <th scope="col" class="w-20 text-center py-3 px-2 border border-neutral-200 font-bold">Kính/CNC</th>
                                <th scope="col" class="w-32 text-center py-3 px-2 border border-neutral-200 font-bold bg-amber-50/50">Số tấm đã xong ngày 1</th>
                                <th scope="col" class="w-32 text-center py-3 px-2 border border-neutral-200 font-bold bg-blue-50/50">Số tấm đang sản xuất</th>
                                <th scope="col" class="w-32 text-center py-3 px-2 border border-neutral-200 font-bold bg-success-50/50">Số tấm đã xong ngày 2</th>
                                <th scope="col" class="w-24 text-center py-3 px-2 border border-neutral-200 font-bold">Còn lại</th>
                                <th scope="col" class="w-36 text-center py-3 px-2 border border-neutral-200 font-bold">Thời gian chốt</th>
                                <th scope="col" class="w-20 text-center py-3 px-2 border border-neutral-200 font-bold">Ngày hẹn</th>
                                <th scope="col" class="w-36 text-center py-3 px-2 border border-neutral-200 font-bold">Hạn hoàn thành</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandTotalPlates = 0;
                                $grandTotalCnc = 0;
                                $grandTotalDay1 = 0;
                                $grandTotalInProd = 0;
                                $grandTotalDay2 = 0;
                                $grandTotalRemaining = 0;

                                foreach ($groupedSupplies as $dateGroup) {
                                    foreach ($dateGroup['supplies'] as $item) {
                                        $grandTotalPlates += $item['totalCount'];
                                        $grandTotalCnc += $item['cncCount'];
                                        $grandTotalDay1 += $item['day1Count'];
                                        $grandTotalInProd += $item['inProductionCount'];
                                        $grandTotalDay2 += $item['day2Count'];
                                        $grandTotalRemaining += $item['remainingCount'];
                                    }
                                }
                            @endphp

                            @if(count($groupedSupplies) > 0)
                            <tr class="bg-grand-total font-bold border-b border-neutral-200 text-neutral-800 text-xs">
                                <td colspan="4" class="text-left py-2.5 px-3 border border-neutral-200">
                                    Tổng cộng
                                </td>
                                <td class="text-center py-2.5 px-2 border border-neutral-200">
                                    {{ $grandTotalPlates }}
                                </td>
                                <td class="text-center py-2.5 px-2 border border-neutral-200">
                                    {{ $grandTotalCnc }}
                                </td>
                                <td class="text-center py-2.5 px-2 border border-neutral-200 bg-amber-50/40 text-amber-800">
                                    {{ $grandTotalDay1 }}
                                </td>
                                <td class="text-center py-2.5 px-2 border border-neutral-200 bg-blue-50/40 text-blue-800">
                                    {{ $grandTotalInProd }}
                                </td>
                                <td class="text-center py-2.5 px-2 border border-neutral-200 bg-success-50/40 text-success-800">
                                    {{ $grandTotalDay2 }}
                                </td>
                                <td class="text-center py-2.5 px-2 border border-neutral-200 text-neutral-800">
                                    {{ $grandTotalRemaining }}
                                </td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200 bg-grand-total"></td>
                            </tr>
                            @endif

                            @forelse($groupedSupplies as $dateGroup)
                                @php
                                    $supplies = $dateGroup['supplies'];
                                    $totalPlates = 0;
                                    $totalCnc = 0;
                                    $totalDay1 = 0;
                                    $totalInProd = 0;
                                    $totalDay2 = 0;
                                    $totalRemaining = 0;
                                @endphp
                                {{-- Tiêu đề ngày --}}
                                <tr class="bg-neutral-100 font-bold text-neutral-800">
                                    <td colspan="13" class="py-2.5 px-4 border border-neutral-200 text-left text-sm uppercase tracking-wider bg-neutral-100">
                                        DANH SÁCH LÀM ĐẸP NGÀY {{ date('d/m/Y', strtotime($dateGroup['date'])) }}
                                    </td>
                                </tr>

                                @foreach($supplies as $itemIndex => $item)
                                    @php
                                        $order = $item['order'];
                                        $supply = $item['supply'];

                                        $totalPlates += $item['totalCount'];
                                        $totalCnc += $item['cncCount'];
                                        $totalDay1 += $item['day1Count'];
                                        $totalInProd += $item['inProductionCount'];
                                        $totalDay2 += $item['day2Count'];
                                        $totalRemaining += $item['remainingCount'];
                                    @endphp
                                    <tr class="hover:bg-neutral-50/50 border-b border-neutral-200">
                                        <td class="text-center py-3 px-2 border border-neutral-200 font-semibold text-neutral-500">
                                            {{ $itemIndex + 1 }}
                                        </td>
                                        <td class="py-3 px-3 border border-neutral-200 font-medium text-neutral-800 text-left">
                                            {{ $order->customer_name }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200">
                                            <a href="{{ route('orders.show', $order->id) }}" class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                                {{ $order->order_code }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-3 border border-neutral-200 text-left text-neutral-600">
                                            {{ $supply->order_supply_code ?? '—' }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 font-semibold text-neutral-700">
                                            {{ $item['totalCount'] }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 text-neutral-600">
                                            {{ $item['cncCount'] ?: '—' }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 font-semibold text-amber-700 bg-amber-50/20">
                                            {{ $item['day1Count'] }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 font-semibold text-blue-700 bg-blue-50/20">
                                            {{ $item['inProductionCount'] }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 font-semibold text-success-700 bg-success-50/20">
                                            {{ $item['day2Count'] }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 font-semibold text-neutral-800">
                                            {{ $item['remainingCount'] }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 text-neutral-500">
                                            {{ $order->order_date ? $order->order_date->format('Y-m-d H:i') : '—' }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 text-neutral-600 font-medium">
                                            {{ $order->delivery_days ?? '—' }}
                                        </td>
                                        <td class="text-center py-3 px-2 border border-neutral-200 text-neutral-500">
                                            {{ $order->deadline ? $order->deadline->format('Y-m-d H:i') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Dòng tổng cộng cho ngày --}}
                                <tr class="bg-neutral-50 font-bold border-t border-neutral-300">
                                    <td colspan="4" class="text-left py-3 px-3 border border-neutral-200 text-neutral-800 bg-neutral-100/30">
                                        Tổng cộng ngày {{ date('d/m/Y', strtotime($dateGroup['date'])) }}
                                    </td>
                                    <td class="text-center py-3 px-2 border border-neutral-200 text-neutral-800">
                                        {{ $totalPlates }}
                                    </td>
                                    <td class="text-center py-3 px-2 border border-neutral-200 text-neutral-800">
                                        {{ $totalCnc }}
                                    </td>
                                    <td class="text-center py-3 px-2 border border-neutral-200 text-amber-800 bg-amber-50/35">
                                        {{ $totalDay1 }}
                                    </td>
                                    <td class="text-center py-3 px-2 border border-neutral-200 text-blue-800 bg-blue-50/35">
                                        {{ $totalInProd }}
                                    </td>
                                    <td class="text-center py-3 px-2 border border-neutral-200 text-success-800 bg-success-50/35">
                                        {{ $totalDay2 }}
                                    </td>
                                    <td class="text-center py-3 px-2 border border-neutral-200 text-neutral-800">
                                        {{ $totalRemaining }}
                                    </td>
                                    <td class="border border-neutral-200"></td>
                                    <td class="border border-neutral-200"></td>
                                    <td class="border border-neutral-200 bg-gray-light"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-neutral-400 py-8 border border-neutral-200">
                                        Không có lệnh sản xuất nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Bộ Lọc Tiến Độ --}}
<x-modal name="sequence-filter-modal" maxWidth="md">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base text-neutral-800 m-0">Bộ lọc tiến độ sản xuất</h5>
        <button type="button" onclick="closeModal('sequence-filter-modal')" class="text-secondary-light hover:text-neutral-700 text-2xl leading-none">&times;</button>
    </div>
    <form action="{{ route('manufactures.sequence') }}" method="GET">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="p-6 space-y-4">
            {{-- Trạng thái hoàn thành --}}
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-700 mb-1.5 block">Trạng thái hoàn thành</label>
                <select name="completion_status" class="form-select w-full border-neutral-200 rounded-lg py-2 px-3 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Tất cả</option>
                    <option value="completed" {{ ($completionStatus ?? '') === 'completed' ? 'selected' : '' }}>Đã xong</option>
                </select>
            </div>

            {{-- Ngày làm đẹp / chốt đơn --}}
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-700 mb-1.5 block">Ngày làm đẹp / chốt đơn</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control w-full border-neutral-200 rounded-lg py-2 px-3 text-sm focus:border-primary-500 focus:ring-primary-500">
                <p class="text-xs text-neutral-400 mt-1">Để trống nếu muốn xem toàn bộ các ngày</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('sequence-filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg text-sm">Hủy</button>
        </div>
    </form>
</x-modal>

@endsection
