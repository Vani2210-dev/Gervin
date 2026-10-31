<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WoodBoard;
use App\Models\WoodBoardType;
use App\Models\WoodBoardPrice;
use App\Models\InventoryReceipt;
use App\Models\InventoryReceiptItem;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueItem;
use App\Models\InventoryStocktake;
use App\Models\InventoryStocktakeItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderSupply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InventoryService
{
    /**
     * Generate next voucher code with prefix and date
     */
    public function generateCode(string $prefix, string $table): string
    {
        $datePrefix = date('ymd');
        $pattern = $prefix . $datePrefix;
        
        $latest = DB::table($table)
            ->where('code', 'like', $pattern . '%')
            ->orderBy('id', 'desc')
            ->value('code');

        if ($latest) {
            $seq = (int)substr($latest, strlen($pattern)) + 1;
            return $pattern . str_pad($seq, 3, '0', STR_PAD_LEFT);
        }

        return $pattern . '001';
    }

    /**
     * Create Goods Receipt (PNK)
     */
    public function createReceipt(Warehouse $warehouse, array $data, array $items, $userId = null): InventoryReceipt
    {
        $this->ensureSchemaUpdated();

        return DB::transaction(function () use ($warehouse, $data, $items, $userId) {
            $code = !empty($data['code']) ? $data['code'] : $this->generateCode('PNK', 'inventory_receipts');
            $date = !empty($data['date']) ? $data['date'] : date('Y-m-d');

            $receipt = InventoryReceipt::create([
                'code' => $code,
                'date' => $date,
                'warehouse_id' => $warehouse->id,
                'supplier_name' => $data['supplier_name'] ?? null,
                'deliverer' => $data['deliverer'] ?? null,
                'receiver' => $data['receiver'] ?? null,
                'total_quantity' => 0,
                'total_amount' => 0,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
                'created_by' => $userId,
            ]);

            $totalQty = 0;
            $totalAmount = 0;
            $isEdgeBandWarehouse = ($warehouse->code === 'KHO-CHI') || (str_contains(mb_strtolower($warehouse->name), 'chỉ'));

            foreach ($items as $item) {
                $rawMatId = (string)($item['wood_board_id'] ?? $item['material_id'] ?? '');
                $itemType = $item['item_type'] ?? null;
                if (!$itemType) {
                    if (str_starts_with($rawMatId, 'edge_') || str_starts_with($rawMatId, 'CHI-') || $isEdgeBandWarehouse) {
                        $itemType = 'edge_band';
                    } else {
                        $itemType = 'board';
                    }
                }

                $cleanId = str_replace(['edge_', 'board_', 'CHI-'], '', $rawMatId);

                if ($itemType === 'edge_band') {
                    $board = is_numeric($cleanId) 
                        ? WoodBoard::find($cleanId) 
                        : WoodBoard::where('color_code', $cleanId)->first();

                    if (!$board) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $price = (float)($item['unit_price'] ?? ($board->edge_band_cost_price ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryReceiptItem::create($this->sanitizeItemData('inventory_receipt_items', [
                        'receipt_id'          => $receipt->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    $board->edge_band_current_stock = (float)$board->edge_band_current_stock + $qty;
                    if ($price > 0) {
                        $board->edge_band_cost_price = $price;
                    }
                    $board->save();
                    $stockAfter = $board->edge_band_current_stock;

                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'date'                => $date,
                        'type'                => 'receipt',
                        'voucher_code'        => $code,
                        'reference_id'        => $receipt->id,
                        'reference_type'      => InventoryReceipt::class,
                        'in_qty'              => $qty,
                        'out_qty'             => 0,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Nhập kho chỉ theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                } else {
                    $priceId = $item['wood_board_price_id'] ?? null;
                    $boardId = is_numeric($cleanId) ? (int)$cleanId : null;

                    $boardPrice = null;
                    if ($priceId) {
                        $boardPrice = \App\Models\WoodBoardPrice::find($priceId);
                    } elseif ($boardId) {
                        $maybePrice = \App\Models\WoodBoardPrice::find($boardId);
                        if ($maybePrice) {
                            $boardPrice = $maybePrice;
                            $boardId = $maybePrice->wood_board_id;
                        }
                    }

                    if ($boardPrice) {
                        $boardId = $boardPrice->wood_board_id;
                    }

                    if (!$boardId && !$boardPrice) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $price = (float)($item['unit_price'] ?? ($boardPrice?->price_board ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryReceiptItem::create($this->sanitizeItemData('inventory_receipt_items', [
                        'receipt_id'          => $receipt->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    // Update WoodBoardPrice stock
                    $stockAfter = 0;
                    if ($boardPrice) {
                        $boardPrice->stock += $qty;
                        if ($price > 0) {
                            $boardPrice->price_board = $price;
                        }
                        $boardPrice->save();
                        $stockAfter = $boardPrice->stock;
                    }

                    // Update WoodBoard stock
                    $board = WoodBoard::find($boardId);
                    if ($board) {
                        $board->current_stock = $board->prices()->sum('stock') ?: ($board->current_stock + $qty);
                        if ($price > 0 && !$boardPrice) {
                            $board->cost_price = $price;
                        }
                        $board->save();
                        if (!$boardPrice) {
                            $stockAfter = $board->current_stock;
                        }
                    }

                    // Create stock transaction
                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'date'                => $date,
                        'type'                => 'receipt',
                        'voucher_code'        => $code,
                        'reference_id'        => $receipt->id,
                        'reference_type'      => InventoryReceipt::class,
                        'in_qty'              => $qty,
                        'out_qty'             => 0,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Nhập kho theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                }
            }

            $receipt->update([
                'total_quantity' => $totalQty,
                'total_amount' => $totalAmount,
            ]);

            return $receipt;
        });
    }

    /**
     * Update Goods Receipt (PNK)
     */
    public function updateReceipt(InventoryReceipt $receipt, Warehouse $warehouse, array $data, array $items, $userId = null): InventoryReceipt
    {
        $this->ensureSchemaUpdated();

        return DB::transaction(function () use ($receipt, $warehouse, $data, $items, $userId) {
            $receipt->loadMissing('items');

            // 1. Hoàn trả lại tồn kho cũ của các items trong phiếu
            if ($receipt->status === 'completed') {
                foreach ($receipt->items as $oldItem) {
                    $oldQty = (float)($oldItem->quantity ?? 0);
                    if ($oldQty <= 0) continue;

                    if ($oldItem->item_type === 'edge_band') {
                        if ($oldItem->wood_board_id) {
                            $oldBoard = WoodBoard::find($oldItem->wood_board_id);
                            if ($oldBoard) {
                                $oldBoard->edge_band_current_stock = max(0, (float)$oldBoard->edge_band_current_stock - $oldQty);
                                $oldBoard->save();
                            }
                        }
                    } else {
                        if ($oldItem->wood_board_price_id) {
                            $oldBoardPrice = \App\Models\WoodBoardPrice::find($oldItem->wood_board_price_id);
                            if ($oldBoardPrice) {
                                $oldBoardPrice->stock = max(0, (float)$oldBoardPrice->stock - $oldQty);
                                $oldBoardPrice->save();
                            }
                        }

                        if ($oldItem->wood_board_id) {
                            $oldBoard = WoodBoard::find($oldItem->wood_board_id);
                            if ($oldBoard) {
                                $oldBoard->current_stock = $oldBoard->prices()->sum('stock') ?: max(0, (float)$oldBoard->current_stock - $oldQty);
                                $oldBoard->save();
                            }
                        }
                    }
                }
            }

            // 2. Xóa các transactions cũ của phiếu này
            InventoryTransaction::where(function ($q) use ($receipt) {
                $q->where('reference_type', InventoryReceipt::class)
                  ->where('reference_id', $receipt->id);
            })->orWhere('voucher_code', $receipt->code)
              ->delete();

            // 3. Xóa các items cũ của phiếu
            $receipt->items()->delete();

            // 4. Cập nhật thông tin phiếu nhập
            $code = !empty($data['code']) ? $data['code'] : $receipt->code;
            $date = !empty($data['date']) ? $data['date'] : $receipt->date;

            $receipt->update([
                'code'          => $code,
                'date'          => $date,
                'warehouse_id'  => $warehouse->id,
                'supplier_name' => $data['supplier_name'] ?? null,
                'deliverer'     => $data['deliverer'] ?? null,
                'receiver'      => $data['receiver'] ?? null,
                'notes'         => $data['notes'] ?? null,
            ]);

            // 5. Thêm lại các items mới và cập nhật tồn kho & transactions mới
            $totalQty = 0;
            $totalAmount = 0;
            $isEdgeBandWarehouse = ($warehouse->code === 'KHO-CHI') || (str_contains(mb_strtolower($warehouse->name), 'chỉ'));

            foreach ($items as $item) {
                $rawMatId = (string)($item['wood_board_id'] ?? $item['material_id'] ?? '');
                $itemType = $item['item_type'] ?? null;
                if (!$itemType) {
                    if (str_starts_with($rawMatId, 'edge_') || str_starts_with($rawMatId, 'CHI-') || $isEdgeBandWarehouse) {
                        $itemType = 'edge_band';
                    } else {
                        $itemType = 'board';
                    }
                }

                $cleanId = str_replace(['edge_', 'board_', 'CHI-'], '', $rawMatId);

                if ($itemType === 'edge_band') {
                    $board = is_numeric($cleanId) 
                        ? WoodBoard::find($cleanId) 
                        : WoodBoard::where('color_code', $cleanId)->first();

                    if (!$board) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $price = (float)($item['unit_price'] ?? ($board->edge_band_cost_price ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryReceiptItem::create($this->sanitizeItemData('inventory_receipt_items', [
                        'receipt_id'          => $receipt->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    $board->edge_band_current_stock = (float)$board->edge_band_current_stock + $qty;
                    if ($price > 0) {
                        $board->edge_band_cost_price = $price;
                    }
                    $board->save();
                    $stockAfter = $board->edge_band_current_stock;

                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'date'                => $date,
                        'type'                => 'receipt',
                        'voucher_code'        => $code,
                        'reference_id'        => $receipt->id,
                        'reference_type'      => InventoryReceipt::class,
                        'in_qty'              => $qty,
                        'out_qty'             => 0,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Nhập kho chỉ theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                } else {
                    $priceId = $item['wood_board_price_id'] ?? null;
                    $boardId = is_numeric($cleanId) ? (int)$cleanId : null;

                    $boardPrice = null;
                    if ($priceId) {
                        $boardPrice = \App\Models\WoodBoardPrice::find($priceId);
                    } elseif ($boardId) {
                        $maybePrice = \App\Models\WoodBoardPrice::find($boardId);
                        if ($maybePrice) {
                            $boardPrice = $maybePrice;
                            $boardId = $maybePrice->wood_board_id;
                        }
                    }

                    if ($boardPrice) {
                        $boardId = $boardPrice->wood_board_id;
                    }

                    if (!$boardId && !$boardPrice) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $price = (float)($item['unit_price'] ?? ($boardPrice?->price_board ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryReceiptItem::create($this->sanitizeItemData('inventory_receipt_items', [
                        'receipt_id'          => $receipt->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    // Update WoodBoardPrice stock
                    $stockAfter = 0;
                    if ($boardPrice) {
                        $boardPrice->stock += $qty;
                        if ($price > 0) {
                            $boardPrice->price_board = $price;
                        }
                        $boardPrice->save();
                        $stockAfter = $boardPrice->stock;
                    }

                    // Update WoodBoard stock
                    $board = WoodBoard::find($boardId);
                    if ($board) {
                        $board->current_stock = $board->prices()->sum('stock') ?: ($board->current_stock + $qty);
                        if ($price > 0 && !$boardPrice) {
                            $board->cost_price = $price;
                        }
                        $board->save();
                        if (!$boardPrice) {
                            $stockAfter = $board->current_stock;
                        }
                    }

                    // Create stock transaction
                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'date'                => $date,
                        'type'                => 'receipt',
                        'voucher_code'        => $code,
                        'reference_id'        => $receipt->id,
                        'reference_type'      => InventoryReceipt::class,
                        'in_qty'              => $qty,
                        'out_qty'             => 0,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Nhập kho theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                }
            }

            $receipt->update([
                'total_quantity' => $totalQty,
                'total_amount'   => $totalAmount,
            ]);

            return $receipt;
        });
    }

    /**
     * Create Goods Issue (PXK)
     */
    public function createIssue(Warehouse $warehouse, array $data, array $items, $userId = null): InventoryIssue
    {
        $this->ensureSchemaUpdated();

        return DB::transaction(function () use ($warehouse, $data, $items, $userId) {
            $code = !empty($data['code']) ? $data['code'] : $this->generateCode('PXK', 'inventory_issues');
            $date = !empty($data['date']) ? $data['date'] : date('Y-m-d');

            $issue = InventoryIssue::create([
                'code' => $code,
                'date' => $date,
                'warehouse_id' => $warehouse->id,
                'recipient' => $data['recipient'] ?? 'Xưởng sản xuất Gervin',
                'deliverer' => $data['deliverer'] ?? null,
                'reason' => $data['reason'] ?? 'Xuất sản xuất',
                'total_quantity' => 0,
                'total_amount' => 0,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
                'created_by' => $userId,
            ]);

            $totalQty = 0;
            $totalAmount = 0;
            $isEdgeBandWarehouse = ($warehouse->code === 'KHO-CHI') || (str_contains(mb_strtolower($warehouse->name), 'chỉ'));

            foreach ($items as $item) {
                $rawMatId = (string)($item['wood_board_id'] ?? $item['material_id'] ?? '');
                $itemType = $item['item_type'] ?? null;
                if (!$itemType) {
                    if (str_starts_with($rawMatId, 'edge_') || str_starts_with($rawMatId, 'CHI-') || $isEdgeBandWarehouse) {
                        $itemType = 'edge_band';
                    } else {
                        $itemType = 'board';
                    }
                }

                $cleanId = str_replace(['edge_', 'board_', 'CHI-'], '', $rawMatId);

                if ($itemType === 'edge_band') {
                    $board = is_numeric($cleanId) 
                        ? WoodBoard::find($cleanId) 
                        : WoodBoard::where('color_code', $cleanId)->first();

                    if (!$board) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $price = (float)($item['unit_price'] ?? ($board->edge_band_cost_price ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryIssueItem::create($this->sanitizeItemData('inventory_issue_items', [
                        'issue_id'            => $issue->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    $board->edge_band_current_stock = max(0, (float)$board->edge_band_current_stock - $qty);
                    $board->save();
                    $stockAfter = $board->edge_band_current_stock;

                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'date'                => $date,
                        'type'                => 'issue',
                        'voucher_code'        => $code,
                        'reference_id'        => $issue->id,
                        'reference_type'      => InventoryIssue::class,
                        'in_qty'              => 0,
                        'out_qty'             => $qty,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Xuất kho chỉ theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                } else {
                    $priceId = $item['wood_board_price_id'] ?? null;
                    $boardId = is_numeric($cleanId) ? (int)$cleanId : null;

                    $boardPrice = null;
                    if ($priceId) {
                        $boardPrice = \App\Models\WoodBoardPrice::find($priceId);
                    } elseif ($boardId) {
                        $maybePrice = \App\Models\WoodBoardPrice::find($boardId);
                        if ($maybePrice) {
                            $boardPrice = $maybePrice;
                            $boardId = $maybePrice->wood_board_id;
                        }
                    }

                    if ($boardPrice) {
                        $boardId = $boardPrice->wood_board_id;
                    }

                    if (!$boardId && !$boardPrice) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $board = WoodBoard::find($boardId);
                    $price = (float)($item['unit_price'] ?? ($boardPrice?->price_board ?? $board?->cost_price ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryIssueItem::create($this->sanitizeItemData('inventory_issue_items', [
                        'issue_id'            => $issue->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    // Update WoodBoardPrice stock
                    $stockAfter = 0;
                    if ($boardPrice) {
                        $boardPrice->stock = max(0, $boardPrice->stock - $qty);
                        $boardPrice->save();
                        $stockAfter = $boardPrice->stock;
                    }

                    // Update WoodBoard stock
                    if ($board) {
                        $board->current_stock = $board->prices()->sum('stock') ?: max(0, $board->current_stock - $qty);
                        $board->save();
                        if (!$boardPrice) {
                            $stockAfter = $board->current_stock;
                        }
                    }

                    // Create stock transaction
                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'date'                => $date,
                        'type'                => 'issue',
                        'voucher_code'        => $code,
                        'reference_id'        => $issue->id,
                        'reference_type'      => InventoryIssue::class,
                        'in_qty'              => 0,
                        'out_qty'             => $qty,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Xuất kho theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                }
            }

            $issue->update([
                'total_quantity' => $totalQty,
                'total_amount' => $totalAmount,
            ]);

            return $issue;
        });
    }

    public function updateIssue(InventoryIssue $issue, Warehouse $warehouse, array $data, array $items, $userId = null): InventoryIssue
    {
        $this->ensureSchemaUpdated();

        return DB::transaction(function () use ($issue, $warehouse, $data, $items, $userId) {
            $issue->loadMissing('items');

            // 1. Hoàn trả lại tồn kho cũ của các items trong phiếu xuất (cộng lại tồn)
            if ($issue->status === 'completed') {
                foreach ($issue->items as $oldItem) {
                    $oldQty = (float)($oldItem->quantity ?? 0);
                    if ($oldQty <= 0) continue;

                    if ($oldItem->item_type === 'edge_band') {
                        if ($oldItem->wood_board_id) {
                            $oldBoard = WoodBoard::find($oldItem->wood_board_id);
                            if ($oldBoard) {
                                $oldBoard->edge_band_current_stock = (float)$oldBoard->edge_band_current_stock + $oldQty;
                                $oldBoard->save();
                            }
                        }
                    } else {
                        if ($oldItem->wood_board_price_id) {
                            $oldBoardPrice = \App\Models\WoodBoardPrice::find($oldItem->wood_board_price_id);
                            if ($oldBoardPrice) {
                                $oldBoardPrice->stock = (float)$oldBoardPrice->stock + $oldQty;
                                $oldBoardPrice->save();
                            }
                        }

                        if ($oldItem->wood_board_id) {
                            $oldBoard = WoodBoard::find($oldItem->wood_board_id);
                            if ($oldBoard) {
                                $oldBoard->current_stock = $oldBoard->prices()->sum('stock') ?: ((float)$oldBoard->current_stock + $oldQty);
                                $oldBoard->save();
                            }
                        }
                    }
                }
            }

            // 2. Xóa các transactions cũ của phiếu xuất này
            InventoryTransaction::where(function ($q) use ($issue) {
                $q->where('reference_type', InventoryIssue::class)
                  ->where('reference_id', $issue->id);
            })->orWhere('voucher_code', $issue->code)
              ->delete();

            // 3. Xóa các items cũ của phiếu
            $issue->items()->delete();

            // 4. Cập nhật thông tin phiếu xuất
            $code = !empty($data['code']) ? $data['code'] : $issue->code;
            $date = !empty($data['date']) ? $data['date'] : $issue->date;

            $issue->update([
                'code'          => $code,
                'date'          => $date,
                'warehouse_id'  => $warehouse->id,
                'recipient'     => $data['recipient'] ?? 'Xưởng sản xuất Gervin',
                'deliverer'     => $data['deliverer'] ?? null,
                'reason'        => $data['reason'] ?? 'Xuất sản xuất',
                'notes'         => $data['notes'] ?? null,
            ]);

            // 5. Thêm lại các items mới và trừ tồn kho & transactions mới
            $totalQty = 0;
            $totalAmount = 0;
            $isEdgeBandWarehouse = ($warehouse->code === 'KHO-CHI') || (str_contains(mb_strtolower($warehouse->name), 'chỉ'));

            foreach ($items as $item) {
                $rawMatId = (string)($item['wood_board_id'] ?? $item['material_id'] ?? '');
                $itemType = $item['item_type'] ?? null;
                if (!$itemType) {
                    if (str_starts_with($rawMatId, 'edge_') || str_starts_with($rawMatId, 'CHI-') || $isEdgeBandWarehouse) {
                        $itemType = 'edge_band';
                    } else {
                        $itemType = 'board';
                    }
                }

                $cleanId = str_replace(['edge_', 'board_', 'CHI-'], '', $rawMatId);

                if ($itemType === 'edge_band') {
                    $board = is_numeric($cleanId) 
                        ? WoodBoard::find($cleanId) 
                        : WoodBoard::where('color_code', $cleanId)->first();

                    if (!$board) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $price = (float)($item['unit_price'] ?? ($board->edge_band_cost_price ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryIssueItem::create($this->sanitizeItemData('inventory_issue_items', [
                        'issue_id'            => $issue->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    $board->edge_band_current_stock = max(0, (float)$board->edge_band_current_stock - $qty);
                    $board->save();
                    $stockAfter = $board->edge_band_current_stock;

                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'date'                => $date,
                        'type'                => 'issue',
                        'voucher_code'        => $code,
                        'reference_id'        => $issue->id,
                        'reference_type'      => InventoryIssue::class,
                        'in_qty'              => 0,
                        'out_qty'             => $qty,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Xuất kho chỉ theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                } else {
                    $priceId = $item['wood_board_price_id'] ?? null;
                    $boardId = is_numeric($cleanId) ? (int)$cleanId : null;

                    $boardPrice = null;
                    if ($priceId) {
                        $boardPrice = \App\Models\WoodBoardPrice::find($priceId);
                    } elseif ($boardId) {
                        $maybePrice = \App\Models\WoodBoardPrice::find($boardId);
                        if ($maybePrice) {
                            $boardPrice = $maybePrice;
                        }
                    }

                    if ($boardPrice) {
                        $boardId = $boardPrice->wood_board_id;
                    }

                    if (!$boardId && !$boardPrice) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    $board = WoodBoard::find($boardId);
                    $price = (float)($item['unit_price'] ?? ($boardPrice?->price_board ?? $board?->cost_price ?? 0));
                    $lineTotal = $qty * $price;

                    InventoryIssueItem::create($this->sanitizeItemData('inventory_issue_items', [
                        'issue_id'            => $issue->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'quantity'            => $qty,
                        'unit_price'          => $price,
                        'total_price'         => $lineTotal,
                        'notes'               => $item['notes'] ?? null,
                    ]));

                    // Update WoodBoardPrice stock
                    $stockAfter = 0;
                    if ($boardPrice) {
                        $boardPrice->stock = max(0, $boardPrice->stock - $qty);
                        $boardPrice->save();
                        $stockAfter = $boardPrice->stock;
                    }

                    // Update WoodBoard stock
                    if ($board) {
                        $board->current_stock = $board->prices()->sum('stock') ?: max(0, $board->current_stock - $qty);
                        $board->save();
                        if (!$boardPrice) {
                            $stockAfter = $board->current_stock;
                        }
                    }

                    // Create stock transaction
                    InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                        'warehouse_id'        => $warehouse->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'date'                => $date,
                        'type'                => 'issue',
                        'voucher_code'        => $code,
                        'reference_id'        => $issue->id,
                        'reference_type'      => InventoryIssue::class,
                        'in_qty'              => 0,
                        'out_qty'             => $qty,
                        'stock_after'         => $stockAfter,
                        'unit_price'          => $price,
                        'notes'               => $data['notes'] ?? ('Xuất kho theo phiếu ' . $code),
                        'created_by'          => $userId,
                    ]));

                    $totalQty += $qty;
                    $totalAmount += $lineTotal;
                }
            }

            $issue->update([
                'total_quantity' => $totalQty,
                'total_amount'   => $totalAmount,
            ]);

            return $issue->fresh();
        });
    }

    /**
     * Create Stocktake (PKK)
     */
    public function createStocktake(Warehouse $warehouse, array $data, array $items, $userId = null): InventoryStocktake
    {
        $this->ensureSchemaUpdated();

        return DB::transaction(function () use ($warehouse, $data, $items, $userId) {
            $code = !empty($data['code']) ? $data['code'] : $this->generateCode('PKK', 'inventory_stocktakes');
            $date = !empty($data['date']) ? $data['date'] : date('Y-m-d');

            $stocktake = InventoryStocktake::create([
                'code' => $code,
                'date' => $date,
                'warehouse_id' => $warehouse->id,
                'creator_name' => $data['creator_name'] ?? 'Kế toán / Thủ kho',
                'total_book_quantity' => 0,
                'total_actual_quantity' => 0,
                'total_difference' => 0,
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
            ]);

            $totalBook = 0;
            $totalActual = 0;
            $totalDiff = 0;
            $isEdgeBandWarehouse = ($warehouse->code === 'KHO-CHI') || (str_contains(mb_strtolower($warehouse->name), 'chỉ'));

            foreach ($items as $item) {
                $rawMatId = (string)($item['wood_board_id'] ?? $item['material_id'] ?? '');
                $itemType = $item['item_type'] ?? null;
                if (!$itemType) {
                    if (str_starts_with($rawMatId, 'edge_') || str_starts_with($rawMatId, 'CHI-') || $isEdgeBandWarehouse) {
                        $itemType = 'edge_band';
                    } else {
                        $itemType = 'board';
                    }
                }

                $cleanId = str_replace(['edge_', 'board_', 'CHI-'], '', $rawMatId);

                if ($itemType === 'edge_band') {
                    $board = is_numeric($cleanId) 
                        ? WoodBoard::find($cleanId) 
                        : WoodBoard::where('color_code', $cleanId)->first();

                    if (!$board) continue;

                    $bookQty = isset($item['book_quantity']) && $item['book_quantity'] !== '' && $item['book_quantity'] !== null 
                        ? (float)$item['book_quantity'] 
                        : (float)($board->edge_band_current_stock ?? 0);
                    $actualQty = isset($item['actual_quantity']) && $item['actual_quantity'] !== '' && $item['actual_quantity'] !== null 
                        ? (float)$item['actual_quantity'] 
                        : 0;
                    $diff = $actualQty - $bookQty;

                    InventoryStocktakeItem::create($this->sanitizeItemData('inventory_stocktake_items', [
                        'stocktake_id'        => $stocktake->id,
                        'item_type'           => 'edge_band',
                        'wood_board_id'       => $board->id,
                        'wood_board_price_id' => null,
                        'book_quantity'       => $bookQty,
                        'actual_quantity'     => $actualQty,
                        'difference'          => $diff,
                        'reason'              => $item['reason'] ?? ($item['notes'] ?? null),
                    ]));

                    $totalBook += $bookQty;
                    $totalActual += $actualQty;
                    $totalDiff += $diff;
                } else {
                    $priceId = $item['wood_board_price_id'] ?? null;
                    $boardId = is_numeric($cleanId) ? (int)$cleanId : null;

                    $boardPrice = null;
                    if ($priceId) {
                        $boardPrice = \App\Models\WoodBoardPrice::find($priceId);
                    } elseif ($boardId) {
                        $maybePrice = \App\Models\WoodBoardPrice::find($boardId);
                        if ($maybePrice) {
                            $boardPrice = $maybePrice;
                            $boardId = $maybePrice->wood_board_id;
                        }
                    }

                    if ($boardPrice) {
                        $boardId = $boardPrice->wood_board_id;
                    }

                    if (!$boardId && !$boardPrice) continue;

                    $bookQty = isset($item['book_quantity']) && $item['book_quantity'] !== '' && $item['book_quantity'] !== null 
                        ? (float)$item['book_quantity'] 
                        : (float)($boardPrice?->stock ?? 0);
                    $actualQty = isset($item['actual_quantity']) && $item['actual_quantity'] !== '' && $item['actual_quantity'] !== null 
                        ? (float)$item['actual_quantity'] 
                        : 0;
                    $diff = $actualQty - $bookQty;

                    InventoryStocktakeItem::create($this->sanitizeItemData('inventory_stocktake_items', [
                        'stocktake_id'        => $stocktake->id,
                        'item_type'           => 'board',
                        'wood_board_id'       => $boardId,
                        'wood_board_price_id' => $boardPrice?->id,
                        'book_quantity'       => $bookQty,
                        'actual_quantity'     => $actualQty,
                        'difference'          => $diff,
                        'reason'              => $item['reason'] ?? ($item['notes'] ?? null),
                    ]));

                    $totalBook += $bookQty;
                    $totalActual += $actualQty;
                    $totalDiff += $diff;
                }
            }

            $stocktake->update([
                'total_book_quantity' => $totalBook,
                'total_actual_quantity' => $totalActual,
                'total_difference' => $totalDiff,
            ]);

            return $stocktake;
        });
    }

    /**
     * Balance Stocktake (Cân bằng kho - tự động điều chỉnh tồn kho theo thực tế)
     */
    public function balanceStocktake(InventoryStocktake $stocktake, $userId = null): void
    {
        if ($stocktake->status === 'balanced') return;

        $this->ensureSchemaUpdated();

        DB::transaction(function () use ($stocktake, $userId) {
            foreach ($stocktake->items as $item) {
                if ($item->item_type === 'edge_band') {
                    $board = $item->woodBoard;
                    if (!$board) continue;
                    $diff = $item->difference;
                    if ($diff != 0) {
                        $board->edge_band_current_stock = $item->actual_quantity;
                        $board->save();
                        $stockAfter = $board->edge_band_current_stock;

                        InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                            'warehouse_id'        => $stocktake->warehouse_id,
                            'item_type'           => 'edge_band',
                            'wood_board_id'       => $board->id,
                            'wood_board_price_id' => null,
                            'date'                => $stocktake->date,
                            'type'                => 'stocktake',
                            'voucher_code'        => $stocktake->code,
                            'reference_id'        => $stocktake->id,
                            'reference_type'      => InventoryStocktake::class,
                            'in_qty'              => $diff > 0 ? $diff : 0,
                            'out_qty'             => $diff < 0 ? abs($diff) : 0,
                            'stock_after'         => $stockAfter,
                            'unit_price'          => $board->edge_band_cost_price ?: 0,
                            'notes'               => 'Cân bằng kho chỉ sau kiểm kê ' . $stocktake->code . ($item->reason ? (' (' . $item->reason . ')') : ''),
                            'created_by'          => $userId,
                        ]));
                    }
                } else {
                    $board = $item->woodBoard;
                    $boardPrice = $item->wood_board_price_id ? \App\Models\WoodBoardPrice::find($item->wood_board_price_id) : null;
                    if (!$board && !$boardPrice) continue;

                    $diff = $item->difference;
                    if ($diff != 0) {
                        $stockAfter = 0;
                        if ($boardPrice) {
                            $boardPrice->stock = $item->actual_quantity;
                            $boardPrice->save();
                            $stockAfter = $boardPrice->stock;
                        }

                        if ($board) {
                            $board->current_stock = $board->prices()->sum('stock') ?: $item->actual_quantity;
                            $board->save();
                            if (!$boardPrice) {
                                $stockAfter = $board->current_stock;
                            }
                        }

                        InventoryTransaction::create($this->sanitizeItemData('inventory_transactions', [
                            'warehouse_id'        => $stocktake->warehouse_id,
                            'item_type'           => 'board',
                            'wood_board_id'       => $board?->id ?? $boardPrice?->wood_board_id,
                            'wood_board_price_id' => $boardPrice?->id,
                            'date'                => $stocktake->date,
                            'type'                => 'stocktake',
                            'voucher_code'        => $stocktake->code,
                            'reference_id'        => $stocktake->id,
                            'reference_type'      => InventoryStocktake::class,
                            'in_qty'              => $diff > 0 ? $diff : 0,
                            'out_qty'             => $diff < 0 ? abs($diff) : 0,
                            'stock_after'         => $stockAfter,
                            'unit_price'          => $boardPrice?->price_board ?? $board?->cost_price ?? 0,
                            'notes'               => 'Cân bằng kho sau kiểm kê ' . $stocktake->code . ($item->reason ? (' (' . $item->reason . ')') : ''),
                            'created_by'          => $userId,
                        ]));
                    }
                }
            }

            $stocktake->update([
                'status' => 'balanced',
                'balanced_at' => now(),
                'balanced_by' => $userId,
            ]);
        });
    }

    /**
     * Delete Goods Receipt (PNK) and reverse/deduct stock
     */
    public function deleteReceipt(InventoryReceipt $receipt): void
    {
        DB::transaction(function () use ($receipt) {
            $receipt->loadMissing('items');

            if ($receipt->status === 'completed') {
                foreach ($receipt->items as $item) {
                    $qty = (float)($item->quantity ?? 0);
                    if ($qty <= 0) continue;

                    if ($item->item_type === 'edge_band') {
                        if ($item->wood_board_id) {
                            $board = WoodBoard::find($item->wood_board_id);
                            if ($board) {
                                $board->edge_band_current_stock = max(0, (float)$board->edge_band_current_stock - $qty);
                                $board->save();
                            }
                        }
                    } else {
                        if ($item->wood_board_price_id) {
                            $boardPrice = \App\Models\WoodBoardPrice::find($item->wood_board_price_id);
                            if ($boardPrice) {
                                $boardPrice->stock = max(0, (float)$boardPrice->stock - $qty);
                                $boardPrice->save();
                            }
                        }

                        if ($item->wood_board_id) {
                            $board = WoodBoard::find($item->wood_board_id);
                            if ($board) {
                                $board->current_stock = $board->prices()->sum('stock') ?: max(0, (float)$board->current_stock - $qty);
                                $board->save();
                            }
                        }
                    }
                }
            }

            InventoryTransaction::where(function ($q) use ($receipt) {
                $q->where('reference_type', InventoryReceipt::class)
                  ->where('reference_id', $receipt->id);
            })->orWhere('voucher_code', $receipt->code)
              ->delete();

            $receipt->items()->delete();
            $receipt->delete();
        });
    }

    /**
     * Delete Goods Issue (PXK) and restore/add back stock
     */
    public function deleteIssue(InventoryIssue $issue): void
    {
        DB::transaction(function () use ($issue) {
            $issue->loadMissing('items');

            if ($issue->status === 'completed') {
                foreach ($issue->items as $item) {
                    $qty = (float)($item->quantity ?? 0);
                    if ($qty <= 0) continue;

                    if ($item->item_type === 'edge_band') {
                        if ($item->wood_board_id) {
                            $board = WoodBoard::find($item->wood_board_id);
                            if ($board) {
                                $board->edge_band_current_stock = (float)$board->edge_band_current_stock + $qty;
                                $board->save();
                            }
                        }
                    } else {
                        if ($item->wood_board_price_id) {
                            $boardPrice = \App\Models\WoodBoardPrice::find($item->wood_board_price_id);
                            if ($boardPrice) {
                                $boardPrice->stock = (float)$boardPrice->stock + $qty;
                                $boardPrice->save();
                            }
                        }

                        if ($item->wood_board_id) {
                            $board = WoodBoard::find($item->wood_board_id);
                            if ($board) {
                                $board->current_stock = $board->prices()->sum('stock') ?: ((float)$board->current_stock + $qty);
                                $board->save();
                            }
                        }
                    }
                }
            }

            InventoryTransaction::where(function ($q) use ($issue) {
                $q->where('reference_type', InventoryIssue::class)
                  ->where('reference_id', $issue->id);
            })->orWhere('voucher_code', $issue->code)
              ->delete();

            $issue->items()->delete();
            $issue->delete();
        });
    }

    /**
     * Đồng bộ Đơn nhập kho sang Phiếu Nhập kho (PNK) và Phiếu Xuất kho (PXK)
     */
    public function syncOrderWarehouseReceiptAndIssue(Order $order): void
    {
        $this->ensureSchemaUpdated();

        // 1. Kiểm tra đơn hàng có phải là Đơn Nhập kho không
        $isNhapKho = false;
        $cust = $order->customer ?: ($order->customer_id ? \App\Models\Customer::find($order->customer_id) : null);
        if ($cust && ($cust->customer_code === 'NK' || str_contains(mb_strtolower($cust->name), 'nhập kho') || str_contains(mb_strtolower($cust->name), 'nhap kho'))) {
            $isNhapKho = true;
        }
        if (!$isNhapKho && $order->customer_name && (str_contains(mb_strtolower($order->customer_name), 'nhập kho') || str_contains(mb_strtolower($order->customer_name), 'nhap kho'))) {
            $isNhapKho = true;
        }
        if (!$isNhapKho && $order->supplies()->where('inventory_action', 'subtract')->exists()) {
            $isNhapKho = true;
        }

        // Tìm các phiếu nhập/xuất trước đó gắn với đơn này (qua order_id hoặc ghi chú)
        $existingReceipt = InventoryReceipt::where('order_id', $order->id)
            ->orWhere(function ($q) use ($order) {
                $q->where('notes', 'like', "%[Đơn nhập kho #{$order->order_code}]%")
                  ->orWhere('supplier_name', 'like', "%Đơn {$order->order_code}%");
            })->first();

        $existingIssue = InventoryIssue::where('order_id', $order->id)
            ->orWhere(function ($q) use ($order) {
                $q->where('notes', 'like', "%[Đơn nhập kho #{$order->order_code}]%")
                  ->orWhere('recipient', 'like', "%Đơn {$order->order_code}%");
            })->first();

        // Nếu đơn không phải là Đơn Nhập kho HOẶC đang là đơn nháp (draft):
        // Xóa hoàn trả tồn kho nếu trước đó từng tạo phiếu
        if (!$isNhapKho || $order->status === 'draft') {
            if ($existingReceipt) {
                $this->deleteReceipt($existingReceipt);
            }
            if ($existingIssue) {
                $this->deleteIssue($existingIssue);
            }
            return;
        }

        // 2. Tách vật tư theo hành vi: nhập kho (add) hoặc xuất kho (subtract)
        $order->load(['supplies.items', 'supplies.glassItems', 'supplies.minLateItems']);
        
        $addItems = [];
        $subtractItems = [];
        $defaultWarehouse = Warehouse::where('code', 'KHO-VAN')->first() ?? Warehouse::first();
        $targetWarehouseId = $defaultWarehouse?->id ?? 1;

        foreach ($order->supplies as $supply) {
            $action = $supply->inventory_action ?? 'add';
            $code = trim($supply->order_supply_code ?: '');
            $name = trim($supply->supply_name ?: '');
            if (!$code && !$name) continue;

            // Tính số lượng
            $qty = (float)($supply->quantity ?? 0);
            if ($qty <= 0) {
                $childItemsCount = ($supply->items ? $supply->items->sum('quantity') : 0) 
                    ?: (($supply->glassItems ? $supply->glassItems->sum('quantity') : 0) 
                    ?: (($supply->minLateItems ? $supply->minLateItems->sum('quantity') : 0) ?: 0));
                $qty = (float)$childItemsCount;
            }
            if ($qty <= 0) {
                $qty = 1;
            }

            // Tìm mặt hàng trong kho
            $boardPrice = WoodBoardPrice::where('code', $code)->first();
            $woodBoard = null;
            if (!$boardPrice && $code) {
                $woodBoard = WoodBoard::where('color_code', $code)->first();
            }
            if (!$boardPrice && !$woodBoard && $name) {
                $boardPrice = WoodBoardPrice::where('name', $name)->first();
                if (!$boardPrice) {
                    $woodBoard = WoodBoard::where('color_code', $name)->first();
                }
            }

            if (!$boardPrice && !$woodBoard) {
                // Tạo mới WoodBoard nếu chưa có
                $woodBoard = WoodBoard::create([
                    'color_code'    => $code ?: $name,
                    'price_group'   => 'Acrylic Foil',
                    'unit'          => 'Tấm',
                    'cost_price'    => 0,
                    'min_stock'     => 5,
                    'initial_stock' => 0,
                    'current_stock' => 0,
                    'warehouse_id'  => $targetWarehouseId,
                    'status'        => 'active',
                ]);
            }

            $materialId = $boardPrice ? $boardPrice->id : $woodBoard->id;
            $unitPrice = (float)($boardPrice ? $boardPrice->price_board : ($woodBoard ? $woodBoard->cost_price : 0));
            $itemType = 'board';

            if ($supply->warehouse_id) {
                $targetWarehouseId = $supply->warehouse_id;
            } elseif ($woodBoard && $woodBoard->warehouse_id) {
                $targetWarehouseId = $woodBoard->warehouse_id;
            }

            $itemData = [
                'material_id'         => $materialId,
                'wood_board_id'       => $boardPrice ? $boardPrice->wood_board_id : $woodBoard->id,
                'wood_board_price_id' => $boardPrice ? $boardPrice->id : null,
                'item_type'           => $itemType,
                'quantity'            => $qty,
                'unit_price'          => $unitPrice,
                'notes'               => "Từ đơn nhập kho {$order->order_code}",
            ];

            if ($action === 'subtract') {
                $subtractItems[] = $itemData;
            } else {
                $addItems[] = $itemData;
            }
        }

        $warehouse = Warehouse::find($targetWarehouseId) ?? $defaultWarehouse;
        $orderDate = $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') : date('Y-m-d');
        $userId = Auth::id() ?: ($order->technical_approved_by ?: 1);

        // 3. Xử lý PHIẾU NHẬP KHO (addItems)
        if (!empty($addItems)) {
            $receiptData = [
                'order_id'      => $order->id,
                'code'          => $existingReceipt ? $existingReceipt->code : $this->generateCode('PNK', 'inventory_receipts'),
                'date'          => $orderDate,
                'supplier_name' => "Đơn hàng {$order->order_code} (Khách: Nhập kho)",
                'deliverer'     => $order->customer_name ?: 'Bộ phận sản xuất',
                'receiver'      => Auth::user()->name ?? 'Thủ kho',
                'notes'         => "Tự động tạo từ [Đơn nhập kho #{$order->order_code}]",
            ];

            if ($existingReceipt) {
                $this->updateReceipt($existingReceipt, $warehouse, $receiptData, $addItems, $userId);
                $existingReceipt->update(['order_id' => $order->id]);
            } else {
                $newReceipt = $this->createReceipt($warehouse, $receiptData, $addItems, $userId);
                $newReceipt->update(['order_id' => $order->id]);
            }
        } else {
            if ($existingReceipt) {
                $this->deleteReceipt($existingReceipt);
            }
        }

        // 4. Xử lý PHIẾU XUẤT KHO (subtractItems)
        if (!empty($subtractItems)) {
            $issueData = [
                'order_id'   => $order->id,
                'code'       => $existingIssue ? $existingIssue->code : $this->generateCode('PXK', 'inventory_issues'),
                'date'       => $orderDate,
                'recipient'  => "Đơn hàng {$order->order_code} (Khách: Nhập kho)",
                'deliverer'  => Auth::user()->name ?? 'Thủ kho',
                'reason'     => "Xuất kho theo [Đơn nhập kho #{$order->order_code}]",
                'notes'      => "Tự động tạo từ [Đơn nhập kho #{$order->order_code}]",
            ];

            if ($existingIssue) {
                $this->deleteIssue($existingIssue);
            }
            $newIssue = $this->createIssue($warehouse, $issueData, $subtractItems, $userId);
            $newIssue->update(['order_id' => $order->id]);
        } else {
            if ($existingIssue) {
                $this->deleteIssue($existingIssue);
            }
        }
    }

    /**
     * Delete Stocktake (PKK) and revert stock adjustment if already balanced
     */
    public function deleteStocktake(InventoryStocktake $stocktake): void
    {
        DB::transaction(function () use ($stocktake) {
            $stocktake->loadMissing('items');

            if ($stocktake->status === 'balanced') {
                foreach ($stocktake->items as $item) {
                    $diff = (float)($item->difference ?? 0);
                    if ($diff == 0 || !$item->wood_board_id) continue;

                    $board = WoodBoard::find($item->wood_board_id);
                    if ($board) {
                        if ($item->item_type === 'edge_band') {
                            $board->edge_band_current_stock = max(0, (float)$board->edge_band_current_stock - $diff);
                        } else {
                            $board->current_stock = max(0, (float)$board->current_stock - $diff);
                        }
                        $board->save();
                    }
                }
            }

            InventoryTransaction::where(function ($q) use ($stocktake) {
                $q->where('reference_type', InventoryStocktake::class)
                  ->where('reference_id', $stocktake->id);
            })->orWhere('voucher_code', $stocktake->code)
              ->delete();

            $stocktake->items()->delete();
            $stocktake->delete();
        });
    }

    /**
     * Initialize Acrylic WoodBoards and historical data from Excel file
     */
    public function seedAcrylicFromExcel(string $filePath): Warehouse
    {
        return DB::transaction(function () use ($filePath) {
            $warehouse = Warehouse::firstOrCreate(
                ['name' => 'Kho Phôi Acrylic Trung Quốc'],
                [
                    'code' => 'KHO-ACRYLIC-TQ',
                    'item_name' => 'Tấm Acrylic',
                    'address' => 'Xưởng Gervin',
                    'manager' => 'Thủ kho Gervin',
                    'status' => 'active',
                    'description' => 'Kho nguyên vật liệu chính Acrylic nhập khẩu Trung Quốc'
                ]
            );

            if (!file_exists($filePath)) {
                return $warehouse;
            }

            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();
            $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

            // 1. Read items (Rows 5 to highestRow)
            $items = [];
            for ($r = 5; $r <= $highestRow; $r++) {
                $code = trim((string)$sheet->getCell('E' . $r)->getValue());
                if (!$code || stripos($code, 'cộng') !== false || stripos($code, 'tổng') !== false) {
                    continue;
                }

                $originCode = trim((string)$sheet->getCell('C' . $r)->getValue());
                $group = trim((string)$sheet->getCell('F' . $r)->getValue()) ?: 'Acrylic TQ';
                $initStock = (float)$sheet->getCell('G' . $r)->getCalculatedValue();

                $board = WoodBoard::updateOrCreate(
                    [
                        'color_code' => $code,
                    ],
                    [
                        'warehouse_id' => $warehouse->id,
                        'origin_code' => $originCode ?: null,
                        'price_group' => $group,
                        'unit' => 'Tấm',
                        'cost_price' => 0,
                        'min_stock' => 150,
                        'initial_stock' => $initStock,
                        'current_stock' => $initStock, // will accumulate with periods below
                        'status' => 'active',
                    ]
                );

                $items[$code] = [
                    'board' => $board,
                    'row' => $r,
                    'initial_stock' => $initStock,
                    'running_stock' => $initStock,
                ];

                // Initial transaction
                if ($initStock > 0) {
                    InventoryTransaction::updateOrCreate(
                        [
                            'warehouse_id' => $warehouse->id,
                            'wood_board_id' => $board->id,
                            'voucher_code' => 'DK-2023',
                        ],
                        [
                            'date' => '2023-12-31',
                            'type' => 'initial',
                            'in_qty' => $initStock,
                            'out_qty' => 0,
                            'stock_after' => $initStock,
                            'notes' => 'Số dư tồn đầu kỳ (Cuối năm 2023)',
                        ]
                    );
                }
            }

            // 2. Read monthly periods and create receipts/issues + update stock
            for ($col = 8; $col <= $highestColIdx; $col += 3) {
                $colLetterIn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $colLetterOut = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);

                $title = trim((string)$sheet->getCell($colLetterIn . '1')->getValue());
                if (!$title && $col <= $highestColIdx) {
                    $prevCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);
                    $title = trim((string)$sheet->getCell($prevCol . '1')->getValue());
                }
                if (!$title) continue;

                $year = null;
                $month = null;
                if (preg_match('/(\d{4})/', $title, $yMatches)) $year = (int)$yMatches[1];
                if (preg_match('/(?:THÁNG\s*)(\d+)/i', $title, $mMatches)) {
                    $month = (int)$mMatches[1];
                } elseif (preg_match('/(\d+)\s*THÁNG/i', $title, $mMatches)) {
                    $month = (int)$mMatches[1];
                }
                if (!$year || !$month) continue;

                $day = 28;
                if (preg_match('/^(\d+)\s*THÁNG/i', $title, $dMatches)) {
                    $day = (int)$dMatches[1];
                }
                $recordDate = sprintf('%04d-%02d-%02d', $year, $month, min($day, 28));

                $inItems = [];
                $outItems = [];

                foreach ($items as $code => &$info) {
                    $r = $info['row'];
                    $inVal = (float)$sheet->getCell($colLetterIn . $r)->getCalculatedValue();
                    $outVal = (float)$sheet->getCell($colLetterOut . $r)->getCalculatedValue();

                    $info['running_stock'] = $info['running_stock'] + $inVal - $outVal;

                    if ($inVal > 0) {
                        $inItems[] = [
                            'wood_board_id' => $info['board']->id,
                            'quantity' => $inVal,
                            'unit_price' => 0,
                            'stock_after' => $info['running_stock'],
                        ];
                    }
                    if ($outVal > 0) {
                        $outItems[] = [
                            'wood_board_id' => $info['board']->id,
                            'quantity' => $outVal,
                            'unit_price' => 0,
                            'stock_after' => $info['running_stock'],
                        ];
                    }
                }
                unset($info);

                // Create Receipt if in items
                if (!empty($inItems)) {
                    $pnkCode = sprintf('PNK%04d%02d', $year, $month);
                    $rc = InventoryReceipt::updateOrCreate(
                        ['code' => $pnkCode],
                        [
                            'date' => $recordDate,
                            'warehouse_id' => $warehouse->id,
                            'supplier_name' => 'Nhà máy cung ứng TQ',
                            'receiver' => 'Thủ kho Gervin',
                            'total_quantity' => collect($inItems)->sum('quantity'),
                            'total_amount' => 0,
                            'notes' => "Tổng hợp nhập kho {$title}",
                            'status' => 'completed',
                        ]
                    );
                    $rc->items()->delete();
                    foreach ($inItems as $ii) {
                        InventoryReceiptItem::create([
                            'receipt_id' => $rc->id,
                            'wood_board_id' => $ii['wood_board_id'],
                            'quantity' => $ii['quantity'],
                            'unit_price' => 0,
                            'total_price' => 0,
                        ]);
                        InventoryTransaction::updateOrCreate(
                            [
                                'warehouse_id' => $warehouse->id,
                                'wood_board_id' => $ii['wood_board_id'],
                                'voucher_code' => $pnkCode,
                            ],
                            [
                                'date' => $recordDate,
                                'type' => 'receipt',
                                'reference_id' => $rc->id,
                                'reference_type' => InventoryReceipt::class,
                                'in_qty' => $ii['quantity'],
                                'out_qty' => 0,
                                'stock_after' => $ii['stock_after'],
                                'notes' => "Nhập kho {$title}",
                            ]
                        );
                    }
                }

                // Create Issue if out items
                if (!empty($outItems)) {
                    $pxkCode = sprintf('PXK%04d%02d', $year, $month);
                    $iss = InventoryIssue::updateOrCreate(
                        ['code' => $pxkCode],
                        [
                            'date' => $recordDate,
                            'warehouse_id' => $warehouse->id,
                            'recipient' => 'Xưởng sản xuất Gervin',
                            'reason' => 'Xuất sản xuất',
                            'total_quantity' => collect($outItems)->sum('quantity'),
                            'total_amount' => 0,
                            'notes' => "Tổng hợp tiêu thụ {$title}",
                            'status' => 'completed',
                        ]
                    );
                    $iss->items()->delete();
                    foreach ($outItems as $oi) {
                        InventoryIssueItem::create([
                            'issue_id' => $iss->id,
                            'wood_board_id' => $oi['wood_board_id'],
                            'quantity' => $oi['quantity'],
                            'unit_price' => 0,
                            'total_price' => 0,
                        ]);
                        InventoryTransaction::updateOrCreate(
                            [
                                'warehouse_id' => $warehouse->id,
                                'wood_board_id' => $oi['wood_board_id'],
                                'voucher_code' => $pxkCode,
                            ],
                            [
                                'date' => $recordDate,
                                'type' => 'issue',
                                'reference_id' => $iss->id,
                                'reference_type' => InventoryIssue::class,
                                'in_qty' => 0,
                                'out_qty' => $oi['quantity'],
                                'stock_after' => $oi['stock_after'],
                                'notes' => "Tiêu thụ {$title}",
                            ]
                        );
                    }
                }
            }

            // Save final running stock to each board and ensure wood_board_prices exist
            $boardTypes = \App\Models\WoodBoardType::orderBy('display_order')->get();
            $primaryType = $boardTypes->first();

            foreach ($items as $code => $info) {
                $b = $info['board'];
                $b->current_stock = $info['running_stock'];
                $b->save();

                foreach ($boardTypes as $bt) {
                    $computedCode = $b->color_code . ($bt->prefix ?? '');
                    $p = \App\Models\WoodBoardPrice::firstOrCreate(
                        [
                            'wood_board_id'      => $b->id,
                            'wood_board_type_id' => $bt->id,
                        ],
                        [
                            'code'        => $computedCode,
                            'name'        => $bt->name,
                            'thickness'   => '17mm',
                            'price_board' => 0,
                            'price_m2'    => 0,
                            'stock'       => ($bt->id === $primaryType?->id) ? $info['running_stock'] : 0,
                        ]
                    );

                    if ($bt->id === $primaryType?->id && $p->stock == 0 && $info['running_stock'] > 0) {
                        $p->update(['stock' => $info['running_stock']]);
                    }
                }
            }

            return $warehouse;
        });
    }

    /**
     * Tự động chạy migration nếu bảng thiếu cột item_type (đảm bảo không bị lỗi Unknown column)
     */
    protected function ensureSchemaUpdated(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            $needsMigrate = false;
            foreach (['inventory_receipt_items', 'inventory_issue_items', 'inventory_transactions'] as $tbl) {
                if (\Illuminate\Support\Facades\Schema::hasTable($tbl) && !\Illuminate\Support\Facades\Schema::hasColumn($tbl, 'item_type')) {
                    $needsMigrate = true;
                    break;
                }
            }
            if ($needsMigrate) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Auto-migrate failed: ' . $e->getMessage());
        }
    }

    /**
     * Lọc bỏ cột item_type nếu bảng chưa có cột này để tránh lỗi SQL 1054
     */
    protected function sanitizeItemData(string $table, array $data): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'item_type')) {
            unset($data['item_type']);
        }
        return $data;
    }

    /**
     * Nhập tồn kho và thông tin từ file Excel Ton_Kho_Tam_Van_
     */
    public function importStockFromExcel(string $filePath, ?int $warehouseId = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Không tìm thấy file: {$filePath}");
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
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

        $isMainWarehouse = ($warehouse->code === 'KHO-VAN' || $warehouse->id == 1);
        $isEdgeBandWarehouse = ($warehouse->code === 'KHO-CHI' || stripos($warehouse->name, 'chỉ') !== false || stripos($warehouse->item_name, 'chỉ') !== false);

        $types = WoodBoardType::all()->keyBy('name');
        $typeSuffixes = [
            1 => '',
            2 => '.TP',
            3 => '.TP.2M',
            4 => '.TP.PVC.1M',
            5 => '.TP.PVC',
            6 => '.TP.PVC.2M',
        ];

        $updatedCount = 0;
        $createdCount = 0;
        $totalStock = 0;

        DB::transaction(function () use ($sheet, $highestRow, $warehouse, $isMainWarehouse, $isEdgeBandWarehouse, $types, $typeSuffixes, &$updatedCount, &$createdCount, &$totalStock) {
            for ($r = 4; $r <= $highestRow; $r++) {
                $sku = trim((string)$sheet->getCell('B' . $r)->getFormattedValue());
                if (!$sku || stripos($sku, 'tổng') !== false) continue;

                $name = trim((string)$sheet->getCell('C' . $r)->getFormattedValue());
                $foilName = trim((string)$sheet->getCell('D' . $r)->getFormattedValue());
                $origin = trim((string)$sheet->getCell('E' . $r)->getFormattedValue());
                $group = trim((string)$sheet->getCell('F' . $r)->getFormattedValue());
                $unit = trim((string)$sheet->getCell('G' . $r)->getFormattedValue());
                $stockRaw = $sheet->getCell('H' . $r)->getCalculatedValue();
                $stock = is_numeric($stockRaw) ? (float)$stockRaw : 0;

                if ($foilName === '0') $foilName = '';
                if ($origin === '0') $origin = '';

                // Base color code
                $baseCode = $sku;
                if (str_contains($sku, '.')) {
                    $baseCode = explode('.', $sku)[0];
                }

                // 1. Nhập vào kho chỉ dán viền
                if ($isEdgeBandWarehouse) {
                    $board = WoodBoard::where('color_code', $baseCode)->first();
                    if (!$board) {
                        $board = WoodBoard::create([
                            'color_code'              => $baseCode,
                            'edge_band_code'          => 'CHI-' . $baseCode,
                            'edge_band_name'          => 'Chỉ ' . $baseCode,
                            'price_group'             => $group ?: 'Chỉ dán viền',
                            'origin_code'             => $origin ?: null,
                            'foil_name'               => $foilName ?: null,
                            'unit'                    => 'Tấm',
                            'edge_band_unit'          => 'm',
                            'cost_price'              => 0,
                            'min_stock'               => 5,
                            'edge_band_min_stock'     => 50,
                            'edge_band_cost_price'    => 0,
                            'edge_band_current_stock' => ($warehouse->code === 'KHO-CHI' || $warehouse->id == 2) ? $stock : 0,
                            'warehouse_id'            => $warehouse->id,
                            'status'                  => 'active',
                        ]);
                        $createdCount++;
                    } else {
                        if (!empty($origin) && $board->origin_code !== $origin) $board->origin_code = $origin;
                        if (!empty($foilName) && $board->foil_name !== $foilName) $board->foil_name = $foilName;
                        $updatedCount++;
                    }

                    if ($warehouse->code === 'KHO-CHI' || $warehouse->id == 2) {
                        $board->edge_band_current_stock = $stock;
                        $board->save();
                    } else {
                        $currentEdgeWhStock = (float)\App\Models\InventoryTransaction::where('warehouse_id', $warehouse->id)
                            ->where('wood_board_id', $board->id)
                            ->where('item_type', 'edge_band')
                            ->sum(DB::raw('in_qty - out_qty'));
                        $diff = $stock - $currentEdgeWhStock;
                        if ($diff != 0) {
                            \App\Models\InventoryTransaction::create([
                                'warehouse_id'        => $warehouse->id,
                                'wood_board_id'       => $board->id,
                                'item_type'           => 'edge_band',
                                'date'                => now()->toDateString(),
                                'type'                => 'initial',
                                'voucher_code'        => 'CBK-' . strtoupper(\Illuminate\Support\Str::random(6)),
                                'in_qty'              => $diff > 0 ? $diff : 0,
                                'out_qty'             => $diff < 0 ? abs($diff) : 0,
                                'stock_after'         => $stock,
                                'unit_price'          => (float)$board->edge_band_cost_price,
                                'notes'               => 'Nhập tồn kho từ Excel - Kho ' . $warehouse->name,
                                'created_by'          => \Illuminate\Support\Facades\Auth::id(),
                            ]);
                            $board->edge_band_current_stock = max(0, (float)$board->edge_band_current_stock + $diff);
                            $board->save();
                        }
                    }

                    $totalStock += $stock;
                    continue;
                }

                // 2. Nhập vào kho tấm ván (kho chính hoặc kho phụ)
                $board = WoodBoard::where('color_code', $baseCode)->first();
                if (!$board) {
                    $board = WoodBoard::create([
                        'color_code'    => $baseCode,
                        'price_group'   => $group ?: 'Acrylic Foil',
                        'origin_code'   => $origin ?: null,
                        'foil_name'     => $foilName ?: null,
                        'unit'          => $unit ?: 'Tấm',
                        'cost_price'    => 0,
                        'min_stock'     => 5,
                        'initial_stock' => $isMainWarehouse ? $stock : 0,
                        'current_stock' => $isMainWarehouse ? $stock : 0,
                        'warehouse_id'  => 1,
                        'status'        => 'active',
                    ]);
                } else {
                    $dirty = false;
                    if (!empty($origin) && $board->origin_code !== $origin) {
                        $board->origin_code = $origin;
                        $dirty = true;
                    }
                    if (!empty($foilName) && $board->foil_name !== $foilName) {
                        $board->foil_name = $foilName;
                        $dirty = true;
                    }
                    if ($dirty) {
                        $board->save();
                    }
                }

                // Resolve type
                $typeId = null;
                if ($group) {
                    $type = $types->get($group);
                    if (!$type) {
                        $type = WoodBoardType::where('name', 'like', "%{$group}%")->first();
                    }
                    $typeId = $type?->id;
                }
                if (!$typeId) {
                    if (str_ends_with($sku, '.TP.PVC.2M')) $typeId = 6;
                    elseif (str_ends_with($sku, '.TP.PVC.1M')) $typeId = 4;
                    elseif (str_ends_with($sku, '.TP.PVC')) $typeId = 5;
                    elseif (str_ends_with($sku, '.TP.2M')) $typeId = 3;
                    elseif (str_ends_with($sku, '.TP')) $typeId = 2;
                    else $typeId = 1;
                }

                // Update or create price
                $price = WoodBoardPrice::where('code', $sku)->first();
                if ($price) {
                    if ($name) $price->name = $name;
                    if ($foilName) $price->foil_name = $foilName;
                    if ($typeId && !$price->wood_board_type_id) $price->wood_board_type_id = $typeId;
                    $updatedCount++;
                } else {
                    $price = WoodBoardPrice::create([
                        'wood_board_id'      => $board->id,
                        'wood_board_type_id' => $typeId,
                        'code'               => $sku,
                        'name'               => $name,
                        'foil_name'          => $foilName ?: null,
                        'thickness'          => '1.0',
                        'price_board'        => 0,
                        'price_m2'           => 0,
                        'stock'              => 0,
                    ]);
                    $createdCount++;
                }

                if ($isMainWarehouse) {
                    $otherWhSum = (float)\App\Models\InventoryTransaction::where('warehouse_id', '!=', $warehouse->id)
                        ->where('wood_board_price_id', $price->id)
                        ->sum(DB::raw('in_qty - out_qty'));
                    $price->stock = $stock + $otherWhSum;
                    $price->save();

                    \App\Models\InventoryTransaction::where('warehouse_id', $warehouse->id)
                        ->where('wood_board_price_id', $price->id)
                        ->where('type', 'initial')
                        ->delete();
                } else {
                    $currentWhStock = (float)\App\Models\InventoryTransaction::where('warehouse_id', $warehouse->id)
                        ->where('wood_board_price_id', $price->id)
                        ->sum(DB::raw('in_qty - out_qty'));
                    $diff = $stock - $currentWhStock;

                    if ($diff != 0) {
                        \App\Models\InventoryTransaction::create([
                            'warehouse_id'        => $warehouse->id,
                            'wood_board_id'       => $price->wood_board_id,
                            'wood_board_price_id' => $price->id,
                            'item_type'           => 'board',
                            'date'                => now()->toDateString(),
                            'type'                => 'initial',
                            'voucher_code'        => 'CBK-' . strtoupper(\Illuminate\Support\Str::random(6)),
                            'in_qty'              => $diff > 0 ? $diff : 0,
                            'out_qty'             => $diff < 0 ? abs($diff) : 0,
                            'stock_after'         => $stock,
                            'unit_price'          => (float)$price->price_board,
                            'notes'               => 'Nhập tồn kho Excel - Kho ' . $warehouse->name,
                            'created_by'          => \Illuminate\Support\Facades\Auth::id(),
                        ]);
                        $price->stock = max(0, (float)$price->stock + $diff);
                        $price->save();
                    }
                }

                $totalStock += $stock;
            }

            // Ensure variants cho các mã cốt nếu không phải kho chỉ
            if (!$isEdgeBandWarehouse) {
                $allBoards = WoodBoard::with('prices')->get();
                $allTypes = WoodBoardType::all();
                foreach ($allBoards as $board) {
                    $existingCodes = $board->prices->pluck('code')->toArray();
                    foreach ($allTypes as $type) {
                        $suffix = $typeSuffixes[$type->id] ?? '';
                        $expectedCode = $board->color_code . $suffix;
                        if (!in_array($expectedCode, $existingCodes)) {
                            WoodBoardPrice::create([
                                'wood_board_id'      => $board->id,
                                'wood_board_type_id' => $type->id,
                                'code'               => $expectedCode,
                                'name'               => "Tấm {$board->color_code} - {$type->name}",
                                'foil_name'          => ($type->id == 1) ? $board->foil_name : null,
                                'thickness'          => '1.0',
                                'price_board'        => 0,
                                'price_m2'           => 0,
                                'stock'              => 0,
                            ]);
                            $createdCount++;
                        }
                    }
                    $board->current_stock = $board->prices()->sum('stock');
                    $board->save();
                }
            }
        });

        return [
            'total_items'   => $updatedCount + $createdCount,
            'updated_count' => $updatedCount,
            'created_count' => $createdCount,
            'total_stock'   => $totalStock,
        ];
    }
}
