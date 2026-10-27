@extends('layout.layout')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-neutral-900 p-4 sm:p-5 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
        <div>
            <h4 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-2 mb-1">
                <iconify-icon icon="solar:bell-bing-bold-duotone" class="text-primary-600 text-2xl"></iconify-icon>
                <span>Trung tâm Thông báo</span>
            </h4>
            <p class="text-xs sm:text-sm text-neutral-500 mb-0">
                Quản lý thông báo hệ thống, đơn hàng và gửi tin nhắn nội bộ tới nhóm thị trường, chức vụ hoặc nhân viên
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="openCreateNotificationModal()" class="btn btn-sm bg-primary-600 hover:bg-primary-700 text-white rounded-xl px-4 py-2.5 flex items-center gap-2 font-semibold shadow-sm transition-all hover:shadow-md">
                <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
                <span>Tạo thông báo mới</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-2 text-sm shadow-xs">
        <iconify-icon icon="lucide:check-circle" class="text-emerald-600 text-lg shrink-0"></iconify-icon>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-2 text-sm shadow-xs">
        <iconify-icon icon="lucide:alert-circle" class="text-red-600 text-lg shrink-0"></iconify-icon>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Main Card with Tabs -->
    <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
        <!-- Tabs Header -->
        <div class="flex items-center border-b border-neutral-200 dark:border-neutral-700 px-4 sm:px-6 bg-neutral-50/50 dark:bg-neutral-800/30 overflow-x-auto scroll-none">
            <a href="{{ route('notifications.index', ['tab' => 'inbox']) }}" 
               class="py-4 px-4 font-bold text-sm border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors {{ $tab === 'inbox' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-200' }}">
                <iconify-icon icon="lucide:inbox" class="text-base"></iconify-icon>
                <span>Hộp thư đến</span>
                @if($unreadCount > 0)
                <span class="bg-red-600 text-white text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                @endif
            </a>

            @if(auth()->user()->hasRole('Admin') || auth()->user()->can('manage notifications'))
            <a href="{{ route('notifications.index', ['tab' => 'sent']) }}" 
               class="py-4 px-4 font-bold text-sm border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors {{ $tab === 'sent' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-200' }}">
                <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
                <span>Thông báo đã gửi</span>
            </a>

            <a href="{{ route('notifications.index', ['tab' => 'settings']) }}" 
               class="py-4 px-4 font-bold text-sm border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors {{ $tab === 'settings' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-200' }}">
                <iconify-icon icon="lucide:settings-2" class="text-base"></iconify-icon>
                <span>Cài đặt thông báo đơn mới</span>
            </a>
            @endif
        </div>

        <div class="p-4 sm:p-6">
            {{-- TAB 1: INBOX --}}
            @if($tab === 'inbox')
            <div class="space-y-4">
                <!-- Filters & Action bar -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="tab" value="inbox">
                        <div class="relative w-full sm:w-64">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm thông báo..." 
                                   class="form-control text-xs sm:text-sm pl-9 pr-3 py-2 rounded-xl border border-neutral-300 dark:border-neutral-600 w-full">
                            <iconify-icon icon="lucide:search" class="absolute left-3 top-2.5 text-neutral-400 text-base"></iconify-icon>
                        </div>

                        <select name="filter_status" onchange="this.form.submit()" class="form-select text-xs sm:text-sm py-2 px-3 rounded-xl border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800">
                            <option value="">Tất cả thông báo</option>
                            <option value="unread" {{ request('filter_status') === 'unread' ? 'selected' : '' }}>Chỉ tin chưa đọc</option>
                        </select>
                    </form>

                    @if($unreadCount > 0)
                    <button type="button" onclick="window.markAllNotificationsRead(event); setTimeout(() => window.location.reload(), 300);" 
                            class="text-xs text-primary-600 hover:text-primary-700 font-semibold px-3 py-2 rounded-lg bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/30 flex items-center gap-1.5 transition-colors self-start sm:self-auto">
                        <iconify-icon icon="lucide:check-check" class="text-sm"></iconify-icon>
                        <span>Đánh dấu tất cả đã đọc</span>
                    </button>
                    @endif
                </div>

                <!-- Inbox List -->
                @if($inboxList->count() > 0)
                <div class="divide-y divide-neutral-100 dark:divide-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl overflow-hidden bg-white dark:bg-neutral-900">
                    @foreach($inboxList as $item)
                        @php
                            $notif = $item->notification;
                            $unread = !$item->is_read;
                            
                            $icon = 'lucide:bell';
                            $iconColor = 'text-primary-600 bg-primary-50';
                            if ($notif->type === 'order_created') {
                                $icon = 'lucide:shopping-bag';
                                $iconColor = 'text-emerald-600 bg-emerald-50';
                            } elseif ($notif->type === 'order_status_changed') {
                                $icon = 'lucide:refresh-cw';
                                $iconColor = 'text-blue-600 bg-blue-50';
                            } elseif ($notif->type === 'order_updated') {
                                $icon = 'lucide:edit-3';
                                $iconColor = 'text-amber-600 bg-amber-50';
                            } elseif ($notif->type === 'urgent') {
                                $icon = 'lucide:alert-triangle';
                                $iconColor = 'text-danger-600 bg-danger-50';
                            }
                        @endphp
                        <div class="p-4 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $unread ? 'bg-primary-50/30 dark:bg-primary-950/15' : 'hover:bg-neutral-50 dark:hover:bg-neutral-800/50' }}">
                            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                <div class="w-10 h-10 rounded-xl {{ $iconColor }} flex items-center justify-center shrink-0 shadow-xs">
                                    <iconify-icon icon="{{ $icon }}" class="text-xl"></iconify-icon>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h6 class="text-sm {{ $unread ? 'font-bold text-neutral-900 dark:text-neutral-100' : 'font-semibold text-neutral-700 dark:text-neutral-300' }} mb-0">
                                            {{ $notif->title }}
                                        </h6>
                                        @if($unread)
                                        <span class="inline-block w-2 h-2 rounded-full bg-red-600" title="Chưa đọc"></span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 bg-red-100 text-red-700 rounded">Mới</span>
                                        @endif
                                    </div>
                                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mb-1 leading-relaxed">
                                        {{ $notif->content }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-neutral-400">
                                        <span class="flex items-center gap-1">
                                            <iconify-icon icon="lucide:clock" class="text-xs"></iconify-icon>
                                            {{ \App\Services\NotificationService::formatRelativeTime($notif->created_at) }} ({{ $notif->created_at->format('d/m/Y H:i') }})
                                        </span>
                                        @if($notif->sender)
                                        <span class="flex items-center gap-1">
                                            <iconify-icon icon="lucide:user" class="text-xs"></iconify-icon>
                                            Gửi bởi: {{ $notif->sender->name }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                @if($notif->link)
                                <a href="{{ $notif->link }}" onclick="window.markNotificationRead({{ $item->id }}, '{{ $notif->link }}')" 
                                   class="btn btn-sm bg-neutral-100 hover:bg-primary-50 text-neutral-700 hover:text-primary-600 rounded-lg px-3 py-1.5 text-xs font-semibold flex items-center gap-1 transition-colors">
                                    <span>Xem chi tiết</span>
                                    <iconify-icon icon="lucide:external-link" class="text-xs"></iconify-icon>
                                </a>
                                @endif

                                <form method="POST" action="{{ route('notifications.destroy', $item->id) }}" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-neutral-400 hover:text-red-600 p-1.5 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors" title="Xóa">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $inboxList->links() }}
                </div>
                @else
                <div class="py-16 text-center text-neutral-400 bg-neutral-50/50 dark:bg-neutral-800/30 rounded-xl border border-dashed border-neutral-200 dark:border-neutral-700">
                    <iconify-icon icon="lucide:mail-open" class="text-5xl text-neutral-300 dark:text-neutral-600 mb-3"></iconify-icon>
                    <h6 class="text-sm font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Không có thông báo nào</h6>
                    <p class="text-xs text-neutral-400 mb-0">Hộp thư thông báo của bạn hiện đang trống.</p>
                </div>
                @endif
            </div>

            {{-- TAB 2: SENT NOTIFICATIONS --}}
            @elseif($tab === 'sent' && (auth()->user()->hasRole('Admin') || auth()->user()->can('manage notifications')))
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h6 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-0">
                        Lịch sử các thông báo đã gửi
                    </h6>
                    <button type="button" onclick="openCreateNotificationModal()" class="text-xs text-primary-600 hover:text-primary-700 font-bold flex items-center gap-1">
                        <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Gửi thông báo khác
                    </button>
                </div>

                @if($sentList->count() > 0)
                <div class="overflow-x-auto border border-neutral-200 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-900">
                    <table class="table-auto w-full text-left text-xs sm:text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/60 text-neutral-600 dark:text-neutral-300 border-b border-neutral-200 dark:border-neutral-700 uppercase font-semibold text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Tiêu đề & Nội dung</th>
                                <th class="px-4 py-3">Đối tượng nhận</th>
                                <th class="px-4 py-3 text-center">Người nhận</th>
                                <th class="px-4 py-3 text-center">Đã đọc</th>
                                <th class="px-4 py-3">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @foreach($sentList as $sNotif)
                            @php
                                $totalRecipients = $sNotif->recipients->count();
                                $readCount = $sNotif->recipients->where('is_read', true)->count();
                                $targetLabel = match($sNotif->target_type) {
                                    'all' => 'Toàn phần mềm',
                                    'market_group' => 'Nhóm thị trường',
                                    'role' => 'Theo chức vụ',
                                    'user' => 'Người cụ thể',
                                    'order_stakeholders' => 'Liên quan đơn hàng',
                                    default => $sNotif->target_type
                                };
                            @endphp
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                                <td class="px-4 py-3 max-w-sm">
                                    <div class="font-bold text-neutral-900 dark:text-neutral-100 mb-0.5">{{ $sNotif->title }}</div>
                                    <div class="text-xs text-neutral-500 line-clamp-2 leading-relaxed">{{ $sNotif->content }}</div>
                                    @if($sNotif->link)
                                    <a href="{{ $sNotif->link }}" class="text-[11px] text-primary-600 hover:underline mt-1 inline-flex items-center gap-1">
                                        <span>Link liên kết</span>
                                        <iconify-icon icon="lucide:arrow-right" class="text-[10px]"></iconify-icon>
                                    </a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                                        {{ $targetLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200">
                                    {{ $totalRecipients }} người
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $readCount > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-neutral-100 text-neutral-500' }}">
                                        {{ $readCount }}/{{ $totalRecipients }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-neutral-500">
                                    {{ $sNotif->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $sentList->links() }}
                </div>
                @else
                <div class="py-16 text-center text-neutral-400 bg-neutral-50/50 dark:bg-neutral-800/30 rounded-xl border border-dashed border-neutral-200 dark:border-neutral-700">
                    <iconify-icon icon="lucide:send" class="text-4xl text-neutral-300 mb-2"></iconify-icon>
                    <p class="text-xs text-neutral-500 mb-0">Chưa có thông báo nào được gửi.</p>
                </div>
                @endif
            </div>

            {{-- TAB 3: SETTINGS --}}
            @elseif($tab === 'settings' && (auth()->user()->hasRole('Admin') || auth()->user()->can('manage notifications')))
            <div class="max-w-2xl space-y-6">
                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 text-blue-900 text-xs sm:text-sm flex items-start gap-3">
                    <iconify-icon icon="lucide:info" class="text-blue-600 text-xl shrink-0 mt-0.5"></iconify-icon>
                    <div class="leading-relaxed">
                        <strong class="font-bold">Quy tắc gửi thông báo khi có đơn hàng mới (đẩy đơn):</strong>
                        <ul class="list-disc pl-4 mt-1.5 space-y-1">
                            <li><strong>Nhóm thị trường:</strong> Tất cả nhân viên phụ trách Nhóm thị trường của khách hàng trong đơn sẽ <span class="text-emerald-700 font-semibold">luôn luôn tự động nhận thông báo</span>.</li>
                            <li><strong>Chức vụ mặc định:</strong> Chọn các chức vụ bên dưới để luôn nhận thông báo mỗi khi có đơn hàng mới bất kể thuộc nhóm thị trường nào.</li>
                        </ul>
                    </div>
                </div>

                <form method="POST" action="{{ route('notifications.settings') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-3">
                            Chức vụ luôn nhận thông báo khi có đơn mới:
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($roles as $role)
                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 cursor-pointer transition-colors">
                                <input type="checkbox" name="default_roles[]" value="{{ $role->name }}" 
                                       {{ in_array($role->name, $defaultNotifyRoles) || $role->name === 'Admin' ? 'checked' : '' }}
                                       {{ $role->name === 'Admin' ? 'disabled' : '' }}
                                       class="rounded text-primary-600 focus:ring-primary-500 w-4 h-4">
                                @if($role->name === 'Admin')
                                <input type="hidden" name="default_roles[]" value="Admin">
                                @endif
                                <div>
                                    <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $role->name }}</span>
                                    @if($role->name === 'Admin')
                                    <span class="text-[10px] bg-neutral-200 text-neutral-600 px-1.5 py-0.5 rounded ml-1 font-bold">Mặc định</span>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 border-t border-neutral-200 dark:border-neutral-700">
                        <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl px-5 py-2.5 text-sm flex items-center gap-2">
                            <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                            <span>Lưu cài đặt thông báo</span>
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL TẠO THÔNG BÁO MỚI -->
<div id="modalCreateNotification" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-scale-up">
        <!-- Modal Header -->
        <div class="p-4 sm:p-5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between bg-neutral-50/50 dark:bg-neutral-800/40">
            <h5 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-2 mb-0">
                <iconify-icon icon="lucide:send" class="text-primary-600"></iconify-icon>
                <span>Gửi thông báo nội bộ</span>
            </h5>
            <button type="button" onclick="closeCreateNotificationModal()" class="text-neutral-400 hover:text-neutral-600 p-1 rounded-lg">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="{{ route('notifications.store') }}" class="p-4 sm:p-6 overflow-y-auto space-y-4">
            @csrf

            <!-- Tiêu đề & Loại -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 uppercase mb-1.5">
                        Tiêu đề thông báo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" required placeholder="Ví dụ: Thông báo họp triển khai đơn hàng gấp..."
                           class="form-control text-sm py-2 px-3 rounded-xl border border-neutral-300 dark:border-neutral-600 w-full focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 uppercase mb-1.5">
                        Mức độ / Loại
                    </label>
                    <select name="type" class="form-select text-sm py-2 px-3 rounded-xl border border-neutral-300 dark:border-neutral-600 w-full bg-white dark:bg-neutral-800">
                        <option value="manual">Thông báo chung</option>
                        <option value="urgent">Khẩn cấp / Quan trọng</option>
                        <option value="broadcast">Tin toàn công ty</option>
                    </select>
                </div>
            </div>

            <!-- Đối tượng nhận thông báo (Radio Selector) -->
            <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 uppercase mb-2">
                    Gửi tới đối tượng nào? <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <input type="radio" name="target_type" value="all" checked onchange="toggleTargetGroup('all')" class="text-primary-600">
                        <span class="text-xs font-semibold">Toàn phần mềm</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <input type="radio" name="target_type" value="market_group" onchange="toggleTargetGroup('market_group')" class="text-primary-600">
                        <span class="text-xs font-semibold">Nhóm thị trường</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <input type="radio" name="target_type" value="role" onchange="toggleTargetGroup('role')" class="text-primary-600">
                        <span class="text-xs font-semibold">Theo chức vụ</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <input type="radio" name="target_type" value="user" onchange="toggleTargetGroup('user')" class="text-primary-600">
                        <span class="text-xs font-semibold">Người cụ thể</span>
                    </label>
                </div>
            </div>

            <!-- Target ID containers (Ẩn/Hiện tương ứng) -->
            <div id="targetContainerMarketGroup" class="hidden p-3.5 bg-neutral-50 dark:bg-neutral-800/40 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <span class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-2">Chọn nhóm thị trường nhận:</span>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($marketGroups as $mg)
                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                        <input type="checkbox" name="target_ids[]" value="{{ $mg->id }}" class="rounded text-primary-600">
                        <span>{{ $mg->name }} ({{ $mg->code }})</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="targetContainerRole" class="hidden p-3.5 bg-neutral-50 dark:bg-neutral-800/40 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <span class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-2">Chọn chức vụ nhận:</span>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($roles as $r)
                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                        <input type="checkbox" name="target_ids[]" value="{{ $r->name }}" class="rounded text-primary-600">
                        <span>{{ $r->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="targetContainerUser" class="hidden p-3.5 bg-neutral-50 dark:bg-neutral-800/40 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <span class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-2">Chọn nhân viên nhận:</span>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto">
                    @foreach($users as $u)
                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                        <input type="checkbox" name="target_ids[]" value="{{ $u->id }}" class="rounded text-primary-600">
                        <span class="truncate">{{ $u->name }} ({{ $u->user_code }})</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Link điều hướng (Tùy chọn) -->
            <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 uppercase mb-1">
                    Link liên kết (tùy chọn)
                </label>
                <input type="text" name="link" placeholder="Ví dụ: /orders/123 hoặc đường link cần mở khi bấm vào thông báo"
                       class="form-control text-sm py-2 px-3 rounded-xl border border-neutral-300 dark:border-neutral-600 w-full">
            </div>

            <!-- Nội dung thông báo -->
            <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 uppercase mb-1.5">
                    Nội dung thông báo <span class="text-red-500">*</span>
                </label>
                <textarea name="content" rows="4" required placeholder="Nhập nội dung thông báo chi tiết..."
                          class="form-control text-sm py-2 px-3 rounded-xl border border-neutral-300 dark:border-neutral-600 w-full focus:ring-2 focus:ring-primary-500"></textarea>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-neutral-200 dark:border-neutral-700 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCreateNotificationModal()" class="btn bg-neutral-200 hover:bg-neutral-300 text-neutral-800 rounded-xl px-4 py-2 text-xs font-semibold">
                    Hủy
                </button>
                <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white rounded-xl px-5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <iconify-icon icon="lucide:send" class="text-sm"></iconify-icon>
                    <span>Gửi thông báo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateNotificationModal() {
    const modal = document.getElementById('modalCreateNotification');
    if (modal) modal.classList.remove('hidden');
}

function closeCreateNotificationModal() {
    const modal = document.getElementById('modalCreateNotification');
    if (modal) modal.classList.add('hidden');
}

function toggleTargetGroup(type) {
    const mg = document.getElementById('targetContainerMarketGroup');
    const role = document.getElementById('targetContainerRole');
    const user = document.getElementById('targetContainerUser');

    mg.classList.add('hidden');
    role.classList.add('hidden');
    user.classList.add('hidden');

    if (type === 'market_group') mg.classList.remove('hidden');
    if (type === 'role') role.classList.remove('hidden');
    if (type === 'user') user.classList.remove('hidden');
}
</script>
@endsection
