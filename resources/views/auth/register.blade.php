<!DOCTYPE html>
<html lang="vi">

<x-head/>

<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white">

    <section class="bg-white dark:bg-dark-2 flex flex-wrap min-h-[100vh]">
        <div class="lg:w-1/2 lg:block hidden">
            <div class="flex items-center flex-col h-full justify-center">
                <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="">
            </div>
        </div>
        <div class="lg:w-1/2 py-8 px-6 flex flex-col justify-center">
            <div class="lg:max-w-[464px] mx-auto w-full">
                <div class="text-center">
                    <a href="{{ route('index') }}" class="mb-4 inline-block">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="w-48 h-48 mx-auto object-contain">
                    </a>
                    <h4 class="mb-3">Đăng ký tài khoản</h4>
                    <p class="mb-8 text-secondary-light text-lg">Vui lòng nhập thông tin để tạo tài khoản mới</p>
                </div>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="icon-field mb-4 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="f7:person"></iconify-icon>
                        </span>
                        <input type="text" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl @error('name') border-danger-600 @enderror" name="name" value="{{ old('name') }}" placeholder="Họ và tên" required autofocus>
                        @error('name')
                            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="icon-field mb-4 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl @error('email') border-danger-600 @enderror" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="username">
                        @error('email')
                            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-5">
                        <div class="relative">
                            <div class="icon-field">
                                <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                    <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                </span>
                                <input type="password" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl @error('password') border-danger-600 @enderror" id="your-password" name="password" placeholder="Mật khẩu" required autocomplete="new-password">
                            </div>
                            <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#your-password"></span>
                        </div>
                        @error('password')
                            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                        <span class="mt-3 text-sm text-secondary-light">Mật khẩu phải có ít nhất 8 ký tự</span>
                    </div>
                    <div class="mb-5">
                        <div class="relative">
                            <div class="icon-field">
                                <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                    <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                </span>
                                <input type="password" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl" id="confirm-password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required autocomplete="new-password">
                            </div>
                            <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#confirm-password"></span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="form-check style-check flex items-start gap-2">
                            <input class="form-check-input border border-neutral-300 mt-1.5" type="checkbox" value="" id="condition" required>
                            <label class="text-sm" for="condition">
                                Bằng cách tạo tài khoản, bạn đồng ý với
                                <a href="javascript:void(0)" class="text-primary-600 font-semibold">Điều khoản & Điều kiện</a> và
                                <a href="javascript:void(0)" class="text-primary-600 font-semibold">Chính sách bảo mật</a>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8">Đăng ký</button>
                    <div class="mt-8 text-center text-sm">
                        <p class="mb-0">Đã có tài khoản? <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:underline">Đăng nhập</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

<x-script />

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".toggle-password").forEach(function (toggle) {
            toggle.addEventListener("click", function () {
                this.classList.toggle("ri-eye-off-line");
                var input = document.querySelector(this.getAttribute("data-toggle"));
                if (input) {
                    input.type = input.type === "password" ? "text" : "password";
                }
            });
        });
    });
</script>

</body>
</html>
