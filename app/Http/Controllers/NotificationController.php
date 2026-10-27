<?php

namespace App\Http\Controllers;

use App\Models\MarketGroup;
use App\Models\NotificationRecipient;
use App\Models\NotificationSetting;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Màn hình danh sách & quản lý thông báo.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->input('tab', 'inbox'); // inbox, sent, settings

        // Danh sách thông báo đã nhận
        $inboxQuery = NotificationRecipient::with(['notification.sender'])
            ->where('user_id', $user->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->search;
                $q->whereHas('notification', function ($sq) use ($term) {
                    $sq->where('title', 'like', "%{$term}%")
                       ->orWhere('content', 'like', "%{$term}%");
                });
            })
            ->when($request->input('filter_status') === 'unread', function ($q) {
                $q->where('is_read', false);
            })
            ->orderBy('id', 'desc');

        $inboxList = $inboxQuery->paginate(15, ['*'], 'inbox_page')->withQueryString();

        // Danh sách thông báo đã gửi (dành cho Admin hoặc người gửi)
        $sentList = collect();
        if ($user->hasRole('Admin') || $user->can('manage notifications')) {
            $sentQuery = SystemNotification::with(['sender', 'recipients'])
                ->when(!$user->hasRole('Admin'), fn($q) => $q->where('sender_id', $user->id))
                ->orderBy('id', 'desc');
            $sentList = $sentQuery->paginate(15, ['*'], 'sent_page')->withQueryString();
        }

        $marketGroups = MarketGroup::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $users = User::select('id', 'name', 'user_code')->orderBy('name')->get();

        $defaultNotifyRoles = NotificationSetting::get('default_notify_roles', ['Admin']);
        if (!is_array($defaultNotifyRoles)) {
            $defaultNotifyRoles = ['Admin'];
        }

        $unreadCount = NotificationService::getUnreadCount($user);

        return view('notifications.index', compact(
            'inboxList',
            'sentList',
            'marketGroups',
            'roles',
            'users',
            'defaultNotifyRoles',
            'unreadCount',
            'tab'
        ));
    }

    /**
     * Gửi thông báo thủ công (theo Nhóm thị trường, Chức vụ, Người, hoặc Toàn phần mềm).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string|max:2000',
            'target_type' => 'required|in:all,market_group,role,user',
            'target_ids'  => 'nullable|array',
            'target_ids.*'=> 'nullable',
            'link'        => 'nullable|string|max:500',
            'type'        => 'nullable|string|in:manual,broadcast,urgent',
        ]);

        $targetType = $request->target_type;
        $targetIds = $request->input('target_ids', []);

        if ($targetType !== 'all' && empty($targetIds)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vui lòng chọn ít nhất một đối tượng nhận thông báo.');
        }

        $notification = NotificationService::sendManualNotification(
            $request->title,
            $request->content,
            $targetType,
            $targetIds,
            $request->link,
            auth()->id(),
            $request->input('type', 'manual')
        );

        if (!$notification) {
            return redirect()->back()->with('error', 'Không tìm thấy người nhận nào phù hợp với đối tượng đã chọn.');
        }

        return redirect()->route('notifications.index', ['tab' => 'sent'])->with('success', 'Đã gửi thông báo thành công!');
    }

    /**
     * Lưu cấu hình chức vụ nhận thông báo đơn mới.
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'default_roles' => 'nullable|array',
            'default_roles.*' => 'string',
        ]);

        $roles = $request->input('default_roles', ['Admin']);
        if (!in_array('Admin', $roles)) {
            $roles[] = 'Admin'; // Admin luôn mặc định có mặt
        }

        NotificationSetting::set('default_notify_roles', $roles);

        return redirect()->route('notifications.index', ['tab' => 'settings'])->with('success', 'Đã lưu cài đặt đối tượng nhận thông báo đơn mới.');
    }

    /**
     * Xóa thông báo khỏi hộp thư của người dùng.
     */
    public function destroy($id)
    {
        $recipient = NotificationRecipient::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $recipient->delete();

        return redirect()->back()->with('success', 'Đã xóa thông báo khỏi hộp thư.');
    }

    /**
     * API: Lấy danh sách thông báo cho Navbar quả chuông & Polling realtime.
     */
    public function apiIndex(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $limit = (int) $request->input('limit', 15);
        $recipients = NotificationRecipient::with(['notification.sender'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();

        $unreadCount = NotificationRecipient::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $items = $recipients->map(function ($r) {
            $notif = $r->notification;
            if (!$notif) return null;

            // Icon & Color mapping
            $icon = 'lucide:bell';
            $color = 'text-primary-600 bg-primary-50';

            if ($notif->type === 'order_created') {
                $icon = 'lucide:shopping-bag';
                $color = 'text-emerald-600 bg-emerald-50';
            } elseif ($notif->type === 'order_status_changed') {
                $icon = 'lucide:refresh-cw';
                $color = 'text-blue-600 bg-blue-50';
            } elseif ($notif->type === 'order_updated') {
                $icon = 'lucide:edit-3';
                $color = 'text-amber-600 bg-amber-50';
            } elseif ($notif->type === 'urgent') {
                $icon = 'lucide:alert-triangle';
                $color = 'text-danger-600 bg-danger-50';
            }

            return [
                'recipient_id' => $r->id,
                'id'           => $notif->id,
                'title'        => $notif->title,
                'content'      => $notif->content,
                'link'         => $notif->link,
                'is_read'      => (bool) $r->is_read,
                'time_ago'     => NotificationService::formatRelativeTime($notif->created_at),
                'created_at'   => $notif->created_at->toIso8601String(),
                'type'         => $notif->type,
                'icon'         => $icon,
                'color'        => $color,
                'sender_name'  => $notif->sender?->name ?? 'Hệ thống',
            ];
        })->filter()->values();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $items,
        ]);
    }

    /**
     * API: Đánh dấu đã đọc 1 thông báo.
     */
    public function apiMarkAsRead(Request $request, $id)
    {
        $userId = auth()->id();
        $ok = NotificationService::markAsRead((int)$id, $userId);

        return response()->json([
            'success'      => $ok,
            'unread_count' => NotificationService::getUnreadCount(auth()->user()),
        ]);
    }

    /**
     * API: Đánh dấu tất cả thông báo là đã đọc.
     */
    public function apiMarkAllAsRead(Request $request)
    {
        $count = NotificationService::markAllAsRead(auth()->id());

        return response()->json([
            'success'      => true,
            'count'        => $count,
            'unread_count' => 0,
        ]);
    }
}
