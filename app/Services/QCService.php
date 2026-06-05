<?php

namespace App\Services;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use Illuminate\Support\Facades\Auth;

class QCService
{
    /**
     * Get QC history logs sorted by time descending.
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
                $action = $log['action'] ?? '';
                if ($action === 'Hoàn thành QC' || $action === 'Ghi nhận lỗi' || stripos($action, 'lỗi') === 0) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $action,
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
                $action = $log['action'] ?? '';
                if ($action === 'Hoàn thành QC' || $action === 'Ghi nhận lỗi' || stripos($action, 'lỗi') === 0) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $action,
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
                $action = $log['action'] ?? '';
                if ($action === 'Hoàn thành QC' || $action === 'Ghi nhận lỗi' || stripos($action, 'lỗi') === 0) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $action,
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
     * Process completing or rolling back (logging errors) QC for a product code.
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

        $isError = $actionType === 'rollback' || mb_stripos($actionType, 'lỗi') === 0;

        if ($isError) {
            // Xác định tên action lỗi
            if (mb_stripos($actionType, 'lỗi') === 0) {
                // action_type gửi thẳng tên lỗi: "lỗi cắt cnc", "lỗi dán cạnh"...
                $stepMappings = [
                    'lỗi ép ván'   => 'Lỗi Ép ván',
                    'lỗi cắt cnc'  => 'Lỗi Cắt CNC',
                    'lỗi dán cạnh' => 'Lỗi Dán cạnh',
                    'lỗi làm đẹp'  => 'Lỗi Làm đẹp',
                ];
                $actionName = $stepMappings[mb_strtolower($actionType)] ?? ucfirst($actionType);
            } else {
                // action_type = 'rollback' (legacy) — dùng error_type
                $errorType = $data['error_type'] ?? null;
                $actionName = 'Ghi nhận lỗi';
                if ($errorType) {
                    $stepMappings = [
                        'ép ván'   => 'Lỗi Ép ván',
                        'cắt cnc'  => 'Lỗi Cắt CNC',
                        'dán cạnh' => 'Lỗi Dán cạnh',
                        'làm đẹp'  => 'Lỗi Làm đẹp',
                    ];
                    $actionName = $stepMappings[strtolower($errorType)] ?? 'Lỗi ' . ucwords($errorType);
                }
            }

            $newLog = [
                'action'      => $actionName,
                'operator'    => $operatorName,
                'operator_id' => Auth::id(),
                'time'        => $logTime,
                'notes'       => $notes,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $message = 'Đã ghi nhận lỗi cho sản phẩm này.';
        } else {
            // Check if already completed
            $alreadyCompleted = false;
            for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
                if (isset($currentStatus[$i]['action']) && $currentStatus[$i]['action'] === 'Hoàn thành QC') {
                    $alreadyCompleted = true;
                    break;
                }
            }

            if ($alreadyCompleted) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Sản phẩm này đã được ghi nhận hoàn thành QC trước đó.'
                ];
            }

            $newLog = [
                'action' => 'Hoàn thành QC',
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

            $message = 'Xác nhận hoàn thành công đoạn QC thành công.';
        }

        $productName = $item->$nameField ?? '—';

        return [
            'success'     => true,
            'status_code' => 200,
            'action_type' => $isError ? 'rollback' : 'complete',
            'message'     => $message,
            'data'        => [
                'product_code' => $codeRecord->product_id,
                'product_name' => $productName,
                'type'         => $type,
                'operator'     => $operatorName,
                'time'         => $logTime,
                'notes'        => $notes ?? ($isError ? 'Ghi nhận lỗi' : 'Hoàn thành QC'),
                'action'       => $isError ? ($actionName ?? 'Ghi nhận lỗi') : 'Hoàn thành QC',
            ]
        ];
    }

    /**
     * Get product info and completed stages by product code.
     */
    public function getProductInfo(string $productCode): array
    {
        $codeStr = trim($productCode);
        
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

        $productName = $item->$nameField ?? '—';
        $orderCode = $item->orderSupply->order->order_code ?? '—';
        
        // Dimensions
        $height = 0;
        $width = 0;
        $thickness = 17; // default standard board thickness

        if ($type === 'min_late') {
            $size = $item->size ?? [];
            if (is_string($size)) {
                $size = json_decode($size, true) ?? [];
            }
            $height = $size['height'] ?? 0;
            $width = $size['width'] ?? 0;
            if (isset($size['thickness'])) {
                $thickness = $size['thickness'];
            }
        } else {
            $height = $item->height ?? 0;
            $width = $item->width ?? 0;
        }

        if ($item->orderSupply && preg_match('/(\d+)\s*mm/i', $item->orderSupply->supply_name, $matches)) {
            $thickness = $matches[1];
        }

        $dimensions = "{$height} × {$width}" . ($thickness ? " × {$thickness}" : "") . " mm";

        // Completed steps from status logs
        $completedSteps = [];
        $status = $codeRecord->status ?? [];
        foreach ($status as $log) {
            $action = strtolower($log['action'] ?? '');
            if ($action === 'đã nhận tem') {
                $completedSteps[] = 'Dán tem';
            } elseif ($action === 'hoàn thành cnc') {
                $completedSteps[] = 'Cắt CNC';
            } elseif (in_array($action, ['ép đơn', 'ép dự trữ', 'làm lệnh ép', 'hoàn thành ép'])) {
                $completedSteps[] = 'Ép ván';
            } elseif ($action === 'hoàn thành dán cạnh') {
                $completedSteps[] = 'Dán cạnh';
            } elseif ($action === 'hoàn thành làm đẹp') {
                $completedSteps[] = 'Làm đẹp';
            }
        }
        $completedSteps = array_values(array_unique($completedSteps));

        return [
            'success' => true,
            'status_code' => 200,
            'data' => [
                'product_code' => $codeRecord->product_id,
                'product_name' => $productName,
                'order_code' => $orderCode,
                'dimensions' => $dimensions,
                'completed_steps' => $completedSteps,
            ]
        ];
    }
}

