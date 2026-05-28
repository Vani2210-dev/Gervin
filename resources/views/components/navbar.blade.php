<div class="navbar-header border-b border-neutral-200">
    <div class="flex items-center justify-between">
        <div class="col-auto">
            <div class="flex flex-wrap items-center gap-[16px]">
                <button type="button" class="sidebar-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon non-active"></iconify-icon>
                    <iconify-icon icon="iconoir:arrow-right" class="icon active"></iconify-icon>
                </button>
                <button type="button" class="sidebar-mobile-toggle d-flex !leading-[0]">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon !text-[30px]"></iconify-icon>
                </button>
                <form class="navbar-search">
                    <input type="text" name="search" placeholder="Search">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>

            </div>
        </div>
        <div class="col-auto">
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" id="theme-toggle" class="w-10 h-10 bg-neutral-200 rounded-full flex justify-center items-center">
                    <span id="theme-toggle-dark-icon" class="hidden">
                        <i class="ri-sun-line"></i>
                    </span>
                    <span id="theme-toggle-light-icon" class="hidden">
                        <i class="ri-moon-line"></i>
                    </span>
                </button>

                <!-- Message Dropdown Start  -->
                <button data-dropdown-toggle="dropdownMessage" class="has-indicator w-10 h-10 bg-neutral-200 rounded-full flex justify-center items-center" type="button">
                    <iconify-icon icon="mage:email" class="text-neutral-900 text-xl"></iconify-icon>
                </button>
                <div id="dropdownMessage" class="z-10 hidden bg-white rounded-2xl overflow-hidden shadow-lg max-w-[394px] w-full">
                    <div class="py-3 px-4 rounded-lg bg-primary-50 m-4 flex items-center justify-between gap-2">
                        <h6 class="text-lg text-neutral-900 font-semibold mb-0">Tin nhắn</h6>
                        <span class="w-10 h-10 bg-white text-primary-600 font-bold flex justify-center items-center rounded-full">0</span>
                    </div>
                    <div class="scroll-sm !border-t-0">
                        <div class="max-h-[400px] overflow-y-auto p-4">
                            <p class="text-center text-neutral-500">Chưa có tin nhắn</p>
                        </div>
                    </div>
                </div>
                <!-- Message Dropdown End  -->
                <!-- Notification Start  -->
                <button data-dropdown-toggle="dropdownNotification" class="has-indicator w-10 h-10 bg-neutral-200 rounded-full flex justify-center items-center" type="button">
                    <iconify-icon icon="iconoir:bell" class="text-neutral-900 text-xl"></iconify-icon>
                </button>
                <div id="dropdownNotification" class="z-10 hidden bg-white rounded-2xl overflow-hidden shadow-lg max-w-[394px] w-full">
                    <div class="py-3 px-4 rounded-lg bg-primary-50 m-4 flex items-center justify-between gap-2">
                        <h6 class="text-lg text-neutral-900 font-semibold mb-0">Thông báo</h6>
                        <span class="w-10 h-10 bg-white text-primary-600 font-bold flex justify-center items-center rounded-full">0</span>
                    </div>
                    <div class="scroll-sm !border-t-0">
                        <div class="max-h-[400px] overflow-y-auto p-4">
                            <p class="text-center text-neutral-500">Chưa có thông báo</p>
                        </div>
                    </div>
                </div>
                <!-- Notification End  -->


                <button data-dropdown-toggle="dropdownProfile" class="flex justify-center items-center rounded-full" type="button">
                    <img src="{{ asset('assets/images/user.png') }}" alt="image" class="w-10 h-10 object-fit-cover rounded-full">
                </button>
                <div id="dropdownProfile" class="z-10 hidden bg-white rounded-lg shadow-lg dropdown-menu-sm p-3">
                    <div class="py-3 px-4 rounded-lg bg-primary-50 mb-4 flex items-center justify-between gap-2">
                        <div>
                            <h6 class="text-lg text-neutral-900 font-semibold mb-0">Admin</h6>
                            <span class="text-neutral-500">Quản trị viên</span>
                        </div>
                        <button type="button" class="hover:text-danger-600">
                            <iconify-icon icon="radix-icons:cross-1" class="icon text-xl"></iconify-icon>
                        </button>
                    </div>

                    <div class="max-h-[400px] overflow-y-auto scroll-sm pe-2">
                        <ul class="flex flex-col">
                            <li>
                                <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('viewProfile') }}">
                                    <iconify-icon icon="solar:user-linear" class="icon text-xl"></iconify-icon>  My Profile
                                </a>
                            </li>
                            <li>
                                <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('email') }}">
                                    <iconify-icon icon="tabler:message-check" class="icon text-xl"></iconify-icon>  Hộp thư
                                </a>
                            </li>
                            <li>
                                <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('company') }}">
                                    <iconify-icon icon="icon-park-outline:setting-two" class="icon text-xl"></iconify-icon>  Cài đặt
                                </a>
                            </li>
                            <li>
                                <a class="text-black px-0 py-2 hover:text-danger-600 flex items-center gap-4" href="javascript:void(0)">
                                    <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon>  Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>