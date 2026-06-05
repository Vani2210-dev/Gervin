@extends('layout.layout')

@php
    $title = 'Đóng gói';
    $subTitle = 'Quy trình';
    $currentUser = auth()->user();
    $canDeletePacking = $currentUser?->can('delete packing') ?? false;
@endphp

@section('content')
    <div class="-mt-4 mb-6">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Theo dõi các kiện đang đóng gói, tạo kiện mới và xem nhanh trạng thái hoàn tất.</p>
    </div>

    <div class="packing-page__toolbar mb-6 flex w-full flex-col items-end gap-4">
        <div class="packing-page__toolbar-actions flex w-auto flex-wrap items-center justify-end gap-3">
            <div class="packing-page__current-user bg-white border border-neutral-200 rounded-lg shadow-sm px-5 py-3 flex items-center gap-3 min-w-[260px]">
                <iconify-icon icon="lucide:user" class="packing-page__current-user-icon text-lg text-secondary-light"></iconify-icon>
                <span class="packing-page__current-user-name font-semibold text-neutral-900">{{ $currentUser?->name ?? 'Người đóng gói' }}</span>
            </div>
            @can('add packing')
                <button type="button" onclick="openModal('create-packing-package-modal')"
                    class="packing-page__create-package-button btn bg-primary-600 hover:bg-primary-700 text-white px-5 py-3 rounded-lg flex items-center justify-center gap-2 font-semibold shadow-sm">
                    <iconify-icon icon="lucide:plus" class="packing-page__create-package-icon text-xl"></iconify-icon>
                    <span class="packing-page__create-package-label">Tạo kiện mới</span>
                </button>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="packing-page__alert packing-page__alert--success alert alert-success bg-success-100 text-success-700 border border-success-200 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="packing-page__alert packing-page__alert--error alert alert-danger bg-danger-100 text-danger-700 border border-danger-200 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="packing-page__sections space-y-6">
        <div class="packing-page__draft-card card border-0 overflow-hidden shadow-sm">
            <div class="packing-page__draft-card-header card-header bg-white border-b border-neutral-200 px-6 py-4 flex items-center justify-between gap-3">
                <div class="packing-page__draft-title-group flex items-center gap-2">
                    <span class="packing-page__draft-status-dot w-4 h-4 rounded-full bg-warning-400 border-4 border-warning-100"></span>
                    <h6 class="packing-page__draft-title text-lg font-bold mb-0 text-neutral-900">Đang đóng gói ({{ $draftPackages->count() }})</h6>
                </div>
            </div>

            <div class="packing-page__draft-card-body card-body">
                <div class="packing-page__draft-table-wrap table-responsive">
                    <table class="packing-page__draft-table table basic-border-table mb-0">
                        <thead class="packing-page__draft-thead">
                            <tr class="packing-page__draft-head-row">
                                <th scope="col" class="packing-page__draft-head-cell packing-page__draft-head-cell--name border-r border-neutral-200 last:border-r-0">Tên kiện</th>
                                <th scope="col" class="packing-page__draft-head-cell packing-page__draft-head-cell--packer border-r border-neutral-200 last:border-r-0">Người đóng gói</th>
                                <th scope="col" class="packing-page__draft-head-cell packing-page__draft-head-cell--created-at border-r border-neutral-200 last:border-r-0">Thời gian tạo</th>
                                <th scope="col" class="packing-page__draft-head-cell packing-page__draft-head-cell--count border-r border-neutral-200 last:border-r-0 text-center">Số linh kiện</th>
                                @can('delete packing')
                                    <th scope="col" class="packing-page__draft-head-cell packing-page__draft-head-cell--action border-r border-neutral-200 last:border-r-0 text-end">Hành động</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="packing-page__draft-tbody">
                            @forelse($draftPackages as $package)
                                <tr class="packing-page__draft-row cursor-pointer hover:bg-neutral-50" onclick="window.location='{{ route('processes.packing.show', $package) }}'">
                                    <td class="packing-page__draft-cell packing-page__draft-cell--name border-r border-neutral-200 last:border-r-0 font-bold text-neutral-900">
                                        {{ $package->name }}
                                    </td>
                                    <td class="packing-page__draft-cell packing-page__draft-cell--packer border-r border-neutral-200 last:border-r-0">
                                        <div class="packing-page__draft-packer flex items-center gap-2">
                                            <span class="packing-page__draft-packer-avatar w-7 h-7 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xs">
                                                {{ mb_substr($package->packer?->name ?? 'N', 0, 1) }}
                                            </span>
                                            <span class="packing-page__draft-packer-name text-secondary-light font-medium">{{ $package->packer?->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="packing-page__draft-cell packing-page__draft-cell--created-at border-r border-neutral-200 last:border-r-0 text-secondary-light">{{ $package->created_at->format('H:i:s d/m/Y') }}</td>
                                    <td class="packing-page__draft-cell packing-page__draft-cell--count border-r border-neutral-200 last:border-r-0 text-center font-semibold text-secondary-light">{{ $package->items_count }}</td>
                                    @can('delete packing')
                                        <td class="packing-page__draft-cell packing-page__draft-cell--action border-r border-neutral-200 last:border-r-0 text-end">
                                            <form method="POST" action="{{ route('processes.packing.destroy', $package) }}" onsubmit="event.stopPropagation(); return confirm('Xóa kiện này?')" class="packing-page__draft-delete-form inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="event.stopPropagation()"
                                                    class="packing-page__draft-delete-button w-8 h-8 rounded-lg bg-danger-50 text-danger-600 border border-danger-100 hover:bg-danger-600 hover:text-white hover:border-danger-600 transition-colors inline-flex items-center justify-center shadow-sm">
                                                    <iconify-icon icon="lucide:trash-2" class="packing-page__draft-delete-icon text-base"></iconify-icon>
                                                </button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr class="packing-page__draft-empty-row">
                                    <td colspan="{{ $canDeletePacking ? 5 : 4 }}" class="packing-page__draft-empty-cell px-6 py-14 text-center text-neutral-400">Không có kiện nào đang đóng gói.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="packing-page__completed-card card border-0 overflow-hidden shadow-sm">
            <div class="packing-page__completed-card-header card-header bg-white border-b border-neutral-200 px-6 py-4 flex items-center justify-between gap-3">
                <div class="packing-page__completed-title-group flex items-center gap-2">
                    <span class="packing-page__completed-status-dot w-4 h-4 rounded-full bg-success-500"></span>
                    <h6 class="packing-page__completed-title text-lg font-bold mb-0 text-neutral-900">Đã hoàn tất ({{ $completedPackages->count() }})</h6>
                </div>
            </div>

            <div class="packing-page__completed-card-body card-body">
                <div class="packing-page__completed-table-wrap table-responsive">
                    <table class="packing-page__completed-table table basic-border-table mb-0">
                        <thead class="packing-page__completed-thead">
                            <tr class="packing-page__completed-head-row">
                                <th scope="col" class="packing-page__completed-head-cell packing-page__completed-head-cell--name border-r border-neutral-200 last:border-r-0">Tên kiện</th>
                                <th scope="col" class="packing-page__completed-head-cell packing-page__completed-head-cell--packer border-r border-neutral-200 last:border-r-0">Người đóng gói</th>
                                <th scope="col" class="packing-page__completed-head-cell packing-page__completed-head-cell--created-at border-r border-neutral-200 last:border-r-0">Thời gian tạo</th>
                                <th scope="col" class="packing-page__completed-head-cell packing-page__completed-head-cell--count border-r border-neutral-200 last:border-r-0 text-center">Số linh kiện</th>
                                <th scope="col" class="packing-page__completed-head-cell packing-page__completed-head-cell--action border-r border-neutral-200 last:border-r-0 text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="packing-page__completed-tbody">
                            @forelse($completedPackages as $package)
                                <tr class="packing-page__completed-row cursor-pointer hover:bg-neutral-50" onclick="window.location='{{ route('processes.packing.show', $package) }}'">
                                    <td class="packing-page__completed-cell packing-page__completed-cell--name border-r border-neutral-200 last:border-r-0 font-bold text-neutral-900">{{ $package->name }}</td>
                                    <td class="packing-page__completed-cell packing-page__completed-cell--packer border-r border-neutral-200 last:border-r-0">
                                        <div class="packing-page__completed-packer flex items-center gap-2">
                                            <span class="packing-page__completed-packer-avatar w-7 h-7 rounded-full bg-success-100 text-success-600 flex items-center justify-center font-bold text-xs">
                                                {{ mb_substr($package->packer?->name ?? 'N', 0, 1) }}
                                            </span>
                                            <span class="packing-page__completed-packer-name text-secondary-light font-medium">{{ $package->packer?->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="packing-page__completed-cell packing-page__completed-cell--created-at border-r border-neutral-200 last:border-r-0 text-secondary-light">{{ $package->created_at->format('H:i:s d/m/Y') }}</td>
                                    <td class="packing-page__completed-cell packing-page__completed-cell--count border-r border-neutral-200 last:border-r-0 text-center font-semibold text-secondary-light">{{ $package->items_count }}</td>
                                    <td class="packing-page__completed-cell packing-page__completed-cell--action border-r border-neutral-200 last:border-r-0 text-end">
                                        <a href="{{ route('processes.packing.show', $package) }}"
                                            class="packing-page__completed-view-button w-8 h-8 rounded-lg bg-primary-50 text-primary-600 border border-primary-100 hover:bg-primary-600 hover:text-white hover:border-primary-600 transition-colors inline-flex items-center justify-center shadow-sm">
                                            <iconify-icon icon="lucide:eye" class="packing-page__completed-view-icon text-base"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="packing-page__completed-empty-row">
                                    <td colspan="5" class="packing-page__completed-empty-cell px-6 py-14 text-center text-neutral-400">Không có kiện nào đã hoàn tất.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @can('add packing')
        <x-modal name="create-packing-package-modal" maxWidth="lg" class="packing-page__create-modal">
            <form method="POST" action="{{ route('processes.packing.store') }}" class="packing-page__create-modal-form">
                @csrf
                <div class="packing-page__create-modal-header px-6 py-5 border-b border-neutral-200 flex items-center justify-between">
                    <div class="packing-page__create-modal-title-group flex items-center gap-3">
                        <span class="packing-page__create-modal-icon-wrap w-12 h-12 rounded-lg bg-primary-100 text-primary-600 border border-primary-200 flex items-center justify-center shadow-sm">
                            <iconify-icon icon="lucide:package" class="packing-page__create-modal-icon text-2xl"></iconify-icon>
                        </span>
                        <h5 class="packing-page__create-modal-title text-xl font-bold text-neutral-900 mb-0">Tạo kiện đóng gói</h5>
                    </div>
                    <button type="button" onclick="closeModal('create-packing-package-modal')" class="packing-page__create-modal-close text-secondary-light hover:text-neutral-900 text-2xl leading-none">
                        <iconify-icon icon="lucide:x" class="packing-page__create-modal-close-icon"></iconify-icon>
                    </button>
                </div>

                <div class="packing-page__create-modal-body px-6 py-5">
                    <div class="packing-page__create-modal-field mb-4">
                        <label class="packing-page__create-modal-label form-label font-semibold text-sm text-neutral-700">Người đóng gói hiện tại</label>
                        <input type="text" value="{{ $currentUser?->name ?? 'Người đóng gói' }}" class="packing-page__create-modal-input form-control rounded-lg bg-neutral-50" readonly>
                    </div>

                    <div class="packing-page__create-modal-field">
                        <label for="packing_package_name" class="packing-page__create-modal-label form-label font-semibold text-sm text-neutral-700">Tên kiện / Biển số</label>
                        <input type="text" id="packing_package_name" name="name" value="{{ old('name') }}" class="packing-page__create-modal-input form-control rounded-lg" placeholder="Ví dụ: Kiện 2" required autofocus>
                        @error('name')
                            <p class="packing-page__create-modal-error text-danger-600 text-sm mt-2 mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="packing-page__create-modal-footer px-6 py-5 border-t border-neutral-200 bg-neutral-50 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('create-packing-package-modal')" class="packing-page__create-modal-cancel btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-5 py-2.5 rounded-lg font-semibold">Hủy bỏ</button>
                    <button type="submit" class="packing-page__create-modal-submit btn bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2">
                        <iconify-icon icon="lucide:plus" class="packing-page__create-modal-submit-icon text-lg"></iconify-icon>
                        <span class="packing-page__create-modal-submit-label">Tạo kiện</span>
                    </button>
                </div>
            </form>
        </x-modal>

        @if($errors->has('name'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    openModal('create-packing-package-modal');
                });
            </script>
        @endif
    @endcan
@endsection
