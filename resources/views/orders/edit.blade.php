@extends('layout.layout')
@php
    $title    = 'Chỉnh sửa đơn hàng Acrylic';
    $subTitle = 'Chỉnh sửa đơn hàng: ' . $acrylicOrder->order_code;
    $action   = route('orders.update', $acrylicOrder);
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        @include('orders._form')
    </div>
</div>

@endsection
