@extends('layout.layout')
@php
    $title='Thêm người dùng';
    $subTitle = 'Thêm người dùng';
@endphp

@section('content')

    <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
        <div class="card-body p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 justify-center">
                <div class="col-span-12 lg:col-span-10 xl:col-span-8 2xl:col-span-6 2xl:col-start-4">
                    <div class="card border border-neutral-200">
                        <div class="card-body">
                            <h6 class="text-base text-neutral-600 mb-4">Thông tin người dùng</h6>

                            @include('users._form', [
                                'action' => route('users.store'),
                                'method' => 'POST',
                                'user' => null,
                                'roles' => $roles
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
