<?php

namespace App\Services;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\WoodBoard;
use App\Models\WoodBoardPrice;
use App\Models\InventoryTransaction;
use App\Models\PressingOrderItem;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;

class PressingService
{
    /**
     * Get Pressing history logs sorted by time descending.
     */
    public function getHistory()
    {
        $acrylicCodes = AcrylicOrderItemCode::whereNotNull('status')
            ->with('acrylicOrderItem.orderSupply')
            ->get();
        $glassCodes = GlassOrderItemCode::whereNotNull('status')
            ->with('glassOrderItem.orderSupply')
            ->get();
        $minLateCodes = MinLateOrderItemCode::whereNotNull('status')
            ->with('minLateOrderItem.orderSupply')
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

        $pressingItems = PressingOrderItem::with('pressingOrder')->whereNotNull('status_logs')->get();
        foreach ($pressingItems as $pi) {
            $statusLogs = $pi->status_logs ?? [];
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                    $history->push((object)[
                        'product_code' => $pi->piece_code,
                        'product_name' => $pi->pressingOrder->material_name ?? 'Lệnh ép ván',
                        'type' => 'pressing_order',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $pi->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? '',
                        'item_id' => $pi->id
                    ]);
                }
            }
        }

        // Include InventoryTransactions created from pressing (both stock pressing and direct order pressing)
        $pressingTxs = InventoryTransaction::whereIn('voucher_code', ['EP-KHO', 'EP-DON'])
            ->with(['woodBoard', 'boardPrice', 'creator'])
            ->latest('id')
            ->take(150)
            ->get();

        foreach ($pressingTxs as $tx) {
            $scannedCode = null;
            if ($tx->notes && preg_match('/\[Mã: (.+?)\]/', $tx->notes, $mCode)) {
                $scannedCode = $mCode[1];
            } elseif ($tx->notes && preg_match('/^Mã quét: (.+)$/', $tx->notes, $mCode)) {
                $scannedCode = $mCode[1];
            }

            $code = $scannedCode ?? $tx->boardPrice?->code ?? $tx->woodBoard?->color_code ?? ($tx->reference_id ?? 'KHO');
            $name = $tx->boardPrice?->name ?? ($tx->woodBoard ? 'Tấm ' . $tx->woodBoard->color_code : 'Ván kho');
            $action = $tx->voucher_code === 'EP-KHO' ? 'ép dự trữ' : 'ép đơn';

            // Avoid showing duplicate if this exact code is already in order history for 'ép đơn'
            if ($action === 'ép đơn') {
                $alreadyExists = $history->contains(function ($h) use ($code) {
                    return $h->product_code === $code && $h->action === 'ép đơn';
                });
                if ($alreadyExists) {
                    continue;
                }
            }

            $history->push((object)[
                'product_code' => $code,
                'product_name' => $name,
                'type'         => 'stock',
                'action'       => $action,
                'operator'     => $tx->creator?->name ?? 'Hệ thống',
                'time'         => $tx->created_at ? $tx->created_at->toDateTimeString() : now()->toDateTimeString(),
                'notes'        => $tx->notes ?? 'Hoàn thành ép ván',
                'item_id'      => $tx->id,
            ]);
        }

        return $history->sortByDesc('time')->values();
    }

    /**
     * Helper to adjust wood board stock and write an InventoryTransaction.
     */
    public function adjustWoodBoardStock(WoodBoardPrice|WoodBoard $boardOrPrice, float $qtyChange, string $voucherCode, ?string $refId = null, ?string $notes = null): array
    {
        $newStock = 0;
        $boardName = '';
        $boardId = null;
        $priceId = null;
        $targetWarehouseId = null;

        if ($boardOrPrice instanceof WoodBoardPrice) {
            $current = (float)$boardOrPrice->stock;
            $newStock = max(0, $current + $qtyChange);
            $boardOrPrice->stock = $newStock;
            $boardOrPrice->save();

            $parentBoard = $boardOrPrice->board;
            if ($parentBoard) {
                $parentBoard->current_stock = $parentBoard->prices()->sum('stock');
                $parentBoard->save();
                $targetWarehouseId = $parentBoard->warehouse_id;
            }

            $boardName = "{$boardOrPrice->code}" . ($boardOrPrice->name ? " - {$boardOrPrice->name}" : "");
            $boardId = $boardOrPrice->wood_board_id;
            $priceId = $boardOrPrice->id;
        } else {
            $current = (float)$boardOrPrice->current_stock;
            $newStock = max(0, $current + $qtyChange);
            $boardOrPrice->current_stock = $newStock;
            $boardOrPrice->save();

            $boardName = "Tấm {$boardOrPrice->color_code}";
            $boardId = $boardOrPrice->id;
            $targetWarehouseId = $boardOrPrice->warehouse_id;
        }

        // Đảm bảo warehouse_id luôn tồn tại trong bảng warehouses (tránh lỗi khóa ngoại 1452)
        $warehouse = null;
        if ($targetWarehouseId) {
            $warehouse = Warehouse::find($targetWarehouseId);
        }
        if (!$warehouse) {
            $warehouse = Warehouse::where('code', 'KHO-VAN')->first() ?? Warehouse::first();
        }
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Kho Tấm Ván',
                'code' => 'KHO-VAN',
                'status' => 'active',
                'item_name' => 'Tấm Ván',
                'description' => 'Kho ván gỗ công nghiệp phục vụ ép'
            ]);
        }
        $warehouseId = $warehouse->id;

        // Đồng bộ warehouse_id hợp lệ vào board nếu trước đó chưa có hoặc sai
        if ($boardOrPrice instanceof WoodBoardPrice && isset($parentBoard) && $parentBoard && $parentBoard->warehouse_id !== $warehouseId) {
            $parentBoard->warehouse_id = $warehouseId;
            $parentBoard->save();
        } elseif (!($boardOrPrice instanceof WoodBoardPrice) && $boardOrPrice->warehouse_id !== $warehouseId) {
            $boardOrPrice->warehouse_id = $warehouseId;
            $boardOrPrice->save();
        }

        $refIdInt = (is_numeric($refId) && (int)$refId > 0) ? (int)$refId : null;
        $finalNotes = $notes;
        if (!empty($refId) && !is_numeric($refId)) {
            $finalNotes = $finalNotes ? "{$finalNotes} [Mã: {$refId}]" : "Mã quét: {$refId}";
        }

        InventoryTransaction::create([
            'warehouse_id'        => $warehouseId,
            'item_type'           => 'board',
            'wood_board_id'       => $boardId,
            'wood_board_price_id' => $priceId,
            'date'                => now()->toDateString(),
            'type'                => $qtyChange > 0 ? 'receipt' : 'issue',
            'voucher_code'        => $voucherCode,
            'reference_id'        => $refIdInt,
            'reference_type'      => 'pressing',
            'in_qty'              => $qtyChange > 0 ? abs($qtyChange) : 0,
            'out_qty'             => $qtyChange < 0 ? abs($qtyChange) : 0,
            'stock_after'         => $newStock,
            'notes'               => $finalNotes,
            'created_by'          => Auth::id(),
        ]);

        return [
            'new_stock'  => $newStock,
            'board_name' => $boardName,
            'board_id'   => $boardId,
            'price_id'   => $priceId,
        ];
    }

    /**
     * Helper to resolve WoodBoardPrice or WoodBoard from item or string.
     */
    public function resolveWoodBoard($itemOrCode, ?int $priceId = null)
    {
        if ($priceId) {
            $price = WoodBoardPrice::with('board')->find($priceId);
            if ($price) return $price;
        }

        if (is_string($itemOrCode)) {
            $s = trim($itemOrCode);
            if (!$s) return null;

            // Bỏ các ký tự rác / dấu ? nếu có do máy quét
            $s = trim($s, "?* \t\n\r\0\x0B");

            // Bỏ hậu tố _ nesting nếu có (VD: 5438.GV26.TP.PVC.016_16 -> 5438.GV26.TP.PVC.016)
            if (str_contains($s, '_')) {
                $s = explode('_', $s)[0];
            }

            // 1. Khớp trực tiếp mã WoodBoardPrice hoặc WoodBoard
            $price = WoodBoardPrice::with('board')->where('code', $s)->first();
            if ($price) return $price;

            $board = WoodBoard::with('prices')->where('color_code', $s)->first();
            if ($board) {
                return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
            }

            $price = WoodBoardPrice::with('board')->where('name', 'like', "%{$s}%")->first();
            if ($price) return $price;

            // 2. Kiểm tra nếu $s là mã tem tấm thành phẩm (AcrylicOrderItemCode, MinLateOrderItemCode, GlassOrderItemCode)
            $codeRecord = AcrylicOrderItemCode::where('product_id', $s)->first();
            if ($codeRecord && $codeRecord->acrylicOrderItem) {
                $resolved = $this->resolveWoodBoard($codeRecord->acrylicOrderItem);
                if ($resolved) return $resolved;
            }

            $codeRecord = MinLateOrderItemCode::where('product_id', $s)->first();
            if ($codeRecord && $codeRecord->minLateOrderItem) {
                $resolved = $this->resolveWoodBoard($codeRecord->minLateOrderItem);
                if ($resolved) return $resolved;
            }

            $codeRecord = GlassOrderItemCode::where('product_id', $s)->first();
            if ($codeRecord && $codeRecord->glassOrderItem) {
                $resolved = $this->resolveWoodBoard($codeRecord->glassOrderItem);
                if ($resolved) return $resolved;
            }

            // 3. Kiểm tra nếu $s là mã tem tấm lệnh ép (PressingOrderItem: LLE-...)
            $pressingItem = PressingOrderItem::with('pressingOrder')->where('piece_code', $s)->first();
            if ($pressingItem && $pressingItem->pressingOrder) {
                $po = $pressingItem->pressingOrder;
                if ($po->wood_board_id) {
                    $b = WoodBoard::with('prices')->find($po->wood_board_id);
                    if ($b) return $b->prices->isNotEmpty() ? $b->prices->first() : $b;
                }
                if ($po->material_name) {
                    $resolved = $this->resolveWoodBoard($po->material_name);
                    if ($resolved) return $resolved;
                }
            }

            // 4. Phân tích chuỗi ghép mã (VD: '211026.6.GV28.1', '5438.GV26.TP.PVC.016', 'DH...GV26...')
            // Thử kiểm tra phần đuôi sau mã đơn hàng dạng Ngày.SốĐơn (VD: 211026.6.GV28.1 -> GV28.1)
            if (preg_match('/^\d{6}\.\d+\.(.+)$/', $s, $matchOrderSuffix)) {
                $suffix = trim($matchOrderSuffix[1]);
                $price = WoodBoardPrice::with('board')->where('code', $suffix)->first();
                if ($price) return $price;

                $board = WoodBoard::with('prices')->where('color_code', $suffix)->first();
                if ($board) {
                    return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
                }
            }

            // Tách các phân đoạn (tokens) theo dấu chấm, gạch ngang, gạch dưới
            $tokens = preg_split('/[.\-_\s]+/', $s);
            $countTokens = count($tokens);

            // Thử các cặp token liền kề (VD: GV28 và 1 -> GV28.1 hoặc GV28-1)
            for ($i = 0; $i < $countTokens - 1; $i++) {
                $comboDot = $tokens[$i] . '.' . $tokens[$i + 1];
                $price = WoodBoardPrice::with('board')->where('code', $comboDot)->first();
                if ($price) return $price;

                $comboDash = $tokens[$i] . '-' . $tokens[$i + 1];
                $price = WoodBoardPrice::with('board')->where('code', $comboDash)->first();
                if ($price) return $price;
            }

            // Thử từng token đơn lẻ (VD: GV28, GV26, GV103, PARC01...)
            foreach ($tokens as $token) {
                $token = trim($token);
                if (empty($token) || is_numeric($token) || strlen($token) < 2) continue;

                // Khớp mã màu WoodBoard
                $board = WoodBoard::with('prices')->where('color_code', $token)->first();
                if ($board) {
                    return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
                }

                // Khớp mã WoodBoardPrice
                $price = WoodBoardPrice::with('board')->where('code', $token)->first();
                if ($price) return $price;
            }

            // 5. Thử trích xuất mã màu bằng regex thông dụng (VD: GV28, GV103, PARC1, v.v.)
            if (preg_match('/([A-Za-z]{2,5}\d{1,4}[A-Za-z0-9.]*)/', $s, $mColor)) {
                $candidate = $mColor[1];
                $price = WoodBoardPrice::with('board')->where('code', $candidate)->first();
                if ($price) return $price;

                // Nếu có dấu chấm ở đuôi (VD: GV28.1) thì thử cả phần trước dấu chấm (GV28)
                if (str_contains($candidate, '.')) {
                    $baseColor = explode('.', $candidate)[0];
                    $board = WoodBoard::with('prices')->where('color_code', $baseColor)->first();
                    if ($board) {
                        return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
                    }
                }

                $board = WoodBoard::with('prices')->where('color_code', $candidate)->first();
                if ($board) {
                    return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
                }
            }

            // 6. Kiểm tra nếu có mã đơn hàng đầu chuỗi (VD: '211026.6')
            if ($countTokens >= 2) {
                $orderCode = $tokens[0] . '.' . $tokens[1];
                $order = \App\Models\Order::with('supplies')->where('order_code', $orderCode)->first();
                if ($order && $order->supplies && $order->supplies->isNotEmpty()) {
                    foreach ($order->supplies as $sup) {
                        $resolved = $this->resolveWoodBoard($sup->order_supply_code ?: $sup->supply_name);
                        if ($resolved) return $resolved;
                    }
                }
            }

            return null;
        }

        // Resolving from an order item object
        if (is_object($itemOrCode)) {
            $supply = $itemOrCode->orderSupply ?? null;

            if ($supply) {
                // By supply code
                if (!empty($supply->order_supply_code)) {
                    $code = trim($supply->order_supply_code);
                    $price = WoodBoardPrice::with('board')->where('code', $code)->first();
                    if ($price) return $price;

                    $board = WoodBoard::with('prices')->where('color_code', $code)->first();
                    if ($board) {
                        return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
                    }
                }

                // By supply name
                if (!empty($supply->supply_name)) {
                    $name = trim($supply->supply_name);
                    $price = WoodBoardPrice::with('board')->where('name', $name)->first();
                    if ($price) return $price;

                    $price = WoodBoardPrice::with('board')->where('code', $name)->first();
                    if ($price) return $price;

                    $board = WoodBoard::with('prices')->where('color_code', $name)->first();
                    if ($board) {
                        return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
                    }

                    $price = WoodBoardPrice::with('board')->where('name', 'like', "%{$name}%")->first();
                    if ($price) return $price;
                }
            }

            // By product name on item
            $pName = $itemOrCode->product_name ?? $itemOrCode->name ?? null;
            if ($pName) {
                $pName = trim($pName);
                $price = WoodBoardPrice::with('board')->where('name', 'like', "%{$pName}%")->first();
                if ($price) return $price;

                $board = WoodBoard::with('prices')->where('color_code', $pName)->first();
                if ($board) {
                    return $board->prices->isNotEmpty() ? $board->prices->first() : $board;
                }
            }
        }

        return null;
    }

    /**
     * Process completing or rolling back Pressing (bulk or single).
     */
    public function completeOrRollback(array $data): array
    {
        $codeStr = trim($data['product_code'] ?? '');
        if (str_contains($codeStr, '_')) {
            $codeStr = explode('_', $codeStr)[0];
        }
        $priceId = !empty($data['wood_board_price_id']) ? intval($data['wood_board_price_id']) : null;
        $notes = $data['notes'] ?? null;
        $actionType = $data['action_type'];
        $logTime = now()->toDateTimeString();
        $operatorName = Auth::user()->name ?? 'Hệ thống';

        $pressingActions = ['làm lệnh ép', 'xuất kho ván', 'ép đơn', 'ép dự trữ', 'complete'];
        $isRollback = ($actionType === 'rollback');
        $effectiveAction = $isRollback ? 'rollback' : 'ép đơn';

        // ═══════════════════════════════════════════════════════════════════
        // CASE 1: TẤM ĐƠN LẺ THEO MÃ QR (ACRYLIC, GLASS, MIN LATE)
        // ═══════════════════════════════════════════════════════════════════
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

        // ═══════════════════════════════════════════════════════════════════
        // CASE 2: TEM TẤM LỆNH ÉP VÁN (PRESSING ORDER ITEM)
        // ═══════════════════════════════════════════════════════════════════
        $pressingItem = null;
        if (!$codeRecord) {
            $pressingItem = PressingOrderItem::with('pressingOrder')->where('piece_code', $codeStr)->first();
            if ($pressingItem) {
                $currentLogs = $pressingItem->status_logs ?? [];
                if ($isRollback) {
                    $newLogs = array_filter($currentLogs, fn($l) => !in_array($l['action'] ?? '', ['ép đơn', 'ép dự trữ', 'complete']));
                    $newLogs[] = [
                        'action'      => 'quay lại ép',
                        'operator'    => $operatorName,
                        'operator_id' => Auth::id(),
                        'time'        => $logTime,
                        'notes'       => $notes,
                    ];
                    $pressingItem->status = 'pending';
                    $pressingItem->status_logs = array_values($newLogs);
                    $pressingItem->save();
                    $pressingItem->pressingOrder?->update(['status' => 'processing']);

                    return [
                        'success'     => true,
                        'status_code' => 200,
                        'is_bulk'     => false,
                        'message'     => "Đã hoàn tác quy trình ép ván cho tấm lệnh {$codeStr}.",
                        'data'        => [
                            'product_code' => $codeStr,
                            'product_name' => $pressingItem->pressingOrder?->material_name ?? 'Lệnh ép ván',
                            'type'         => 'pressing_order',
                            'operator'     => $operatorName,
                            'time'         => $logTime,
                            'notes'        => $notes ?: 'Quay lại ép',
                            'action'       => 'quay lại ép',
                        ]
                    ];
                } else {
                    $currentLogs[] = [
                        'action'      => 'ép đơn',
                        'operator'    => $operatorName,
                        'operator_id' => Auth::id(),
                        'time'        => $logTime,
                        'notes'       => $notes,
                    ];
                    $pressingItem->status = 'completed';
                    $pressingItem->status_logs = $currentLogs;
                    $pressingItem->save();

                    // Check if parent order completed
                    if ($pressingItem->pressingOrder) {
                        $po = $pressingItem->pressingOrder;
                        $totalDone = $po->items()->where('status', 'completed')->count();
                        $po->qty_done = $totalDone;
                        if ($totalDone >= $po->qty_needed) {
                            $po->status = 'completed';
                        }
                        $po->save();
                    }

                    return [
                        'success'     => true,
                        'status_code' => 200,
                        'is_bulk'     => false,
                        'message'     => "Đã ghi nhận hoàn thành ép ván cho tấm lệnh {$codeStr}.",
                        'data'        => [
                            'product_code' => $codeStr,
                            'product_name' => $pressingItem->pressingOrder?->material_name ?? 'Lệnh ép ván',
                            'type'         => 'pressing_order',
                            'operator'     => $operatorName,
                            'time'         => $logTime,
                            'notes'        => $notes ?: 'Hoàn thành ép ván',
                            'action'       => 'ép đơn',
                        ]
                    ];
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // CASE 3: QUÉT TRỰC TIẾP MÃ VÁN HOẶC MÃ MÀU
        // ═══════════════════════════════════════════════════════════════════
        if (!$codeRecord && !$pressingItem) {
            $directBoard = $this->resolveWoodBoard($codeStr, $priceId);
            if ($directBoard) {
                $boardName = $directBoard instanceof WoodBoardPrice ? ($directBoard->name ?? $directBoard->code) : ($directBoard->color_code ?? 'Ván kho');
                $actionLabel = $isRollback ? 'quay lại ép' : 'ép đơn';
                $msg = $isRollback ? "Đã ghi nhận hoàn tác ép ván cho {$boardName}." : "Đã ghi nhận hoàn thành công đoạn ép ván cho {$boardName}.";

                return [
                    'success'     => true,
                    'status_code' => 200,
                    'is_bulk'     => false,
                    'message'     => $msg,
                    'data'        => [
                        'product_code' => $directBoard->code ?? $directBoard->color_code ?? $codeStr,
                        'product_name' => $boardName,
                        'type'         => 'stock',
                        'operator'     => $operatorName,
                        'time'         => $logTime,
                        'notes'        => $notes ?: $msg,
                        'action'       => $actionLabel,
                    ]
                ];
            }
        }

        if (!$codeRecord || !$item) {
            return [
                'success' => false,
                'status_code' => 404,
                'message' => 'Không tìm thấy sản phẩm, tấm ván hoặc lệnh sản xuất với mã: ' . $codeStr
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

        // ─────────────────────────────────────────
        // ROLLBACK
        // ─────────────────────────────────────────
        if ($isRollback) {
            if (!$latestPressingLog || $latestPressingIndex === -1) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Sản phẩm này chưa ghi nhận quy trình ép ván nên không thể quay lại.'
                ];
            }

            $currentStatus = array_slice($currentStatus, 0, $latestPressingIndex);
            $newLog = [
                'action'      => 'quay lại ép',
                'operator'    => $operatorName,
                'operator_id' => Auth::id(),
                'time'        => $logTime,
                'notes'       => $notes,
            ];
            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $msg = "Đã hoàn tác quy trình ép ván cho tấm {$codeStr}.";
        } else {
            // ─────────────────────────────────────────
            // COMPLETE ÉP ĐƠN
            // ─────────────────────────────────────────
            if ($latestPressingLog && in_array($latestPressingLog['action'], ['ép đơn', 'complete'])) {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => "Tấm ván này đã được ghi nhận hoàn thành ép ván trước đó."
                ];
            }

            $newLog = [
                'action'      => 'ép đơn',
                'operator'    => $operatorName,
                'operator_id' => Auth::id(),
                'time'        => $logTime,
                'notes'       => $notes,
            ];
            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            if ($notes) {
                $item->notes = $notes;
                $item->save();
            }
            \App\Models\Order::markInProductionFromItem($item);

            $msg = "Đã ghi nhận hoàn thành ép ván cho tấm {$codeStr} thành công!";
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
                'action' => $isRollback ? 'quay lại ép' : 'ép đơn',
            ]
        ];
    }
}
