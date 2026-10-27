<div class="navbar-header border-b border-neutral-200">
    <div class="flex items-center justify-between">
        <div class="col-auto">
            <div class="flex flex-wrap items-center gap-[16px]">
                <button type="button" class="sidebar-toggle active">
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
                <div class="relative">
                    <button data-dropdown-toggle="dropdownNotification" id="btnNotificationToggle" class="relative w-10 h-10 bg-neutral-200 hover:bg-neutral-300 dark:bg-neutral-700 dark:hover:bg-neutral-600 rounded-full flex justify-center items-center transition-colors" type="button" title="Thông báo">
                        <iconify-icon icon="iconoir:bell" class="text-neutral-900 dark:text-neutral-100 text-xl"></iconify-icon>
                        <span id="notifBadge" class="hidden absolute -top-1 -right-1 min-w-[20px] h-5 px-1 bg-red-600 text-white text-[11px] font-bold rounded-full flex items-center justify-center shadow-md animate-pulse">
                            0
                        </span>
                    </button>

                    <div id="dropdownNotification" class="z-50 hidden bg-white dark:bg-neutral-900 rounded-2xl overflow-hidden shadow-2xl border border-neutral-200 dark:border-neutral-700 w-[360px] sm:w-[420px]">
                        <!-- Header -->
                        <div class="p-3.5 bg-neutral-50 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <h6 class="text-base text-neutral-900 dark:text-neutral-100 font-bold mb-0">Thông báo</h6>
                                <span id="notifHeaderCount" class="text-xs px-2 py-0.5 rounded-full font-bold bg-primary-50 text-primary-600 border border-primary-200">0 chưa đọc</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="window.markAllNotificationsRead(event)" class="text-xs text-primary-600 hover:text-primary-700 font-semibold px-2 py-1 hover:bg-primary-50 dark:hover:bg-neutral-700 rounded transition-colors" title="Đánh dấu đã đọc tất cả">
                                    <iconify-icon icon="lucide:check-check" class="text-sm align-middle"></iconify-icon> Đã đọc tất cả
                                </button>
                                <a href="{{ route('notifications.index', ['tab' => 'settings']) }}" class="text-neutral-500 hover:text-neutral-700 p-1 hover:bg-neutral-200 dark:hover:bg-neutral-700 rounded transition-colors" title="Cài đặt thông báo">
                                    <iconify-icon icon="lucide:settings" class="text-base"></iconify-icon>
                                </a>
                            </div>
                        </div>

                        <!-- Filter tabs: Tất cả vs Chưa đọc -->
                        <div class="flex border-b border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs font-semibold">
                            <button type="button" id="tabNotifAll" onclick="window.switchNotifTab('all')" class="flex-1 py-2 text-center border-b-2 border-primary-600 text-primary-600 transition-colors">
                                Tất cả
                            </button>
                            <button type="button" id="tabNotifUnread" onclick="window.switchNotifTab('unread')" class="flex-1 py-2 text-center text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-200 border-b-2 border-transparent transition-colors">
                                Chưa đọc
                            </button>
                        </div>

                        <!-- Notification List -->
                        <div class="scroll-sm max-h-[380px] overflow-y-auto divide-y divide-neutral-100 dark:divide-neutral-800" id="notifListContainer">
                            <div class="py-10 text-center text-neutral-400 text-sm">
                                <iconify-icon icon="lucide:loader" class="animate-spin text-2xl text-primary-500 mb-2"></iconify-icon>
                                <div>Đang tải thông báo...</div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-2.5 bg-neutral-50 dark:bg-neutral-800/60 border-t border-neutral-200 dark:border-neutral-700 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-primary-600 hover:text-primary-700 font-bold inline-flex items-center gap-1.5 transition-colors">
                                <span>Xem tất cả thông báo</span>
                                <iconify-icon icon="lucide:arrow-right" class="text-sm"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Notification End  -->
