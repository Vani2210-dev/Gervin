<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WoodBoard;
use App\Models\InventoryReceipt;
use App\Models\InventoryReceiptItem;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueItem;
use App\Models\InventoryStocktake;
use App\Models\InventoryStocktakeItem;
use App\Models\InventoryTransaction;
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

                    $bookQty = (float)($item['book_quantity'] ?? $board->edge_band_current_stock);
                    $actualQty = (float)($item['actual_quantity'] ?? $bookQty);
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

                    $bookQty = (float)($item['book_quantity'] ?? ($boardPrice?->stock ?? 0));
                    $actualQty = (float)($item['actual_quantity'] ?? $bookQty);
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
}
