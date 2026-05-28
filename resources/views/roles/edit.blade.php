@extends('layout.layout')
@php
    $title='Chỉnh sửa vai trò';
    $subTitle = 'Chỉnh sửa vai trò';
@endphp

@section('content')

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card h-full rounded-xl border-0 overflow-hidden">
                <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
                    <h5 class="text-lg font-semibold text-secondary-light mb-0">Chỉnh sửa vai trò</h5>
                </div>
                <div class="card-body p-6">
                    @include('roles._form', [
                        'action' => route('roles.update', $role),
                        'method' => 'PUT',
                        'role' => $role,
                        'selectedPermissions' => old('permissions', $rolePermissions),
                    ])
                </div>
            </div>
        </div>
    </div>

@endsection
