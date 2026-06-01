@extends('layout.layout')
@php
    $title        = 'Tạo đơn hàng Acrylic';
    $subTitle     = 'Tạo đơn hàng Acrylic mới';
    $action       = route('acrylic_orders.store');
    $acrylicOrder = null;
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        @include('acrylic_orders._form')
    </div>
</div>

@endsection
