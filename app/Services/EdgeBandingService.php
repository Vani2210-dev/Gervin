<?php

namespace App\Services;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use Illuminate\Support\Facades\Auth;

class EdgeBandingService
{
    /**
     * Get Edge Banding history logs sorted by time descending.
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'edge_banding_length' => $log['edge_banding_length'] ?? 0,
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'edge_banding_length' => $log['edge_banding_length'] ?? 0,
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'edge_banding_length' => $log['edge_banding_length'] ?? 0,
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        return $history->sortByDesc('time')->values();
    }

    /**
     * Get product status by code, check if 'lỗi dán cạnh' stage exists.
     */
    public function getProductStatus(string $codeStr): array
    {
        $codeStr = trim($codeStr);

        $codeRecord = AcrylicOrderItemCode::where('product_id', $codeStr)->first();
        $item = null;

        if ($codeRecord) {
            $item = $codeRecord->acrylicOrderItem;
        } else {
            $codeRecord = GlassOrderItemCode::where('product_id', $codeStr)->first();
            if ($codeRecord) {
                $item = $codeRecord->glassOrderItem;
            }
        }

        if (!$codeRecord) {
            $codeRecord = MinLateOrderItemCode::where('product_id', $codeStr)->first();
            if ($codeRecord) {
                $item = $codeRecord->minLateOrderItem;
            }
        }

        if (!$codeRecord || !$item) {
            return [
                'success'     => false,
                'status_code' => 404,
                'message'     => 'Không tìm thấy sản phẩm với mã: ' . $codeStr,
            ];
        }

        $statusLogs = $codeRecord->status ?? [];

        $hasLoiDanCanh = collect($statusLogs)->contains(function ($log) {
            return isset($log['action']) && mb_strtolower($log['action']) === 'lỗi dán cạnh';
        });

        return [
            'success'         => true,
            'status_code'     => 200,
            'has_loi_dan_canh' => $hasLoiDanCanh,
        ];
    }

    /**
     * Process completing or rolling back Edge Banding for a product code.
     */
    public function completeOrRollback(array $data): array
    {
        $codeStr = trim($data['product_code']);
        $notes = $data['notes'] ?? null;
        $length = $data['edge_banding_length'] ?? null;
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

        // Find the latest Edge Banding stage log
        $latestEdgeLog = null;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                $latestEdgeLog = $log;
                break;
            }
        }

        // Find the index of the latest 'hoàn thành dán cạnh' log
        $lastCompletedEdgeIndex = -1;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && $log['action'] === 'hoàn thành dán cạnh') {
                $lastCompletedEdgeIndex = $i;
                break;
            }
        }

        if ($actionType === 'rollback') {
            if (!$latestEdgeLog || $latestEdgeLog['action'] !== 'hoàn thành dán cạnh' || $lastCompletedEdgeIndex === -1) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Sản phẩm này chưa ghi nhận hoàn thành dán cạnh nên không thể quay lại.'
                ];
            }

            $currentStatus = array_slice($currentStatus, 0, $lastCompletedEdgeIndex);

            $newLog = [
                'action' => 'quay lại dán cạnh',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
                'edge_banding_length' => $length ?? 0,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $message = 'Đã hoàn tác (quay lại) công đoạn dán cạnh cho sản phẩm này.';
        } else {
            if ($latestEdgeLog && $latestEdgeLog['action'] === 'hoàn thành dán cạnh') {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Sản phẩm này đã được ghi nhận hoàn thành dán cạnh trước đó.'
                ];
            }

            // Xóa tất cả các action của công đoạn dán cạnh trước đó (hoàn thành, quay lại, lỗi...)
            $currentStatus = array_values(array_filter($currentStatus, function ($log) {
                $action = mb_strtolower($log['action'] ?? '');
                return !str_contains($action, 'dán cạnh');
            }));

            $newLog = [
                'action' => 'hoàn thành dán cạnh',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
                'edge_banding_length' => $length ?? 0,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $item->notes = $notes;
            $item->save();

            $message = 'Xác nhận hoàn thành công đoạn dán cạnh thành công.';
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
                'notes' => $notes ?? ($actionType === 'complete' ? 'Hoàn thành dán cạnh' : 'Quay lại dán cạnh'),
                'edge_banding_length' => $length ?? 0,
            ]
        ];
    }
}
