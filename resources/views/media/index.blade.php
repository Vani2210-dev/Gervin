@extends('layout.layout')
@php
    $title = 'Quản lý File';
    $subTitle = 'Quản lý File';
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">

            {{-- Card header: toolbar --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Per page --}}
                    <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                    <form method="GET" action="{{ route('media.index') }}" id="perPageForm">
                        <input type="hidden" name="folder" value="{{ $currentFolder }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([10, 20, 50, 100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Tìm kiếm --}}
                    <form method="GET" action="{{ route('media.index') }}" class="navbar-search">
                        <input type="hidden" name="folder" value="{{ $currentFolder }}">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="text" class="bg-white h-10 w-auto" name="search"
                            value="{{ $search }}" placeholder="Tìm kiếm file...">
                        <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    </form>

                    {{-- Breadcrumb thư mục --}}
                    @if($currentFolder !== '/')
                    <div class="flex items-center gap-1 text-sm text-secondary-light">
                        <a href="{{ route('media.index') }}" class="hover:text-primary-600">Gốc</a>
                        @php $parts = explode('/', trim($currentFolder, '/')); $built = ''; @endphp
                        @foreach($parts as $part)
                            @php $built .= ($built ? '/' : '') . $part; $snap = $built; @endphp
                            <span>/</span>
                            <a href="{{ route('media.index', ['folder' => $snap]) }}" class="hover:text-primary-600">{{ $part }}</a>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Nút hành động --}}
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('filter-modal')"
                        class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Lọc
                    </button>
                    @if(request()->filled('filter_name') || request()->filled('filter_type') || request()->filled('filter_min_size') || request()->filled('filter_max_size'))
                    <a href="{{ route('media.index', ['folder' => $currentFolder]) }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif
                    @can('add media')
                    <button type="button" onclick="openModal('upload-modal')"
                        class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Tải lên file
                    </button>
                    <button type="button" onclick="openModal('folder-modal')"
                        class="btn btn-outline-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Thư mục mới
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="mx-6 mt-4 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mx-6 mt-4 bg-danger-100 border border-danger-300 text-danger-700 rounded-lg px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Table --}}
            <div class="card-body">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Tên</th>
                                <th scope="col">Loại</th>
                                <th scope="col">Kích thước</th>
                                <th scope="col">Ngày sửa đổi</th>
                                <th scope="col">Tải lên bởi</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($files as $file)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($file['is_dir'])
                                            <iconify-icon icon="solar:folder-bold" class="text-warning-500 text-2xl"></iconify-icon>
                                            <a href="{{ $file['url'] }}" class="text-base font-medium text-secondary-light hover:text-primary-600">
                                                {{ $file['name'] }}
                                            </a>
                                        @else
                                            @php
                                                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                                $icon = match(true) {
                                                    in_array($ext, ['jpg','jpeg','png','gif','webp']) => 'solar:gallery-bold',
                                                    in_array($ext, ['doc','docx'])                   => 'solar:document-bold',
                                                    in_array($ext, ['xls','xlsx','csv'])             => 'solar:chart-square-bold',
                                                    $ext === 'pdf'                                   => 'solar:document-text-bold',
                                                    in_array($ext, ['zip','rar','7z'])               => 'solar:zip-file-bold',
                                                    default                                          => 'solar:file-bold',
                                                };
                                                $iconColor = match(true) {
                                                    in_array($ext, ['jpg','jpeg','png','gif','webp']) => 'text-success-500',
                                                    in_array($ext, ['doc','docx'])                   => 'text-primary-500',
                                                    in_array($ext, ['xls','xlsx','csv'])             => 'text-success-600',
                                                    $ext === 'pdf'                                   => 'text-danger-500',
                                                    default                                          => 'text-neutral-400',
                                                };
                                            @endphp
                                            <iconify-icon icon="{{ $icon }}" class="{{ $iconColor }} text-2xl"></iconify-icon>
                                            <div>
                                                <a href="{{ $file['url'] }}" target="_blank"
                                                    class="text-base mb-0 font-normal text-secondary-light hover:text-primary-600 block">
                                                    {{ $file['document']?->title ?? $file['name'] }}
                                                </a>
                                                @if($file['document']?->title && $file['document']->title !== $file['name'])
                                                    <span class="text-xs text-neutral-400">{{ $file['name'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-base mb-0 font-normal text-secondary-light">
                                        {{ $file['is_dir'] ? 'Thư mục' : strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-base mb-0 font-normal text-secondary-light">
                                        @if(!$file['is_dir'] && $file['size'] > 0)
                                            @php $s = $file['size']; @endphp
                                            {{ $s >= 1048576 ? round($s/1048576,1).' MB' : round($s/1024,1).' KB' }}
                                        @else —
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="text-base mb-0 font-normal text-secondary-light">
                                        {{ $file['last_modified'] ? date('d/m/Y H:i', $file['last_modified']) : '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-base mb-0 font-normal text-secondary-light">
                                        {{ $file['document']?->uploader?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center gap-3 justify-center">
                                        @if(!$file['is_dir'])
                                        <a href="{{ route('media.download', ['filename' => $file['path']]) }}"
                                            class="bg-info-100 hover:bg-info-200 text-info-600 font-medium w-10 h-10 flex justify-center items-center rounded-full"
                                            title="Tải xuống">
                                            <iconify-icon icon="solar:download-outline" class="icon text-xl"></iconify-icon>
                                        </a>
                                        @endif
                                        @can('delete media')
                                        @if($file['name'] !== '.. (Quay lại)')
                                        <form method="POST"
                                            action="{{ route('media.destroy', ['filename' => $file['path']]) }}"
                                            onsubmit="return confirm('Xóa {{ addslashes($file['is_dir'] ? 'thư mục' : 'file') }} này?')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="is_folder" value="{{ $file['is_dir'] ? '1' : '0' }}">
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-10 h-10 flex justify-center items-center rounded-full"
                                                title="Xóa">
                                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <p class="text-neutral-500">Thư mục trống</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $files->firstItem() ?? 0 }} đến {{ $files->lastItem() ?? 0 }}
                        trong tổng {{ $files->total() }} mục
                    </span>
                    {{ $files->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Upload --}}
@can('add media')
<x-modal name="upload-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-600 flex items-center justify-between">
        <h5 class="font-semibold text-base">Tải lên file</h5>
        <button type="button" onclick="closeModal('upload-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="folder" value="{{ $currentFolder }}">
        <div class="p-6 flex flex-col gap-4">
            <div class="form-group">
                <label class="form-label">Chọn file <span class="text-danger-500">*</span></label>
                <input type="file" name="files[]" multiple class="form-control" required>
                <span class="text-xs text-secondary-light mt-1 block">Tối đa 20MB mỗi file. Có thể chọn nhiều file.</span>
            </div>
            <div class="form-group">
                <label class="form-label">Tiêu đề</label>
                <input type="text" name="title" class="form-control" placeholder="Để trống sẽ lấy tên file">
            </div>
            <div class="form-group">
                <label class="form-label">Ghi chú</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Ghi chú thêm..."></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-600 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Tải lên</button>
            <button type="button" onclick="closeModal('upload-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

{{-- Modal Tạo thư mục --}}
<x-modal name="folder-modal" maxWidth="md">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-600 flex items-center justify-between">
        <h5 class="font-semibold text-base">Tạo thư mục mới</h5>
        <button type="button" onclick="closeModal('folder-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('media.folder') }}" method="POST">
        @csrf
        <input type="hidden" name="current_folder" value="{{ $currentFolder }}">
        <div class="p-6">
            <div class="form-group">
                <label class="form-label">Tên thư mục <span class="text-danger-500">*</span></label>
                <input type="text" name="folder_name" class="form-control" placeholder="Nhập tên thư mục" required>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-600 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Tạo</button>
            <button type="button" onclick="closeModal('folder-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Lọc --}}
<x-modal name="filter-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Lọc file</h5>
        <button type="button" onclick="closeModal('filter-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('media.index') }}" method="GET">
        <input type="hidden" name="folder" value="{{ $currentFolder }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên file</label>
                <input type="text" name="filter_name" class="form-control rounded-lg" placeholder="Nhập tên file..." value="{{ request('filter_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Loại file</label>
                <select name="filter_type" class="form-select rounded-lg">
                    <option value="">Tất cả loại</option>
                    <option value="image" {{ request('filter_type') === 'image' ? 'selected' : '' }}>Ảnh</option>
                    <option value="word" {{ request('filter_type') === 'word' ? 'selected' : '' }}>Word</option>
                    <option value="pdf" {{ request('filter_type') === 'pdf' ? 'selected' : '' }}>PDF</option>
                    <option value="excel" {{ request('filter_type') === 'excel' ? 'selected' : '' }}>Excel</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Kích thước từ (KB)</label>
                <input type="number" name="filter_min_size" class="form-control rounded-lg" placeholder="0" min="0" value="{{ request('filter_min_size') }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Kích thước đến (KB)</label>
                <input type="number" name="filter_max_size" class="form-control rounded-lg" placeholder="0" min="0" value="{{ request('filter_max_size') }}">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

@endsection
