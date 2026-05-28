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
                <div>
                    <a href="{{ route('index') }}" class="mb-2.5 max-w-[290px]">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="">
                    </a>
                    <h4 class="mb-3">Đăng nhập vào tài khoản</h4>
                    <p class="mb-8 text-secondary-light text-lg">Chào mừng trở lại! Vui lòng nhập thông tin của bạn</p>
                </div>

                @if (session('status'))
                    <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-300 rounded-lg px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="icon-field mb-4 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl @error('email') border-danger-600 @enderror" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
                        @error('email')
                            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="relative mb-5">
                        <div class="icon-field">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                            </span>
                            <input type="password" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl @error('password') border-danger-600 @enderror" id="your-password" name="password" placeholder="Mật khẩu" required autocomplete="current-password">
                        </div>
                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#your-password"></span>
                        @error('password')
                            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mt-7">
                        <div class="flex justify-between gap-2">
                            <div class="flex items-center">
                                <input class="form-check-input border border-neutral-300" type="checkbox" name="remember" id="remember">
                                <label class="ps-2" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-primary-600 font-medium hover:underline">Quên mật khẩu?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8">Đăng nhập</button>

                    <div class="mt-8 text-center text-sm">
                        <p class="mb-0">Chưa có tài khoản? <a href="{{ route('register') }}" class="text-primary-600 font-semibold hover:underline">Đăng ký</a></p>
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
