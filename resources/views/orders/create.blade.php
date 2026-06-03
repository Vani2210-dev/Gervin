@extends('layout.layout')
@php
    $typeLabels = [
        'acrylic' => 'Đơn hàng Acrylic',
        'glass' => 'Đơn hàng gia công cánh kính',
        'min_late' => 'Đơn hàng gia công Min-late',
    ];

    $typeDescriptions = [
        'acrylic' => 'Quy trình xử lý cho đơn hàng acrylic.',
        'glass' => 'Quy trình xử lý cho đơn hàng gia công cánh kính.',
        'min_late' => 'Quy trình xử lý cho đơn hàng gia công min-late.',
    ];
@endphp

@section('content')

    @if(isset($orderType))
        @php
            $title = $typeLabels[$orderType] ?? 'Tạo đơn hàng';
            $subTitle = 'Tạo mới: ' . ($typeLabels[$orderType] ?? ucfirst($orderType));
            $action = route('orders.store');
            $acrylicOrder = null;
        @endphp

        <div class="grid grid-cols-12">
            <div class="col-span-12">
                @include('orders._form')
            </div>
        </div>
    @else
        @php
            $title = 'Chọn loại đơn hàng';
            $subTitle = 'Chọn loại đơn hàng';
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 items-stretch order-type-page__grid">
            @foreach($typeLabels as $type => $label)
                @php
                    $cardMeta = [
                        'acrylic' => [
                            'icon' => 'solar:box-minimalistic-bold',
                            'iconBg' => 'bg-danger-100',
                            'iconText' => 'text-danger-600',
                            'linkText' => 'text-danger-600',
                            'badge' => 'bg-danger-100 text-danger-600',
                            'button' => 'bg-danger-600 hover:bg-danger-700 text-white',
                        ],
                        'glass' => [
                            'icon' => 'solar:settings-bold',
                            'iconBg' => 'bg-primary-100',
                            'iconText' => 'text-primary-600',
                            'linkText' => 'text-primary-600',
                            'badge' => 'bg-primary-100 text-primary-600',
                            'button' => 'bg-primary-600 hover:bg-primary-700 text-white',
                        ],
                        'min_late' => [
                            'icon' => 'solar:document-text-bold',
                            'iconBg' => 'bg-success-100',
                            'iconText' => 'text-success-600',
                            'linkText' => 'text-success-600',
                            'badge' => 'bg-success-100 text-success-600',
                            'button' => 'bg-success-600 hover:bg-success-700 text-white',
                        ],
                    ][$type];
                @endphp
                <div class="order-type-page__item flex h-full w-full">
                    <div
                        class="card h-full w-full rounded-xl overflow-hidden border-0 order-type-card order-type-card--{{ $type }}">
                        <div class="card-body p-6 text-start flex flex-col order-type-card__body">
                            <div
                                class="w-[64px] h-[64px] inline-flex items-center justify-center {{ $cardMeta['iconBg'] }} {{ $cardMeta['iconText'] }} mb-4 rounded-xl order-type-card__icon">
                                <iconify-icon icon="{{ $cardMeta['icon'] }}"
                                    class="h5 mb-0 order-type-card__icon-symbol"></iconify-icon>
                            </div>
                            <h6 class="mb-2 text-neutral-900 fw-bold order-type-card__title">{{ $label }}</h6>
                            <p class="card-text mb-2 text-secondary-light order-type-card__description">
                                {{ $typeDescriptions[$type] ?? '' }}
                            </p>
                            <div class="mt-auto pt-4 order-type-card__button-wrap flex justify-start">
                                <a href="{{ route('orders.create.type', $type) }}"
                                    class="btn {{ $cardMeta['button'] }} rounded-lg px-5 py-[11px] inline-flex items-center justify-center gap-2 order-type-card__button">
                                    Chọn loại này
                                    <iconify-icon icon="iconamoon:arrow-right-2"
                                        class="text-xl order-type-card__button-icon"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <style>
            .order-type-card__body {
                height: 300px;
            }
        </style>
    @endif


@endsection