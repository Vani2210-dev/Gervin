@php
    $user = $user ?? null;
    $action = $action ?? route('users.store');
    $method = $method ?? 'POST';
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    {{-- Avatar --}}
    <div class="mb-6">
        <label class="inline-block font-semibold text-neutral-600 text-sm mb-3">Ảnh đại diện</label>
        <div class="avatar-upload">
            <div class="avatar-edit absolute bottom-0 end-0 me-6 mt-4 z-[1] cursor-pointer">
                <input type="file" id="avatar" name="avatar" accept=".png,.jpg,.jpeg" hidden>
                <label for="avatar" class="w-8 h-8 flex justify-center items-center bg-primary-100 text-primary-600 border border-primary-600 hover:bg-primary-200 text-lg rounded-full cursor-pointer">
                    <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                </label>
            </div>
            <div class="avatar-preview">
                <div id="imagePreview"
                    @if($user?->avatar)
                        style="background-image: url('{{ route('users.avatar', $user) }}?t={{ $user->updated_at?->timestamp }}');"
                    @endif
                ></div>
            </div>
        </div>
        <p class="text-xs text-secondary-light mt-2">JPG, PNG — tối đa 2MB. Để trống nếu không thay đổi.</p>
        @error('avatar')
            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-5">
        <label for="name" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Họ và tên <span class="text-danger-600">*</span></label>
        <input type="text" class="form-control rounded-lg" id="name" name="name" value="{{ old('name', $user?->name) }}" placeholder="Nhập họ và tên" required>
        @error('name')
            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-5">
        <label for="email" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Email <span class="text-danger-600">*</span></label>
        <input type="email" class="form-control rounded-lg" id="email" name="email" value="{{ old('email', $user?->email) }}" placeholder="Nhập địa chỉ email" required>
        @error('email')
            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-5">
        <label for="phone" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Số điện thoại</label>
        <input type="text" class="form-control rounded-lg" id="phone" name="phone" value="{{ old('phone', $user?->phone) }}" placeholder="Nhập số điện thoại">
        @error('phone')
            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-5">
        <label for="role_id" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Vai trò</label>
        <select class="tom-select-role" id="role_id" name="role_id">
            <option value="">-- Chọn vai trò --</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ old('role_id', $user?->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role_id')
            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-5">
        <label for="description" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Mô tả</label>
        <textarea name="description" class="form-control rounded-lg" id="description" placeholder="Viết mô tả...">{{ old('description', $user?->description) }}</textarea>
        @error('description')
            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>
    @if($method === 'PUT')
    <div class="mb-5">
        <label for="password" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Mật khẩu mới</label>
        <input type="password" class="form-control rounded-lg" id="password" name="password" placeholder="Để trống nếu không muốn thay đổi">
        @error('password')
            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>
    @endif
    <div class="flex items-center justify-center gap-3">
        <a href="{{ route('users.index') }}" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-base px-14 py-[11px] rounded-lg">
            Hủy
        </a>
        <button type="submit" class="btn btn-primary border border-primary-600 text-base px-14 py-3 rounded-lg">
            {{ $method === 'PUT' ? 'Cập nhật' : 'Lưu' }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect !== 'undefined' && document.getElementById('role_id')) {
            new TomSelect('#role_id', {
                allowEmptyOption: true,
                placeholder: '-- Chọn vai trò --',
            });
        }
    });

    document.getElementById('avatar').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('imagePreview');
                preview.style.backgroundImage = 'url(' + e.target.result + ')';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
