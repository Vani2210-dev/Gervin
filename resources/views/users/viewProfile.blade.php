@extends('layout.layout')
@php
    $title = $user->name;
    $subTitle = 'Hồ sơ người dùng';
    $script ='<script>
                    // ======================== Upload Image Start =====================
                    function readURL(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
                                $("#imagePreview").hide();
                                $("#imagePreview").fadeIn(650);
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                    $("#imageUpload").change(function() {
                        readURL(this);
                    });
                    // ======================== Upload Image End =====================

                    // ================== Password Show Hide Js Start ==========
                    function initializePasswordToggle(toggleSelector) {
                        $(toggleSelector).on("click", function() {
                            $(this).toggleClass("ri-eye-off-line");
                            var input = $($(this).attr("data-toggle"));
                            if (input.attr("type") === "password") {
                                input.attr("type", "text");
                            } else {
                                input.attr("type", "password");
                            }
                        });
                    }
                    // Call the function
                    initializePasswordToggle(".toggle-password");
                    // ========================= Password Show Hide Js End ===========================
            </script>';
@endphp

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4">
            <div class="user-grid-card relative border border-neutral-200 rounded-2xl overflow-hidden bg-white h-full">
                <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt="" class="w-full object-fit-cover">
                <div class="pb-6 ms-6 mb-6 me-6 -mt-[100px]">
                    <div class="text-center border-b border-neutral-200">
                        @if($user->avatar)
                            <img src="{{ route('users.avatar', $user) }}?t={{ $user->updated_at?->timestamp }}" alt=""
                                 class="rounded-full object-cover mx-auto"
                                 style="width:200px;height:200px;border:3px solid #fff;">
                        @else
                            <div class="rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-6xl font-bold mx-auto"
                                 style="width:200px;height:200px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <h6 class="mb-0 mt-4">{{ $user->name }}</h6>
                        <span class="text-secondary-light mb-4">{{ $user->email }}</span>
                    </div>
                    <div class="mt-6">
                        <h6 class="text-xl mb-4">Thông tin cá nhân</h6>
                        <ul>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[30%] text-base font-semibold text-neutral-600">Họ và tên</span>
                                <span class="w-[70%] text-secondary-light font-medium">: {{ $user->name }}</span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[30%] text-base font-semibold text-neutral-600">Email</span>
                                <span class="w-[70%] text-secondary-light font-medium">: {{ $user->email }}</span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[30%] text-base font-semibold text-neutral-600">Điện thoại</span>
                                <span class="w-[70%] text-secondary-light font-medium">: {{ $user->phone ?? '—' }}</span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[30%] text-base font-semibold text-neutral-600">Vai trò</span>
                                <span class="w-[70%] text-secondary-light font-medium">: {{ $user->role?->name ?? '—' }}</span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[30%] text-base font-semibold text-neutral-600">Chức vụ</span>
                                <span class="w-[70%] text-secondary-light font-medium">: {{ $user->designation ?? '—' }}</span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[30%] text-base font-semibold text-neutral-600">Ngày tạo</span>
                                <span class="w-[70%] text-secondary-light font-medium">: {{ $user->created_at?->format('d/m/Y') ?? '—' }}</span>
                            </li>
                            <li class="flex items-center gap-1">
                                <span class="w-[30%] text-base font-semibold text-neutral-600">Mô tả</span>
                                <span class="w-[70%] text-secondary-light font-medium">: {{ $user->description ?? '—' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-8">
            <div class="card h-full border-0">
                <div class="card-body p-6">

                    <ul class="tab-style-gradient flex flex-wrap text-sm font-medium text-center mb-5" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                        <li class="" role="presentation">
                            <button class="py-2.5 px-4 border-t-2 font-semibold text-base inline-flex items-center gap-3 text-neutral-600" id="edit-profile-tab" data-tabs-target="#edit-profile" type="button" role="tab" aria-controls="edit-profile" aria-selected="false">
                                Chỉnh sửa
                            </button>
                        </li>
                        <li class="" role="presentation">
                            <button class="py-2.5 px-4 border-t-2 font-semibold text-base inline-flex items-center gap-3 text-neutral-600 hover:text-gray-600 hover:border-gray-300" id="change-password-tab" data-tabs-target="#change-password" type="button" role="tab" aria-controls="change-password" aria-selected="false">
                                Đổi mật khẩu
                            </button>
                        </li>
                    </ul>

                    <div id="default-tab-content">
                        {{-- Tab: Chỉnh sửa --}}
                        <div class="hidden" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
                            @php
                                $isOwnProfile = auth()->id() === $user->id;
                                $formAction = $isOwnProfile
                                    ? route('updateProfile')
                                    : route('users.update', $user);
                                $formMethod = $isOwnProfile ? 'POST' : 'PUT';
                            @endphp
                            @include('users._form', [
                                'user'   => $user,
                                'roles'  => $roles,
                                'action' => $formAction,
                                'method' => $formMethod,
                            ])
                        </div>

                        {{-- Tab: Đổi mật khẩu --}}
                        <div class="hidden" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                            @php $isOwnProfile = auth()->id() === $user->id; @endphp
                            <form action="{{ $isOwnProfile ? route('updateProfile') : route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @unless($isOwnProfile) @method('PUT') @endunless
                                <div class="mb-5">
                                    <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Mật khẩu mới <span class="text-danger-600">*</span></label>
                                    <div class="relative">
                                        <input type="password" class="form-control rounded-lg" name="password" id="your-password" placeholder="Nhập mật khẩu mới">
                                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#your-password"></span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center gap-3">
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-base px-14 py-3 rounded-lg">Lưu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