@auth
                <button data-dropdown-toggle="dropdownProfile" class="flex justify-center items-center rounded-full" type="button">
                    @if(auth()->user()->avatar)
                        <img src="{{ route('users.avatar', auth()->user()) }}?t={{ auth()->user()->updated_at?->timestamp }}"
                             alt="" class="w-10 h-10 object-cover rounded-full">
                    @else
                        <div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                </button>
                <div id="dropdownProfile" class="z-10 hidden bg-white rounded-lg shadow-lg dropdown-menu-sm p-3">
                    <div class="py-3 px-4 rounded-lg bg-primary-50 mb-4 flex items-center justify-between gap-2">
                        <div>
                            <h6 class="text-lg text-neutral-900 font-semibold mb-0">{{ auth()->user()->name }}</h6>
                            <span class="text-neutral-500">{{ auth()->user()->role?->name ?? 'Người dùng' }}</span>
                        </div>
                        <button type="button" class="hover:text-danger-600">
                            <iconify-icon icon="radix-icons:cross-1" class="icon text-xl"></iconify-icon>
                        </button>
                    </div>

                    <div class="max-h-[400px] overflow-y-auto scroll-sm pe-2">
                        <ul class="flex flex-col">
                            <li>
                                <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('viewProfile') }}">
                                    <iconify-icon icon="solar:user-linear" class="icon text-xl"></iconify-icon> Hồ sơ của tôi
                                </a>
                            </li>
                            <li>
                                <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('email') }}">
                                    <iconify-icon icon="tabler:message-check" class="icon text-xl"></iconify-icon> Hộp thư
                                </a>
                            </li>
                            <li>
                                <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('company') }}">
                                    <iconify-icon icon="icon-park-outline:setting-two" class="icon text-xl"></iconify-icon> Cài đặt
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-black w-full text-start px-0 py-2 hover:text-danger-600 flex items-center gap-4">
                                        <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>

<!-- Notification Toast Container -->
<div id="notifToastContainer" class="fixed top-5 right-5 z-[99999] flex flex-col gap-2.5 max-w-[360px] pointer-events-none"></div>

