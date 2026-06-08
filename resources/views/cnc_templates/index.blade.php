@extends('layout.layout')
@php
    $title    = 'Cấu hình CNC';
    $subTitle = 'Quản lý mẫu thông số CNC';
@endphp

@section('content')
<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-50 rounded-lg">
                        <iconify-icon icon="lucide:settings-2" class="text-primary-600 text-xl"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="font-bold text-base text-neutral-800 mb-0">Mẫu thông số CNC</h5>
                        <p class="text-xs text-neutral-500 mb-0">Cấu hình bao trong, xoi 1 và xoi 2 cho từng mẫu</p>
                    </div>
                </div>
                @can('add supply')
                <button type="button" onclick="openModal('create-cnc-modal')"
                    class="btn btn-primary text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-2">
                    <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                    Thêm mẫu CNC mới
                </button>
                @endcan
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
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 border-collapse border border-neutral-200 text-xs w-full" style="min-width: 1100px;">
                        <thead class="bg-neutral-50 font-semibold text-neutral-700 text-center text-xs uppercase tracking-wider">
                            <tr>
                                <th rowspan="2" class="text-center align-middle border border-neutral-200 w-10 px-3 py-3">STT</th>
                                <th rowspan="2" class="text-left align-middle border border-neutral-200 px-3 py-3" style="min-width:200px">Tên mẫu CNC</th>
                                <th colspan="4" class="border border-neutral-200 px-3 py-2 bg-blue-50 text-blue-800">Bao trong (mm)</th>
                                <th colspan="6" class="border border-neutral-200 px-3 py-2 bg-emerald-50 text-emerald-800">Xoi 1 (mm)</th>
                                <th colspan="6" class="border border-neutral-200 px-3 py-2 bg-amber-50 text-amber-800">Xoi 2 (mm)</th>
                                <th rowspan="2" class="text-center align-middle border border-neutral-200 px-3 py-3 w-28">Hành động</th>
                            </tr>
                            <tr>
                                <th class="border border-neutral-200 bg-blue-50/50 text-blue-700 px-2 py-2 w-16">Trái</th>
                                <th class="border border-neutral-200 bg-blue-50/50 text-blue-700 px-2 py-2 w-16">Phải</th>
                                <th class="border border-neutral-200 bg-blue-50/50 text-blue-700 px-2 py-2 w-16">Trên</th>
                                <th class="border border-neutral-200 bg-blue-50/50 text-blue-700 px-2 py-2 w-16">Dưới</th>
                                <th class="border border-neutral-200 bg-emerald-50/50 text-emerald-700 px-2 py-2 w-16">Trái</th>
                                <th class="border border-neutral-200 bg-emerald-50/50 text-emerald-700 px-2 py-2 w-16">Phải</th>
                                <th class="border border-neutral-200 bg-emerald-50/50 text-emerald-700 px-2 py-2 w-16">Trên</th>
                                <th class="border border-neutral-200 bg-emerald-50/50 text-emerald-700 px-2 py-2 w-16">Dưới</th>
                                <th class="border border-neutral-200 bg-emerald-50/50 text-emerald-700 px-2 py-2 w-16">Rộng</th>
                                <th class="border border-neutral-200 bg-emerald-50/50 text-emerald-700 px-2 py-2 w-16">Sâu</th>
                                <th class="border border-neutral-200 bg-amber-50/50 text-amber-700 px-2 py-2 w-16">Trái</th>
                                <th class="border border-neutral-200 bg-amber-50/50 text-amber-700 px-2 py-2 w-16">Phải</th>
                                <th class="border border-neutral-200 bg-amber-50/50 text-amber-700 px-2 py-2 w-16">Trên</th>
                                <th class="border border-neutral-200 bg-amber-50/50 text-amber-700 px-2 py-2 w-16">Dưới</th>
                                <th class="border border-neutral-200 bg-amber-50/50 text-amber-700 px-2 py-2 w-16">Rộng</th>
                                <th class="border border-neutral-200 bg-amber-50/50 text-amber-700 px-2 py-2 w-16">Sâu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $index => $tpl)
                            <tr class="hover:bg-neutral-50/30">
                                <td class="text-center border border-neutral-200 px-3 py-2.5">{{ $index + 1 }}</td>
                                <td class="border border-neutral-200 px-3 py-2.5 font-medium text-neutral-800">{{ $tpl->name }}</td>
                                {{-- Bao trong --}}
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-blue-700 font-semibold">{{ $tpl->offset_left ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-blue-700 font-semibold">{{ $tpl->offset_right ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-blue-700 font-semibold">{{ $tpl->offset_top ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-blue-700 font-semibold">{{ $tpl->offset_bottom ?? '—' }}</td>
                                {{-- Xoi 1 --}}
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-emerald-700 font-semibold">{{ $tpl->mill_left ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-emerald-700 font-semibold">{{ $tpl->mill_right ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-emerald-700 font-semibold">{{ $tpl->mill_top ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-emerald-700 font-semibold">{{ $tpl->mill_bottom ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-emerald-700 font-semibold">{{ $tpl->mill_width ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-emerald-700 font-semibold">{{ $tpl->mill_depth ?? '—' }}</td>
                                {{-- Xoi 2 --}}
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-amber-700 font-semibold">{{ $tpl->mill_left_2 ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-amber-700 font-semibold">{{ $tpl->mill_right_2 ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-amber-700 font-semibold">{{ $tpl->mill_top_2 ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-amber-700 font-semibold">{{ $tpl->mill_bottom_2 ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-amber-700 font-semibold">{{ $tpl->mill_width_2 ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5 text-amber-700 font-semibold">{{ $tpl->mill_depth_2 ?? '—' }}</td>
                                <td class="text-center border border-neutral-200 px-2 py-2.5">
                                    <div class="flex items-center gap-2 justify-center">
                                        @can('edit supply')
                                        <button type="button"
                                            onclick="openEditModal({{ $tpl->id }}, {{ json_encode($tpl) }})"
                                            class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-8 h-8 flex justify-center items-center rounded-full"
                                            title="Sửa">
                                            <iconify-icon icon="lucide:edit" class="text-base"></iconify-icon>
                                        </button>
                                        @endcan
                                        @can('delete supply')
                                        <form method="POST" action="{{ route('cnc_templates.destroy', $tpl) }}"
                                            onsubmit="return confirm('Xóa mẫu CNC \"{{ addslashes($tpl->name) }}\"?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-8 h-8 flex justify-center items-center rounded-full"
                                                title="Xóa">
                                                <iconify-icon icon="fluent:delete-24-regular" class="text-base"></iconify-icon>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="19" class="text-center py-12">
                                    <iconify-icon icon="lucide:settings-2" class="text-4xl text-neutral-300 mb-3 block mx-auto"></iconify-icon>
                                    <p class="text-neutral-500 text-sm">Chưa có mẫu CNC nào. Nhấn "Thêm mẫu CNC mới" để bắt đầu.</p>
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

{{-- Modal Thêm mẫu CNC --}}
@can('add supply')
<x-modal name="create-cnc-modal" maxWidth="3xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="text-primary-600 text-xl"></iconify-icon>
            <h5 class="font-bold text-base text-neutral-800 mb-0">Thêm mẫu CNC mới</h5>
        </div>
        <button type="button" onclick="closeModal('create-cnc-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('cnc_templates.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
            <div class="form-group mb-2">
                <label class="form-label font-semibold text-xs text-neutral-600 uppercase tracking-wider mb-1 block">Tên mẫu CNC <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-control form-control-sm rounded-lg" placeholder="Ví dụ: Kính đố 70 hèm s5r10" required>
            </div>

            {{-- Bao trong --}}
            <div class="bg-blue-50/60 rounded-xl border border-blue-100 p-4">
                <h6 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-1.5">
                    <iconify-icon icon="lucide:box" class="text-blue-600"></iconify-icon>
                    Bao trong (Offset)
                </h6>
                <div style="display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 0.75rem 0.75rem;">
                    @foreach(['left' => 'Trái', 'right' => 'Phải', 'top' => 'Trên', 'bottom' => 'Dưới'] as $key => $label)
                    <div>
                        <label class="text-[10px] text-blue-700 font-semibold mb-2 block">{{ $label }}</label>
                        <input type="number" name="offset_{{ $key }}" class="form-control form-control-sm rounded-lg" placeholder="0">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Xoi 1 --}}
            <div class="bg-emerald-50/60 rounded-xl border border-emerald-100 p-4">
                <h6 class="text-sm font-bold text-emerald-800 mb-3 flex items-center gap-1.5">
                    <iconify-icon icon="lucide:git-branch" class="text-emerald-600"></iconify-icon>
                    Xoi 1 (Milling 1)
                </h6>
                <div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 1rem 0.75rem;">
                    @foreach(['left' => 'Trái', 'right' => 'Phải', 'top' => 'Trên', 'bottom' => 'Dưới', 'width' => 'Rộng', 'depth' => 'Sâu'] as $key => $label)
                    <div>
                        <label class="text-[10px] text-emerald-700 font-semibold mb-2 block">{{ $label }}</label>
                        <input type="number" name="mill_{{ $key }}" class="form-control form-control-sm rounded-lg" placeholder="0">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Xoi 2 --}}
            <div class="bg-amber-50/60 rounded-xl border border-amber-100 p-4">
                <h6 class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-1.5">
                    <iconify-icon icon="lucide:git-branch" class="text-amber-600"></iconify-icon>
                    Xoi 2 (Milling 2) <span class="text-[10px] font-normal text-amber-600 ml-1">— để trống nếu không dùng</span>
                </h6>
                <div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 1rem 0.75rem;">
                    @foreach(['left' => 'Trái', 'right' => 'Phải', 'top' => 'Trên', 'bottom' => 'Dưới', 'width' => 'Rộng', 'depth' => 'Sâu'] as $key => $label)
                    <div>
                        <label class="text-[10px] text-amber-700 font-semibold mb-2 block">{{ $label }}</label>
                        <input type="number" name="mill_{{ $key }}_2" class="form-control form-control-sm rounded-lg" placeholder="—">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3 bg-neutral-50 rounded-b-xl">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-1.5">
                <iconify-icon icon="solar:diskette-outline" class="text-base"></iconify-icon> Lưu mẫu CNC
            </button>
            <button type="button" onclick="closeModal('create-cnc-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg text-sm">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa mẫu CNC --}}
@can('edit supply')
<x-modal name="edit-cnc-modal" maxWidth="3xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:edit" class="text-success-600 text-xl"></iconify-icon>
            <h5 class="font-bold text-base text-neutral-800 mb-0">Chỉnh sửa mẫu CNC</h5>
        </div>
        <button type="button" onclick="closeModal('edit-cnc-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-cnc-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 uppercase tracking-wider mb-1 block">Tên mẫu CNC <span class="text-danger-500">*</span></label>
                <input type="text" id="edit_name" name="name" class="form-control form-control-sm rounded-lg" required>
            </div>

            {{-- Bao trong --}}
            <div class="bg-blue-50/60 rounded-xl border border-blue-100 p-4">
                <h6 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-1.5">
                    <iconify-icon icon="lucide:box" class="text-blue-600"></iconify-icon>
                    Bao trong (Offset)
                </h6>
                <div style="display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 0.75rem 0.75rem;">
                    @foreach(['left' => 'Trái', 'right' => 'Phải', 'top' => 'Trên', 'bottom' => 'Dưới'] as $key => $label)
                    <div>
                        <label class="text-[10px] text-blue-700 font-semibold mb-2 block">{{ $label }}</label>
                        <input type="number" id="edit_offset_{{ $key }}" name="offset_{{ $key }}" class="form-control form-control-sm rounded-lg" placeholder="0">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Xoi 1 --}}
            <div class="bg-emerald-50/60 rounded-xl border border-emerald-100 p-4">
                <h6 class="text-sm font-bold text-emerald-800 mb-3 flex items-center gap-1.5">
                    <iconify-icon icon="lucide:git-branch" class="text-emerald-600"></iconify-icon>
                    Xoi 1 (Milling 1)
                </h6>
                <div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 1rem 0.75rem;">
                    @foreach(['left' => 'Trái', 'right' => 'Phải', 'top' => 'Trên', 'bottom' => 'Dưới', 'width' => 'Rộng', 'depth' => 'Sâu'] as $key => $label)
                    <div>
                        <label class="text-[10px] text-emerald-700 font-semibold mb-2 block">{{ $label }}</label>
                        <input type="number" id="edit_mill_{{ $key }}" name="mill_{{ $key }}" class="form-control form-control-sm rounded-lg" placeholder="0">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Xoi 2 --}}
            <div class="bg-amber-50/60 rounded-xl border border-amber-100 p-4">
                <h6 class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-1.5">
                    <iconify-icon icon="lucide:git-branch" class="text-amber-600"></iconify-icon>
                    Xoi 2 (Milling 2)
                </h6>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    @foreach(['left' => 'Trái', 'right' => 'Phải', 'top' => 'Trên', 'bottom' => 'Dưới', 'width' => 'Rộng', 'depth' => 'Sâu'] as $key => $label)
                    <div>
                        <label class="text-[10px] text-amber-700 font-semibold mb-2 block">{{ $label }}</label>
                        <input type="number" id="edit_mill_{{ $key }}_2" name="mill_{{ $key }}_2" class="form-control form-control-sm rounded-lg" placeholder="—">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3 bg-neutral-50 rounded-b-xl">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-1.5">
                <iconify-icon icon="solar:diskette-outline" class="text-base"></iconify-icon> Cập nhật
            </button>
            <button type="button" onclick="closeModal('edit-cnc-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg text-sm">Hủy</button>
        </div>
    </form>
</x-modal>

<script>
function openEditModal(id, tpl) {
    document.getElementById('edit-cnc-form').action = '/cnc-templates/' + id;
    document.getElementById('edit_name').value = tpl.name || '';

    ['left', 'right', 'top', 'bottom'].forEach(k => {
        const el = document.getElementById('edit_offset_' + k);
        if (el) el.value = (tpl['offset_' + k] !== null && tpl['offset_' + k] !== undefined) ? tpl['offset_' + k] : '';
    });
    ['left', 'right', 'top', 'bottom', 'width', 'depth'].forEach(k => {
        const el = document.getElementById('edit_mill_' + k);
        if (el) el.value = (tpl['mill_' + k] !== null && tpl['mill_' + k] !== undefined) ? tpl['mill_' + k] : '';
        const el2 = document.getElementById('edit_mill_' + k + '_2');
        if (el2) el2.value = (tpl['mill_' + k + '_2'] !== null && tpl['mill_' + k + '_2'] !== undefined) ? tpl['mill_' + k + '_2'] : '';
    });

    openModal('edit-cnc-modal');
}
</script>
@endcan
@endsection
