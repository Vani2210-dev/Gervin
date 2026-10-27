<?php

namespace App\Services;

use App\Models\NotificationRecipient;
use App\Models\NotificationSetting;
use App\Models\Order;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Lấy danh sách ID người dùng theo mục tiêu gửi.
     */
    public static function resolveUserIds(string $targetType, array $targetIds = []): array
    {
        $userIds = [];

        switch ($targetType) {
            case 'all':
                $userIds = User::pluck('id')->all();
                break;

            case 'market_group':
                if (!empty($targetIds)) {
                    $userIds = DB::table('market_group_user')
                        ->whereIn('market_group_id', $targetIds)
                        ->pluck('user_id')
                        ->all();
                }
                break;

            case 'role':
                if (!empty($targetIds)) {
                    $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $targetIds)
                        ->orWhereIn('name', $targetIds)
                        ->pluck('name')
                        ->all();
                    $roleIds = \Spatie\Permission\Models\Role::whereIn('id', $targetIds)
                        ->orWhereIn('name', $targetIds)
                        ->pluck('id')
                        ->all();

                    $userIds = User::where(function ($q) use ($roleNames, $roleIds) {
                        $q->whereHas('roles', fn($sq) => $sq->whereIn('name', $roleNames))
                          ->orWhereIn('role_id', $roleIds);
                    })->pluck('id')->all();
                }
                break;

            case 'user':
                $userIds = array_map('intval', $targetIds);
                break;
        }

        return array_values(array_unique(array_filter($userIds)));
    }

    /**
     * Tạo thông báo và phân phối tới danh sách người nhận.
     */
    public static function createNotification(array $data, array $recipientUserIds): ?SystemNotification
    {
        $recipientUserIds = array_values(array_unique(array_filter($recipientUserIds)));
        if (empty($recipientUserIds)) {
            return null;
        }

        return DB::transaction(function () use ($data, $recipientUserIds) {
            $notification = SystemNotification::create([
                'sender_id'   => $data['sender_id'] ?? auth()->id(),
                'type'        => $data['type'] ?? 'manual',
                'title'       => $data['title'],
                'content'     => $data['content'],
                'link'        => $data['link'] ?? null,
                'target_type' => $data['target_type'] ?? 'user',
                'target_ids'  => $data['target_ids'] ?? null,
                'data'        => $data['data'] ?? null,
            ]);

            $now = now();
            $records = [];
            foreach ($recipientUserIds as $uId) {
                $records[] = [
                    'notification_id' => $notification->id,
                    'user_id'         => $uId,
                    'is_read'         => false,
                    'read_at'         => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            NotificationRecipient::insert($records);

            return $notification;
        });
    }

    /**
     * Gửi thông báo thủ công từ giao diện Quản lý thông báo.
     */
    public static function sendManualNotification(
        string $title,
        string $content,
        string $targetType,
        array $targetIds = [],
        ?string $link = null,
        ?int $senderId = null,
        string $type = 'manual'
    ): ?SystemNotification {
        $recipientUserIds = self::resolveUserIds($targetType, $targetIds);

        return self::createNotification([
            'sender_id'   => $senderId ?: auth()->id(),
            'type'        => $type,
            'title'       => $title,
            'content'     => $content,
            'link'        => $link,
            'target_type' => $targetType,
            'target_ids'  => $targetIds,
        ], $recipientUserIds);
    }

    /**
     * Lấy danh sách User nhận thông báo của một đơn hàng.
     * Gồm: Nhân viên thuộc Nhóm thị trường của khách + Các Chức vụ nhận mặc định (Admin, Quản lý...).
     */
    public static function getOrderNotificationRecipients(Order $order): array
    {
        $recipientUserIds = [];

        // 1. Nhân viên thuộc nhóm thị trường của khách hàng
        $marketGroupId = $order->customer?->market_group_id;
        if ($marketGroupId) {
            $mgUsers = DB::table('market_group_user')
                ->where('market_group_id', $marketGroupId)
                ->pluck('user_id')
                ->all();
            $recipientUserIds = array_merge($recipientUserIds, $mgUsers);
        }

        // 2. Chức vụ nhận thông báo mặc định (cấu hình trong setting hoặc mặc định Admin)
        $defaultRoles = (array) NotificationSetting::get('default_notify_roles', ['Admin']);
        if (!in_array('Admin', $defaultRoles)) {
            $defaultRoles[] = 'Admin';
        }

        $roleNames = array_values(array_unique(array_filter($defaultRoles)));
        $roleIds = \Spatie\Permission\Models\Role::whereIn('name', $roleNames)->pluck('id')->all();

        $roleUserIds = User::where(function ($q) use ($roleNames, $roleIds) {
            $q->whereHas('roles', fn($sq) => $sq->whereIn('name', $roleNames))
              ->orWhereIn('role_id', $roleIds);
        })->pluck('id')->all();

        $recipientUserIds = array_merge($recipientUserIds, $roleUserIds);

        return array_values(array_unique(array_filter($recipientUserIds)));
    }

    /**
     * Bắn thông báo khi có đơn hàng mới (mới đẩy đơn).
     */
    public static function notifyOrderCreated(Order $order): ?SystemNotification
    {
        $recipients = self::getOrderNotificationRecipients($order);
        if (empty($recipients)) {
            return null;
        }

        $typeNames = [
            'acrylic'  => 'Acrylic',
            'glass'    => 'Kính nhôm',
            'min_late' => 'Min/Late',
            'Flywood'  => 'Flywood',
            'service'  => 'Gia công dịch vụ',
        ];
        $typeName = $typeNames[$order->type] ?? ucfirst($order->type);

        $totalMoneyStr = number_format($order->total_amount, 0, ',', '.') . 'đ';
        $title = "Đơn hàng mới: {$order->order_code}";
        $content = "Khách: {$order->customer_name} | Trị giá: {$totalMoneyStr} ({$typeName})";
        $link = route('orders.show', $order);

        return self::createNotification([
            'sender_id'   => auth()->id(),
            'type'        => 'order_created',
            'title'       => $title,
            'content'     => $content,
            'link'        => $link,
            'target_type' => 'order_stakeholders',
            'target_ids'  => $recipients,
            'data'        => [
                'order_id'      => $order->id,
                'order_code'    => $order->order_code,
                'customer_name' => $order->customer_name,
                'total_amount'  => $order->total_amount,
                'type'          => $order->type,
            ],
        ], $recipients);
    }

    /**
     * Bắn thông báo khi trạng thái đơn thay đổi (kế toán duyệt, kỹ thuật duyệt, chuyển sản xuất, hủy...).
     */
    public static function notifyOrderStatusChanged(Order $order, string $statusAction, ?string $extraNote = null): ?SystemNotification
    {
        $recipients = self::getOrderNotificationRecipients($order);
        if (empty($recipients)) {
            return null;
        }

        $statusLabels = [
            'accountant_approve'   => 'Kế toán đã duyệt đơn',
            'accountant_unapprove' => 'Kế toán đã hủy duyệt',
            'transferred'          => 'Kỹ thuật duyệt & Chuyển sản xuất',
            'request_edit'         => 'Yêu cầu sửa đơn hàng',
            'cancelled'            => 'Đã hủy đơn hàng',
            'in_production'        => 'Đang tiến hành sản xuất',
            'completed'            => 'Đã hoàn thành sản xuất',
        ];

        $actionText = $statusLabels[$statusAction] ?? $statusAction;
        $title = "Cập nhật đơn: {$order->order_code}";
        $content = "{$actionText} - Khách: {$order->customer_name}" . ($extraNote ? " (Lý do: {$extraNote})" : '');
        $link = route('orders.show', $order);

        return self::createNotification([
            'sender_id'   => auth()->id(),
            'type'        => 'order_status_changed',
            'title'       => $title,
            'content'     => $content,
            'link'        => $link,
            'target_type' => 'order_stakeholders',
            'target_ids'  => $recipients,
            'data'        => [
                'order_id'   => $order->id,
                'order_code' => $order->order_code,
                'status'     => $statusAction,
            ],
        ], $recipients);
    }

    /**
     * Bắn thông báo khi đơn hàng được cập nhật/chỉnh sửa nội dung.
     */
    public static function notifyOrderUpdated(Order $order): ?SystemNotification
    {
        $recipients = self::getOrderNotificationRecipients($order);
        if (empty($recipients)) {
            return null;
        }

        $userName = auth()->user()?->name ?? 'Người dùng';
        $title = "Đơn {$order->order_code} vừa được chỉnh sửa";
        $content = "{$userName} vừa cập nhật nội dung đơn hàng (Khách: {$order->customer_name})";
        $link = route('orders.show', $order);

        return self::createNotification([
            'sender_id'   => auth()->id(),
            'type'        => 'order_updated',
            'title'       => $title,
            'content'     => $content,
            'link'        => $link,
            'target_type' => 'order_stakeholders',
            'target_ids'  => $recipients,
            'data'        => [
                'order_id'   => $order->id,
                'order_code' => $order->order_code,
            ],
        ], $recipients);
    }

    /**
     * Lấy danh sách thông báo của người dùng kèm phân trang / giới hạn.
     */
    public static function getUserNotifications(User $user, int $limit = 20, bool $unreadOnly = false)
    {
        $query = NotificationRecipient::with(['notification.sender'])
            ->where('user_id', $user->id)
            ->when($unreadOnly, fn($q) => $q->where('is_read', false))
            ->orderBy('id', 'desc');

        return $query->take($limit)->get();
    }

    /**
     * Lấy số lượng thông báo chưa đọc của người dùng.
     */
    public static function getUnreadCount(User $user): int
    {
        return NotificationRecipient::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc.
     */
    public static function markAsRead(int $recipientId, int $userId): bool
    {
        return (bool) NotificationRecipient::where('id', $recipientId)
            ->where('user_id', $userId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Đánh dấu tất cả thông báo của user là đã đọc.
     */
    public static function markAllAsRead(int $userId): int
    {
        return NotificationRecipient::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Định dạng thời gian thân thiện (Ví dụ: Vừa xong, 5 phút trước...).
     */
    public static function formatRelativeTime($carbonDate): string
    {
        if (!$carbonDate) return '';
        $diffSec = now()->diffInSeconds($carbonDate);
        if ($diffSec < 60) {
            return 'Vừa xong';
        }
        if ($diffSec < 3600) {
            return floor($diffSec / 60) . ' phút trước';
        }
        if ($diffSec < 86400) {
            return floor($diffSec / 3600) . ' giờ trước';
        }
        if ($diffSec < 86400 * 2) {
            return 'Hôm qua';
        }
        return $carbonDate->format('d/m/Y H:i');
    }
}
