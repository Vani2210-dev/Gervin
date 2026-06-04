<?php

namespace App\Services;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use Illuminate\Support\Facades\Auth;

class FinishingService
{
    /**
     * Get Finishing history logs sorted by time descending.
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

        foreach ($acrylicCodes as $code) {
            $item = $code->acrylicOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
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
     * Process completing or rolling back Finishing for a product code.
     */
    public function completeOrRollback(array $data): array
    {
        $codeStr = trim($data['product_code']);
        $notes = $data['notes'] ?? null;
        $actionType = $data['action_type'] ?? 'complete';

        // Search in Acrylic
        $codeRecord = AcrylicOrderItemCode::where('product_id', $codeStr)->first();
        $type = 'acrylic';
        $nameField = 'product_name';
        $item = null;

        if ($codeRecord) {
            $item = $codeRecord->acrylicOrderItem;
        } else {
            // Search in Glass
            $codeRecord = GlassOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'glass';
            $nameField = 'product_name';
            if ($codeRecord) {
                $item = $codeRecord->glassOrderItem;
            }
        }

        if (!$codeRecord) {
            // Search in Min Late
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
                'message' => 'Không tìm thấy sản phẩm với mã định danh: ' . $codeStr
            ];
        }

        $currentStatus = $codeRecord->status ?? [];
        $logTime = now()->toDateTimeString();
        $operatorName = Auth::user()->name ?? 'Hệ thống';

        // Find the latest Finishing log
        $latestFinishingLog = null;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
                $latestFinishingLog = $log;
                break;
            }
        }

        // Find the index of the latest 'hoàn thành làm đẹp' log
        $lastCompletedFinishingIndex = -1;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && $log['action'] === 'hoàn thành làm đẹp') {
                $lastCompletedFinishingIndex = $i;
                break;
            }
        }

        if ($actionType === 'rollback') {
            if (!$latestFinishingLog || $latestFinishingLog['action'] !== 'hoàn thành làm đẹp' || $lastCompletedFinishingIndex === -1) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Sản phẩm này chưa ghi nhận hoàn thành làm đẹp nên không thể quay lại.'
                ];
            }

            $currentStatus = array_slice($currentStatus, 0, $lastCompletedFinishingIndex);

            $newLog = [
                'action' => 'quay lại làm đẹp',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $message = 'Đã hoàn tác (quay lại) công đoạn làm đẹp cho sản phẩm này.';
        } else {
            if ($latestFinishingLog && $latestFinishingLog['action'] === 'hoàn thành làm đẹp') {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Sản phẩm này đã được ghi nhận hoàn thành làm đẹp trước đó.'
                ];
            }

            $newLog = [
                'action' => 'hoàn thành làm đẹp',
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

            $message = 'Xác nhận hoàn thành công đoạn làm đẹp thành công.';
        }

        $productName = $item->$nameField ?? '—';

        return [
            'success' => true,
            'status_code' => 200,
            'action_type' => $actionType,
            'message' => $message,
            'data' => [
                'product_code' => $codeRecord->product_id,
                'product_name' => $productName,
                'type' => $type,
                'operator' => $operatorName,
                'time' => $logTime,
                'notes' => $notes ?? ($actionType === 'complete' ? 'Hoàn thành làm đẹp' : 'Quay lại làm đẹp'),
            ]
        ];
    }
}