<script>
(function() {
    let cachedNotifications = [];
    let currentNotifTab = 'all';
    let isInitialFetch = true;
    let highestSeenNotifId = 0;

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
    }

    // Âm thanh chuông báo chuẩn KiotViet bằng Web Audio API
    function playKiotVietChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;

            // Tone 1: 587Hz (D5)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now);
            gain1.gain.setValueAtTime(0.2, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Tone 2: 880Hz (A5)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.12);
            gain2.gain.setValueAtTime(0.22, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.55);
        } catch (e) {
            console.warn('Cannot play chime sound:', e);
        }
    }

    // Hiển thị Toast nổi góc màn hình kiểu KiotViet
    function showNotificationToast(item) {
        const container = document.getElementById('notifToastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto bg-white dark:bg-neutral-900 border border-primary-200 dark:border-primary-800 shadow-2xl rounded-2xl p-3.5 flex items-start gap-3 transition-all duration-300 transform translate-y-[-10px] opacity-0 cursor-pointer hover:border-primary-500';
        toast.innerHTML = `
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 shadow-sm">
                <iconify-icon icon="${item.icon || 'lucide:bell'}" class="text-xl"></iconify-icon>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1 mb-0.5">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Thông báo mới</span>
                    <span class="text-[10px] text-neutral-400">${item.time_ago || 'Vừa xong'}</span>
                </div>
                <h6 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate mb-0.5">${item.title}</h6>
                <p class="text-xs text-neutral-600 dark:text-neutral-300 line-clamp-2">${item.content}</p>
            </div>
            <button type="button" class="text-neutral-400 hover:text-neutral-600 p-1" onclick="event.stopPropagation(); this.closest('div').remove();">
                <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
            </button>
        `;

        toast.onclick = function() {
            window.markNotificationRead(item.recipient_id, item.link);
            toast.remove();
        };

        container.appendChild(toast);

        // Hiệu ứng trượt vào
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-10px]', 'opacity-0');
        });

        // Tự động biến mất sau 7 giây
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }
        }, 7000);
    }

    // Render danh sách thông báo vào popup chuông
    function renderNotifications() {
        const listEl = document.getElementById('notifListContainer');
        if (!listEl) return;

        let items = cachedNotifications;
        if (currentNotifTab === 'unread') {
            items = cachedNotifications.filter(i => !i.is_read);
        }

        if (items.length === 0) {
            listEl.innerHTML = `
                <div class="py-12 px-4 text-center text-neutral-400">
                    <iconify-icon icon="lucide:bell-off" class="text-3xl text-neutral-300 dark:text-neutral-600 mb-2"></iconify-icon>
                    <p class="text-xs font-medium text-neutral-500 mb-0">
                        ${currentNotifTab === 'unread' ? 'Không có thông báo chưa đọc nào' : 'Chưa có thông báo nào'}
                    </p>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(item => {
            const unreadClass = !item.is_read ? 'bg-primary-50/40 dark:bg-primary-950/20' : 'bg-transparent';
            const titleClass = !item.is_read ? 'font-bold text-neutral-900 dark:text-neutral-100' : 'font-semibold text-neutral-700 dark:text-neutral-300';
            const unreadDot = !item.is_read ? '<span class="w-2 h-2 rounded-full bg-primary-600 shrink-0" title="Chưa đọc"></span>' : '';

            html += `
                <div class="p-3 hover:bg-neutral-50 dark:hover:bg-neutral-800/80 transition-colors cursor-pointer flex items-start gap-3 ${unreadClass}"
                     onclick="window.markNotificationRead(${item.recipient_id}, '${item.link || ''}')">
                    <div class="w-9 h-9 rounded-xl ${item.color} flex items-center justify-center shrink-0 shadow-xs">
                        <iconify-icon icon="${item.icon}" class="text-lg"></iconify-icon>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <span class="text-xs ${titleClass} truncate">${item.title}</span>
                            ${unreadDot}
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 line-clamp-2 leading-relaxed mb-1">
                            ${item.content}
                        </p>
                        <div class="flex items-center justify-between text-[11px] text-neutral-400">
                            <span>${item.time_ago}</span>
                            <span class="text-[10px] text-neutral-400 font-medium">${item.sender_name || ''}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        listEl.innerHTML = html;
    }

    // Fetch thông báo từ API
    async function fetchNotifications(silent = false) {
        try {
            const res = await fetch('{{ route("api.notifications.index") }}?limit=20', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;

            const data = await res.json();
            const unreadCount = data.unread_count || 0;
            const items = data.notifications || [];

            // Cập nhật Badge trên chuông
            const badgeEl = document.getElementById('notifBadge');
            const headerCountEl = document.getElementById('notifHeaderCount');

            if (badgeEl) {
                if (unreadCount > 0) {
                    badgeEl.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    badgeEl.classList.remove('hidden');
                } else {
                    badgeEl.classList.add('hidden');
                }
            }

            if (headerCountEl) {
                headerCountEl.textContent = `${unreadCount} chưa đọc`;
            }

            // Kiểm tra thông báo mới để phát chuông & hiện toast
            if (!isInitialFetch && items.length > 0) {
                const newItems = items.filter(i => i.id > highestSeenNotifId && !i.is_read);
                if (newItems.length > 0) {
                    playKiotVietChime();
                    newItems.slice(0, 2).forEach(item => showNotificationToast(item));
                }
            }

            if (items.length > 0) {
                const maxId = Math.max(...items.map(i => i.id));
                if (maxId > highestSeenNotifId) {
                    highestSeenNotifId = maxId;
                }
            }

            cachedNotifications = items;
            isInitialFetch = false;
            renderNotifications();
        } catch (err) {
            if (!silent) console.warn('Fetch notifications error:', err);
        }
    }

    // Đánh dấu đã đọc 1 thông báo
    window.markNotificationRead = async function(recipientId, targetLink) {
        // Cập nhật UI ngay lập tức
        const item = cachedNotifications.find(i => i.recipient_id === recipientId);
        if (item && !item.is_read) {
            item.is_read = true;
            renderNotifications();
            const badgeEl = document.getElementById('notifBadge');
            const curVal = parseInt(badgeEl?.textContent || '0', 10);
            if (curVal > 1) {
                badgeEl.textContent = curVal - 1;
            } else if (badgeEl) {
                badgeEl.classList.add('hidden');
            }
        }

        // Gửi API ngầm
        try {
            fetch(`/api/notifications/${recipientId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });
        } catch (e) {}

        // Điều hướng tới link nếu có
        if (targetLink && targetLink !== '#' && targetLink !== 'null' && targetLink !== '') {
            window.location.href = targetLink;
        }
    };

    // Đánh dấu đã đọc tất cả
    window.markAllNotificationsRead = async function(event) {
        if (event) event.stopPropagation();

        cachedNotifications.forEach(i => i.is_read = true);
        const badgeEl = document.getElementById('notifBadge');
        if (badgeEl) badgeEl.classList.add('hidden');
        const headerCountEl = document.getElementById('notifHeaderCount');
        if (headerCountEl) headerCountEl.textContent = '0 chưa đọc';
        renderNotifications();

        try {
            await fetch('{{ route("api.notifications.read-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });
        } catch (e) {
            console.warn('Mark all read error:', e);
        }
    };

    // Đổi tab lọc thông báo: Tất cả vs Chưa đọc
    window.switchNotifTab = function(type) {
        currentNotifTab = type;
        const tabAll = document.getElementById('tabNotifAll');
        const tabUnread = document.getElementById('tabNotifUnread');

        if (type === 'all') {
            tabAll.className = 'flex-1 py-2 text-center border-b-2 border-primary-600 text-primary-600 transition-colors';
            tabUnread.className = 'flex-1 py-2 text-center text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-200 border-b-2 border-transparent transition-colors';
        } else {
            tabUnread.className = 'flex-1 py-2 text-center border-b-2 border-primary-600 text-primary-600 transition-colors';
            tabAll.className = 'flex-1 py-2 text-center text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-200 border-b-2 border-transparent transition-colors';
        }

        renderNotifications();
    };

    // Khởi chạy khi tải trang
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => fetchNotifications(false));
    } else {
        fetchNotifications(false);
    }

    // Polling định kỳ mỗi 15 giây
    setInterval(() => {
        fetchNotifications(true);
    }, 15000);
})();
</script>