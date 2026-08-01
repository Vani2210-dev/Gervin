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
</style>

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-sm">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center gap-3">
                    <h5 class="text-lg font-bold text-neutral-800 m-0">Tiến độ lệnh sản xuất</h5>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Filter & Export Form --}}
                    <form method="GET" action="{{ route('manufactures.sequence') }}" class="flex items-center gap-2" id="filterForm">
                        <span class="text-sm font-medium text-neutral-500">Ngày:</span>
                        <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm border-neutral-200 rounded-lg w-auto py-1.5 px-3" onchange="this.form.submit()">
                    </form>

                    <a href="{{ route('manufactures.sequence.export', ['date' => $date]) }}" 
                        class="btn btn-success text-sm btn-sm px-4 py-2 flex items-center gap-1.5 rounded-lg shadow-sm font-semibold transition-all">
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
                                        <td class="text-center py-3 px-2 border border-neutral-200 font-semibold text-neutral-700">
                                            <span class="{{ $item['remainingCount'] > 0 ? 'text-neutral-900' : 'text-neutral-400' }}">
                                                {{ $item['remainingCount'] }}
                                            </span>
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

@endsection
