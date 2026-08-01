@extends('layout.layout')
@php
    $title    = 'Lệnh sản xuất';
    $subTitle = 'Danh sách lệnh sản xuất';
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Per page --}}
                    <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                    <form method="GET" action="{{ route('manufactures.index') }}" id="perPageForm">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('manufactures.index') }}" class="navbar-search flex items-center gap-2">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg w-64" placeholder="Tìm theo mã lệnh, ghi chú..." value="{{ $search }}">
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Status filters --}}
                    <form method="GET" action="{{ route('manufactures.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="status" class="form-select form-select-sm border-neutral-200 rounded-lg" onchange="this.form.submit()">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="initialized" {{ $status == 'initialized' ? 'selected' : '' }}>Khởi tạo</option>
                            <option value="tech_approved" {{ $status == 'tech_approved' ? 'selected' : '' }}>KT Duyệt</option>
                            <option value="manager_approved" {{ $status == 'manager_approved' ? 'selected' : '' }}>QĐ Duyệt</option>
                            <option value="stamps_received" {{ $status == 'stamps_received' ? 'selected' : '' }}>Nhận tem</option>
                            <option value="in_production" {{ $status == 'in_production' ? 'selected' : '' }}>Sản xuất</option>
                            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        </select>
                    </form>

                    @can('add manufacture')
                    <a href="{{ route('manufactures.create') }}"
                        class="btn btn-primary text-sm btn-sm px-3 py-2.5 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="text-xl"></iconify-icon>
                        Tạo lệnh SX
                    </a>
                    @endcan
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
            <div class="card-body">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">STT</th>
                                <th scope="col">Mã lệnh</th>
                                <th scope="col">Đơn hàng ghép</th>
                                <th scope="col">Người tạo</th>
                                <th scope="col">Ghi chú</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col">Ngày tạo</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($manufactures as $index => $m)
                            @php $stt = $manufactures->firstItem() + $loop->index; @endphp
                            <tr>
                                <td>{{ $stt }}</td>
                                <td>
                                    <a href="{{ route('manufactures.show', $m) }}" class="text-primary-600 font-semibold hover:underline">
                                        {{ $m->code }}
                                    </a>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($m->orders as $o)
                                        <span class="badge bg-neutral-200 text-neutral-800 text-xs px-2 py-1 rounded font-medium">
                                            {{ $o->order_code }} ({{ ucfirst($o->type) }})
                                        </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $m->creator->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light block max-w-xs truncate" title="{{ $m->notes }}">{{ $m->notes ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($m->status === 'initialized')
                                        <span class="badge bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-semibold">Khởi tạo</span>
                                    @elseif($m->status === 'tech_approved')
                                        <span class="badge bg-yellow-100 text-yellow-700 text-xs px-2.5 py-1 rounded-full font-semibold">KT Duyệt</span>
                                    @elseif($m->status === 'manager_approved')
                                        <span class="badge bg-orange-100 text-orange-700 text-xs px-2.5 py-1 rounded-full font-semibold">QĐ Duyệt</span>
                                    @elseif($m->status === 'stamps_received')
                                        <span class="badge bg-purple-100 text-purple-700 text-xs px-2.5 py-1 rounded-full font-semibold">Nhận tem</span>
                                    @elseif($m->status === 'in_production')
                                        <span class="badge bg-indigo-100 text-indigo-700 text-xs px-2.5 py-1 rounded-full font-semibold">Sản xuất</span>
                                    @elseif($m->status === 'completed')
                                        <span class="badge bg-success-100 text-success-700 text-xs px-2.5 py-1 rounded-full font-semibold">Hoàn thành</span>
                                    @else
                                        <span class="badge bg-neutral-100 text-neutral-700 text-xs px-2.5 py-1 rounded-full font-semibold">{{ $m->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $m->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center gap-3 justify-center">
                                        <a href="{{ route('manufactures.show', $m) }}"
                                            class="bg-info-100 hover:bg-info-200 text-info-600 font-medium w-10 h-10 flex justify-center items-center rounded-full" title="Xem chi tiết">
                                            <iconify-icon icon="lucide:eye" class="text-lg"></iconify-icon>
                                        </a>
                                        @can('edit manufacture')
                                        <a href="{{ route('manufactures.edit', $m) }}"
                                            class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-10 h-10 flex justify-center items-center rounded-full" title="Chỉnh sửa">
                                            <iconify-icon icon="lucide:edit" class="text-lg"></iconify-icon>
                                        </a>
                                        @endcan
                                        @can('delete manufacture')
                                        <form method="POST" action="{{ route('manufactures.destroy', $m) }}"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa lệnh sản xuất này?')" class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-10 h-10 flex justify-center items-center rounded-full" title="Xóa">
                                                <iconify-icon icon="fluent:delete-24-regular" class="text-lg"></iconify-icon>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <p class="text-neutral-500">Chưa có lệnh sản xuất nào</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $manufactures->firstItem() ?? 0 }} đến {{ $manufactures->lastItem() ?? 0 }}
                        trong tổng {{ $manufactures->total() }} lệnh sản xuất
                    </span>
                    {{ $manufactures->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
