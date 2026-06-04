<?php

namespace App\Services;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\ManufactureOrder;
use Illuminate\Support\Facades\Auth;

class PressingService
{
    /**
     * Get Pressing history logs sorted by time descending.
     */
    public function getHistory()
    {
        $acrylicCodes = AcrylicOrderItemCode::whereNotNull('status')
            ->with('acrylicOrderItem')
            ->get();
        $glassCodes = GlassOrderItemCode::whereNotNull('status')
            ->with('glassOrderItem')
            ->get();
        $minLateCodes = MinLateOrderItemCode::whereNotNull('status')
            ->with('minLateOrderItem')
            ->get();

        $history = collect();
        $pressingActions = ['làm lệnh ép', 'xuất kho ván', 'ép đơn', 'ép dự trữ', 'quay lại ép'];

        foreach ($acrylicCodes as $code) {
            $item = $code->acrylicOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($glassCodes as $code) {
            $item = $code->glassOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($minLateCodes as $code) {
            $item = $code->minLateOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        return $history->sortByDesc('time')->values();
    }

    /**
     * Process completing or rolling back Pressing (bulk or single).
     */
    public function completeOrRollback(array $data): array
    {
        $codeStr = trim($data['product_code']);
        $notes = $data['notes'] ?? null;
        $actionType = $data['action_type'];
        $logTime = now()->toDateTimeString();
        $operatorName = Auth::user()->name ?? 'Hệ thống';

        $pressingActions = ['làm lệnh ép', 'xuất kho ván', 'ép đơn', 'ép dự trữ'];

        // Try to find a ManufactureOrder by this code first (bulk operation)
        $manufacture = ManufactureOrder::with('orders.supplies')->where('code', $codeStr)->first();
        if ($manufacture) {
            $items = $manufacture->getAllItems();
            if ($items->isEmpty()) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Lệnh sản xuất này không có sản phẩm nào.'
                ];
            }

            $updatedCount = 0;
            foreach ($items as $itemObj) {
                $codeRecord = null;
                if ($itemObj->type === 'acrylic') {
                    $codeRecord = AcrylicOrderItemCode::find($itemObj->id);
                } elseif ($itemObj->type === 'glass') {
                    $codeRecord = GlassOrderItemCode::find($itemObj->id);
                } elseif ($itemObj->type === 'min_late') {
                    $codeRecord = MinLateOrderItemCode::find($itemObj->id);
                }

                if ($codeRecord) {
                    $currentStatus = $codeRecord->status ?? [];
                    
                    // Find latest pressing log
                    $latestPressingLog = null;
                    $latestPressingIndex = -1;
                    for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
                        $log = $currentStatus[$i];
                        if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                            $latestPressingLog = $log;
                            $latestPressingIndex = $i;
                            break;
                        }
                    }

                    if ($actionType === 'rollback') {
                        if ($latestPressingLog && $latestPressingIndex !== -1) {
                            $currentStatus = array_slice($currentStatus, 0, $latestPressingIndex);
                            $newLog = [
                                'action' => 'quay lại ép',
                                'operator' => $operatorName,
                                'operator_id' => Auth::id(),
                                'time' => $logTime,
                                'notes' => $notes,
                            ];
                            $currentStatus[] = $newLog;
                            $codeRecord->status = $currentStatus;
                            $codeRecord->save();
                            $updatedCount++;
                        }
                    } else {
                        // Avoid duplicate consecutive action
                        if (!$latestPressingLog || $latestPressingLog['action'] !== $actionType) {
                            $newLog = [
                                'action' => $actionType,
                                'operator' => $operatorName,
                                'operator_id' => Auth::id(),
                                'time' => $logTime,
                                'notes' => $notes,
                            ];
                            $currentStatus[] = $newLog;
                            $codeRecord->status = $currentStatus;
                            $codeRecord->save();
                            $updatedCount++;
                        }
                    }
                }
            }

            if ($updatedCount === 0) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Tất cả sản phẩm trong lệnh này đã ở trạng thái này hoặc không thể hoàn tác.'
                ];
            }

            $msg = $actionType === 'rollback'
                ? "Đã hoàn tác (quay lại) quy trình ép ván cho {$updatedCount} sản phẩm trong Lệnh sản xuất {$codeStr}."
                : "Đã ghi nhận bước \"" . ucwords($actionType) . "\" cho {$updatedCount} sản phẩm trong Lệnh sản xuất {$codeStr}.";

            return [
                'success' => true,
                'status_code' => 200,
                'is_bulk' => true,
                'message' => $msg,
                'data' => [
                    'product_code' => $codeStr,
                    'product_name' => "Lệnh sản xuất: {$codeStr}",
                    'type' => 'manufacture_order',
                    'operator' => $operatorName,
                    'time' => $logTime,
                    'notes' => $notes ?? $msg,
                    'action' => $actionType === 'rollback' ? 'quay lại ép' : $actionType,
                ]
            ];
        }

        // Single plate item code scan
        $codeRecord = AcrylicOrderItemCode::where('product_id', $codeStr)->first();
        $type = 'acrylic';
        $nameField = 'product_name';
        $item = null;

        if ($codeRecord) {
            $item = $codeRecord->acrylicOrderItem;
        } else {
            $codeRecord = GlassOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'glass';
            $nameField = 'product_name';
            if ($codeRecord) {
                $item = $codeRecord->glassOrderItem;
            }
        }

        if (!$codeRecord) {
            $codeRecord = MinLateOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'min_late';
            $nameField = 'name';
            if ($codeRecord) {
                $item = $codeRecord->minLateOrderItem;
            }
        }

        if (!$codeRecord || !$item) {
            return [
                'success' => false,
                'status_code' => 404,
                'message' => 'Không tìm thấy sản phẩm hoặc lệnh sản xuất với mã định danh: ' . $codeStr
            ];
        }

        $currentStatus = $codeRecord->status ?? [];
        
        $latestPressingLog = null;
        $latestPressingIndex = -1;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                $latestPressingLog = $log;
                $latestPressingIndex = $i;
                break;
            }
        }

        if ($actionType === 'rollback') {
            if (!$latestPressingLog || $latestPressingIndex === -1) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Sản phẩm này chưa ghi nhận quy trình ép ván nên không thể quay lại.'
                ];
            }

            $currentStatus = array_slice($currentStatus, 0, $latestPressingIndex);
            $newLog = [
                'action' => 'quay lại ép',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];
            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $msg = "Đã hoàn tác (quay lại) quy trình ép ván cho sản phẩm {$codeStr}.";
        } else {
            if ($latestPressingLog && $latestPressingLog['action'] === $actionType) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => "Sản phẩm này đã được ghi nhận bước \"" . ucwords($actionType) . "\" trước đó."
                ];
            }

            $newLog = [
                'action' => $actionType,
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];
            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $item->notes = $notes;
            $item->save();

            $msg = "Đã ghi nhận bước \"" . ucwords($actionType) . "\" cho sản phẩm {$codeStr} thành công.";
        }

        $productName = $item->$nameField ?? '—';

        return [
            'success' => true,
            'status_code' => 200,
            'is_bulk' => false,
            'message' => $msg,
            'data' => [
                'product_code' => $codeRecord->product_id,
                'product_name' => $productName,
                'type' => $type,
                'operator' => $operatorName,
                'time' => $logTime,
                'notes' => $notes ?? $msg,
                'action' => $actionType === 'rollback' ? 'quay lại ép' : $actionType,
            ]
        ];
    }
}
