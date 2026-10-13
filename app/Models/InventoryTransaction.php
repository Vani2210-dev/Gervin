<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
