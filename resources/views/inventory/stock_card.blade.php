@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Thẻ kho - ' . $material->code;
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary px-3 py-2 rounded-lg text-xs font-bold flex items-center gap-1 border border-neutral-300">
                        <iconify-icon icon="solar:arrow-left-linear" class="text-base"></iconify-icon> Danh sách vật tư
                    </a>
                    <div>
                        <h5 class="font-bold text-neutral-800 text-base mb-0">Thẻ kho chi tiết: {{ $material->code }} - {{ $material->name }}</h5>
                        <p class="text-xs text-neutral-500 mb-0">Sổ theo dõi biến động từng lần nhập, xuất và kiểm kê cân bằng kho.</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @include('inventory.partials.stock_card_modal_content')
            </div>
        </div>
    </div>
</div>
@endsection
