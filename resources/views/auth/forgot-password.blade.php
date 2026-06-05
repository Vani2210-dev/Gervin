<!DOCTYPE html>
<html lang="vi">

<x-head />

<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white">

    <section class="bg-white dark:bg-neutral-700 flex flex-wrap min-h-[100vh]">
        <div class="lg:w-1/2 lg:block hidden">
            <div class="flex items-center flex-col h-full justify-center">
                <img src="{{ asset('assets/images/auth/forgot-pass-img.png') }}" alt="">
            </div>
        </div>
        <div class="lg:w-1/2 py-8 px-6 flex flex-col justify-center">
            <div class="lg:max-w-[464px] mx-auto w-full">
                <div class="text-center">
                    <a href="{{ route('index') }}" class="mb-4 inline-block">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="w-48 h-48 mx-auto object-contain">
                    </a>
                    <h4 class="mb-3">Quên mật khẩu</h4>
                    <p class="mb-8 text-secondary-light text-lg">Nhập địa chỉ email liên kết với tài khoản của bạn và chúng tôi sẽ gửi link đặt lại mật khẩu.</p>
                </div>

                @if (session('status'))
                    <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-300 rounded-lg px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="icon-field mb-6 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-neutral-600 rounded-xl @error('email') border-danger-600 @enderror" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
                        @error('email')
                            <div class="text-danger-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl">Gửi link đặt lại mật khẩu</button>

                    <div class="text-center mt-6">
                        <a href="{{ route('login') }}" class="text-primary-600 font-bold hover:underline">Quay lại đăng nhập</a>
                    </div>

                    <div class="mt-10 md:mt-[60px] lg:mt-[100px] xl:mt-[120px] text-center text-sm">
                        <p class="mb-0">Đã có tài khoản? <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:underline">Đăng nhập</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <x-script/>

</body>
</html>
