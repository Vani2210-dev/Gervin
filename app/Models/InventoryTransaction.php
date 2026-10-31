<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\InventoryReceipt;
use App\Models\InventoryIssue;
use App\Models\InventoryStocktake;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'warehouse_id',
        'item_type',
        'wood_board_id',
        'wood_board_price_id',
        'date',
        'type', // 'receipt', 'issue', 'stocktake', 'initial'
        'voucher_code',
        'reference_id',
        'reference_type',
        'in_qty',
        'out_qty',
        'stock_after',
        'unit_price',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'in_qty' => 'double',
        'out_qty' => 'double',
        'stock_after' => 'double',
        'unit_price' => 'double',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function woodBoard(): BelongsTo
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function boardPrice(): BelongsTo
    {
        return $this->belongsTo(WoodBoardPrice::class, 'wood_board_price_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getItemSkuAttribute(): string
    {
        if ($this->item_type === 'edge_band') {
            return $this->woodBoard?->edge_band_code ?? ('CHI-' . ($this->woodBoard?->color_code ?? ''));
        }
        return $this->boardPrice?->code ?? $this->woodBoard?->code ?? '-';
    }

    public function getItemNameAttribute(): string
    {
        if ($this->item_type === 'edge_band') {
            return $this->woodBoard?->edge_band_name ?? ('Chỉ dán viền ' . ($this->woodBoard?->color_code ?? ''));
        }
        return $this->boardPrice?->name ?? $this->woodBoard?->name ?? '-';
    }

    public function getItemUnitAttribute(): string
    {
        if ($this->item_type === 'edge_band') {
            return $this->woodBoard?->edge_band_unit ?? 'm';
        }
        return $this->woodBoard?->unit ?? 'Tấm';
    }

    public function getItemCategoryAttribute(): string
    {
        if ($this->item_type === 'edge_band') {
            return 'Chỉ dán viền (' . ($this->woodBoard?->price_group ?? 'Tiêu chuẩn') . ')';
        }
        return $this->woodBoard?->category ?? 'Tấm ván';
    }

    public function getEditUrlAttribute(): ?string
    {
        // 1. Phiếu Nhập Kho (PNK)
        if ($this->type === 'receipt' || $this->reference_type === InventoryReceipt::class || str_starts_with((string)$this->voucher_code, 'PNK')) {
            $receiptId = $this->reference_id;
            if (!$receiptId || $this->reference_type !== InventoryReceipt::class) {
                $receipt = InventoryReceipt::where('code', $this->voucher_code)->first(['id']);
                $receiptId = $receipt?->id;
            }
            if ($receiptId) {
                return route('inventory.receipts.edit', $receiptId);
            }
        }

        // 2. Phiếu Xuất Kho (PXK)
        if ($this->type === 'issue' || $this->reference_type === InventoryIssue::class || str_starts_with((string)$this->voucher_code, 'PXK')) {
            $issueId = $this->reference_id;
            if (!$issueId || $this->reference_type !== InventoryIssue::class) {
                $issue = InventoryIssue::where('code', $this->voucher_code)->first(['id']);
                $issueId = $issue?->id;
            }
            if ($issueId) {
                return route('inventory.issues.edit', $issueId);
            }
        }

        // 3. Phiếu Kiểm Kê (PKK)
        if ($this->type === 'stocktake' || $this->reference_type === InventoryStocktake::class || str_starts_with((string)$this->voucher_code, 'PKK')) {
            $stocktakeId = $this->reference_id;
            if (!$stocktakeId || $this->reference_type !== InventoryStocktake::class) {
                $stocktake = InventoryStocktake::where('code', $this->voucher_code)->first(['id']);
                $stocktakeId = $stocktake?->id;
            }
            if ($stocktakeId) {
                return route('inventory.stocktakes.show', $stocktakeId);
            }
        }

        return null;
    }
}
