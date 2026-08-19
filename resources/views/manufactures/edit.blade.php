@extends('layout.layout')
@php
    $title    = 'Chỉnh sửa lệnh sản xuất';
    $subTitle = 'Chỉnh sửa lệnh ' . $manufacture->code;
@endphp

@section('content')

<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6">
                <h5 class="font-semibold text-base">Chỉnh sửa lệnh sản xuất</h5>
            </div>
            
            <form action="{{ route('manufactures.update', $manufacture) }}" method="POST" id="manufacture-form">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label font-semibold text-sm text-neutral-700 mb-2 block">Mã lệnh sản xuất</label>
                            <input type="text" name="code" class="form-control rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-600" readonly value="{{ $manufacture->code }}">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label font-semibold text-sm text-neutral-700 mb-2 block">Ghi chú lệnh</label>
                            <textarea name="notes" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập ghi chú hoặc yêu cầu sản xuất..." rows="3">{{ old('notes', $manufacture->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="border-t border-neutral-200 pt-6">
                        <h6 class="font-bold text-base text-neutral-800 mb-4 flex items-center gap-2">
                            <iconify-icon icon="lucide:clipboard-list" class="text-xl text-primary-500"></iconify-icon>
                            Chọn đơn hàng ghép <span class="text-danger-500">*</span>
                        </h6>
                        
                        @if($errors->has('order_ids'))
                        <div class="alert alert-danger bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-4 mb-4">
                            Vui lòng chọn ít nhất 1 đơn hàng để ghép lệnh sản xuất.
                        </div>
                        @endif

                        <div class="table-responsive border border-neutral-200 rounded-xl overflow-hidden scroll-sm">
                            <table class="table bordered-table sm-table mb-0">
                                <thead class="bg-neutral-50">
                                    <tr>
                                        <th scope="col" style="width: 50px;" class="text-center">Chọn</th>
                                        <th scope="col">Mã đơn hàng</th>
                                        <th scope="col">Loại đơn</th>
                                        <th scope="col">Tên khách hàng</th>
                                        <th scope="col">Hạn giao hàng</th>
                                        <th scope="col">Tổng tiền</th>
                                        <th scope="col">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                    @php
                                        $isChecked = in_array($order->id, old('order_ids', $linkedOrderIds));
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle">
                                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="form-checkbox h-5 w-5 text-primary-600 border-neutral-300 rounded focus:ring-primary-500" {{ $isChecked ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-secondary-light">{{ $order->order_code }}</span>
                                                @if($order->relation_type === 'rework')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Sửa tấm</span>
                                                @elseif($order->relation_type === 'warranty')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Bảo hành</span>
                                                @elseif($order->relation_type === 'additional')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-success-100 text-success-800 border border-success-200">Bổ sung</span>
                                                    @if($order->board_return_status === 'pending')
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Khách đang giữ ván chưa trả (Tạm tính công nợ)">Chờ trả ván</span>
                                                    @elseif($order->board_return_status === 'returned')
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200" title="Đã xác nhận trả ván (Đã cấn trừ công nợ)">Đã trả ván</span>
                                                    @endif
                                                @elseif($order->relation_type === 'reuse')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Tận dụng tấm</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-neutral-200 text-neutral-800 font-medium px-2 py-1 rounded text-xs">
                                                {{ ucfirst($order->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-base text-secondary-light font-medium">{{ $order->customer_name }}</span>
                                        </td>
                                        <td>
                                            <span class="text-base text-secondary-light">
                                                {{ $order->deadline ? $order->deadline->format('H:i d/m/Y') : '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-base text-secondary-light font-semibold">
                                                {{ number_format(round($order->total_amount, -3), 0, ',', '.') }} VNĐ
                                            </span>
                                        </td>
                                        <td>
                                            @if($order->status === 'pending')
                                                <span class="badge bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-semibold">Chờ xử lý</span>

                                            @elseif($order->status === 'transferred')
                                                <span class="badge bg-info-100 text-info-700 text-xs px-2.5 py-1 rounded-full font-semibold">Chuyển SX</span>
                                            @elseif($order->status === 'completed')
                                                <span class="badge bg-success-100 text-success-700 text-xs px-2.5 py-1 rounded-full font-semibold">Hoàn thành</span>
                                            @else
                                                <span class="badge bg-neutral-100 text-neutral-700 text-xs px-2.5 py-1 rounded-full font-semibold">{{ $order->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-8">
                                            <p class="text-neutral-500 mb-0">Không có đơn hàng nào.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-neutral-200 bg-neutral-50/50 flex items-center justify-end gap-3">
                    <a href="{{ route('manufactures.show', $manufacture) }}" class="btn btn-outline-neutral px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Quay lại</a>
                    <button type="submit" class="btn btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all">Cập nhật lệnh SX</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
