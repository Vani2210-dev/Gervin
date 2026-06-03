<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('index') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Trang chủ</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> AI</a>
                    </li>
                    <li>
                        <a href="{{ route('index2') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> CRM</a>
                    </li>
                    <li>
                        <a href="{{ route('index3') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Thương mại điện tử</a>
                    </li>
                    <li>
                        <a href="{{ route('index4') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Tiền mã hóa</a>
                    </li>
                    <li>
                        <a href="{{ route('index5') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Đầu tư</a>
                    </li>
                    <li>
                        <a href="{{ route('index6') }}"><i class="ri-circle-fill circle-icon text-purple-600 w-auto"></i> Hệ thống học tập</a>
                    </li>
                    <li>
                        <a href="{{ route('index7') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> NFT & Gaming</a>
                    </li>
                    <li>
                        <a href="{{ route('index8') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Y tế</a>
                    </li>
                    <li>
                        <a href="{{ route('index9') }}"><i class="ri-circle-fill circle-icon text-purple-600 w-auto"></i> Phân tích</a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-menu-group-title">Ứng dụng</li>
            @can('view customer')
            <li>
                <a href="{{ route('customers.index') }}">
                    <iconify-icon icon="mdi:account-group" class="menu-icon"></iconify-icon>
                    <span>Khách hàng</span>
                </a>
            </li>
            @endcan
            @can('view acrylic order')
            <li>
                <a href="{{ route('orders.index') }}">
                    <iconify-icon icon="mdi:clipboard-text-outline" class="menu-icon"></iconify-icon>
                    <span>Quản lý Đơn hàng</span>
                </a>
            </li>
            @endcan
            @can('view manufacture')
            <li>
                <a href="{{ route('manufactures.index') }}">
                    <iconify-icon icon="mdi:factory" class="menu-icon"></iconify-icon>
                    <span>Lệnh sản xuất</span>
                </a>
            </li>
            @endcan
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mingcute:storage-line" class="menu-icon"></iconify-icon>
                    <span>Quản lý kho</span>
                </a>
                <ul class="sidebar-submenu">
                    @can('view supply')
                    <li>
                        <a href="{{ route('warehouses.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Kho vật tư</a>
                    </li>
                    <li>
                        <a href="{{ route('supplies.index') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Danh mục vật tư</a>
                    </li>
                    @endcan
                    <li>
                        <a href="#"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Kho đồ cũ(DC)</a>
                    </li>
                </ul>
            </li>
            @can('view media')
            <li>
                <a href="{{ route('media.index') }}">
                    <iconify-icon icon="solar:folder-cloud-outline" class="menu-icon"></iconify-icon>
                    <span>Quản lý File</span>
                </a>
            </li>
            @endcan
            @can('view user')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Người dùng</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('users.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Danh sách người dùng</a>
                    </li>
                    <li>
                        <a href="{{ route('users.create') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Thêm người dùng</a>
                    </li>
                    <li>
                        <a href="{{ route('viewProfile') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Xem hồ sơ</a>
                    </li>
                </ul>
            </li>
            @endcan
            @can('view role')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="heroicons:shield-check" class="menu-icon"></iconify-icon>
                    <span>Phân quyền</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('roles.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Vai trò</a>
                    </li>
                </ul>
            </li>
            @endcan

            <!-- View UI -->
            @can('view ui')
            <li class="sidebar-menu-group-title">Ứng dụng</li>

            <li>
                <a href="{{ route('email') }}">
                    <iconify-icon icon="mage:email" class="menu-icon"></iconify-icon>
                    <span>Email</span>
                </a>
            </li>
            <li>
                <a href="{{ route('chatMessage') }}">
                    <iconify-icon icon="bi:chat-dots" class="menu-icon"></iconify-icon>
                    <span>Trò chuyện</span>
                </a>
            </li>
            <li>
                <a href="{{ route('calendarMain') }}">
                    <iconify-icon icon="solar:calendar-outline" class="menu-icon"></iconify-icon>
                    <span>Lịch</span>
                </a>
            </li>
            <li>
                <a href="{{ route('kanban') }}">
                    <iconify-icon icon="material-symbols:map-outline" class="menu-icon"></iconify-icon>
                    <span>Kanban</span>
                </a>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Hóa đơn</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('invoiceList') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Danh sách</a>
                    </li>
                    <li>
                        <a href="{{ route('invoicePreview') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Xem trước</a>
                    </li>
                    <li>
                        <a href="{{ route('invoiceAdd') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Thêm mới</a>
                    </li>
                    <li>
                        <a href="{{ route('invoiceEdit') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Chỉnh sửa</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:ai-brain-03" class="menu-icon"></iconify-icon>
                    <span>Ứng dụng AI</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('textGenerator') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Tạo văn bản</a>
                    </li>
                    <li>
                        <a href="{{ route('codeGenerator') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Tạo mã</a>
                    </li>
                    <li>
                        <a href="{{ route('imageGenerator') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Tạo ảnh</a>
                    </li>
                    <li>
                        <a href="{{ route('voiceGenerator') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Tạo giọng nói</a>
                    </li>
                    <li>
                        <a href="{{ route('videoGenerator') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Tạo video</a>
                    </li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:bitcoin-circle" class="menu-icon"></iconify-icon>
                    <span>Tiền mã hóa</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('wallet') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Ví</a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">Thành phần UI</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                    <span>Thành phần</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('typography') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Typography</a>
                    </li>
                    <li>
                        <a href="{{ route('colors') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Màu sắc</a>
                    </li>
                    <li>
                        <a href="{{ route('button') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Nút</a>
                    </li>
                    <li>
                        <a href="{{ route('dropdown') }}"><i class="ri-circle-fill circle-icon text-purple-600  w-auto"></i> Dropdown</a>
                    </li>
                    <li>
                        <a href="{{ route('alert') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Cảnh báo</a>
                    </li>
                    <li>
                        <a href="{{ route('card') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Thẻ</a>
                    </li>
                    <li>
                        <a href="{{ route('carousel') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Carousel</a>
                    </li>
                    <li>
                        <a href="{{ route('avatar') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Ảnh đại diện</a>
                    </li>
                    <li>
                        <a href="{{ route('progress') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Thanh tiến trình</a>
                    </li>
                    <li>
                        <a href="{{ route('tabs') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Tab & Accordion</a>
                    </li>
                    <li>
                        <a href="{{ route('pagination') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Phân trang</a>
                    </li>
                    <li>
                        <a href="{{ route('badges') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Huy hiệu</a>
                    </li>
                    <li>
                        <a href="{{ route('tooltip') }}"><i class="ri-circle-fill circle-icon w-auto"></i> Tooltip & Popover</a>
                    </li>
                    <li>
                        <a href="{{ route('videos') }}"><i class="ri-circle-fill circle-icon text-cyan-600 w-auto"></i> Video</a>
                    </li>
                    <li>
                        <a href="{{ route('starRating') }}"><i class="ri-circle-fill circle-icon text-[#7f27ff] w-auto"></i> Đánh giá sao</a>
                    </li>
                    <li>
                        <a href="{{ route('tags') }}"><i class="ri-circle-fill circle-icon text-[#8252e9] w-auto"></i> Thẻ</a>
                    </li>
                    <li>
                        <a href="{{ route('list') }}"><i class="ri-circle-fill circle-icon text-[#e30a0a] w-auto"></i> Danh sách</a>
                    </li>
                    <li>
                        <a href="{{ route('calendar') }}"><i class="ri-circle-fill circle-icon text-yellow-400 w-auto"></i> Lịch</a>
                    </li>
                    <li>
                        <a href="{{ route('radio') }}"><i class="ri-circle-fill circle-icon text-orange-500 w-auto"></i> Radio</a>
                    </li>
                    <li>
                        <a href="{{ route('switch') }}"><i class="ri-circle-fill circle-icon text-pink-600 w-auto"></i> Công tắc</a>
                    </li>
                    <li>
                        <a href="{{ route('imageUpload') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Tải lên</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="heroicons:document" class="menu-icon"></iconify-icon>
                    <span>Form</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('form') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Form nhập liệu</a>
                    </li>
                    <li>
                        <a href="{{ route('formLayout') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Bố cục form</a>
                    </li>
                    <li>
                        <a href="{{ route('formValidation') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Xác thực form</a>
                    </li>
                    <li>
                        <a href="{{ route('wizard') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Form nhiều bước</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mingcute:storage-line" class="menu-icon"></iconify-icon>
                    <span>Bảng</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('tableBasic') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Bảng cơ bản</a>
                    </li>
                    <li>
                        <a href="{{ route('tableData') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Bảng dữ liệu</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:pie-chart-outline" class="menu-icon"></iconify-icon>
                    <span>Biểu đồ</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('lineChart') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Biểu đồ đường</a>
                    </li>
                    <li>
                        <a href="{{ route('columnChart') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Biểu đồ cột</a>
                    </li>
                    <li>
                        <a href="{{ route('pieChart') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Biểu đồ tròn</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('widgets') }}">
                    <iconify-icon icon="fe:vector" class="menu-icon"></iconify-icon>
                    <span>Widgets</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="simple-line-icons:vector" class="menu-icon"></iconify-icon>
                    <span>Xác thực</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('signin') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Đăng nhập</a>
                    </li>
                    <li>
                        <a href="{{ route('signup') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Đăng ký</a>
                    </li>
                    <li>
                        <a href="{{ route('forgotPassword') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Quên mật khẩu</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('gallery') }}">
                    <iconify-icon icon="solar:gallery-wide-linear" class="menu-icon"></iconify-icon>
                    <span>Thư viện</span>
                </a>
            </li>
            <li>
                <a href="{{ route('pricing') }}">
                    <iconify-icon icon="hugeicons:money-send-square" class="menu-icon"></iconify-icon>
                    <span>Bảng giá</span>
                </a>
            </li>
            <li>
                <a href="{{ route('faq') }}">
                    <iconify-icon icon="mage:message-question-mark-round" class="menu-icon"></iconify-icon>
                    <span>Câu hỏi thường gặp</span>
                </a>
            </li>
            <li>
                <a href="{{ route('pageError') }}">
                    <iconify-icon icon="streamline:straight-face" class="menu-icon"></iconify-icon>
                    <span>404</span>
                </a>
            </li>
            <li>
                <a href="{{ route('termsCondition') }}">
                    <iconify-icon icon="octicon:info-24" class="menu-icon"></iconify-icon>
                    <span>Điều khoản & Điều kiện</span>
                </a>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
                    <span>Cài đặt</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('company') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Công ty</a>
                    </li>
                    <li>
                        <a href="{{ route('notification') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Thông báo</a>
                    </li>
                    <li>
                        <a href="{{ route('notificationAlert') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Cảnh báo thông báo</a>
                    </li>
                    <li>
                        <a href="{{ route('theme') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Giao diện</a>
                    </li>
                    <li>
                        <a href="{{ route('currencies') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Tiền tệ</a>
                    </li>
                    <li>
                        <a href="{{ route('language') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Ngôn ngữ</a>
                    </li>
                    <li>
                        <a href="{{ route('paymentGateway') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Cổng thanh toán</a>
                    </li>
                </ul>
            </li>
            @endcan
        </ul>
    </div>
</aside>