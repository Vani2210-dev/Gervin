@extends('layout.layout')
@php
    $title        = 'Tạo đơn hàng';
    $subTitle     = 'Tạo đơn hàng mới';
    $action       = route('orders.store');
    $acrylicOrder = null;
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        @include('orders._form')
    </div>
</div>

@endsection
