<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStocktakeItem extends Model
{
    protected $fillable = [
        'stocktake_id',
        'item_type',
        'wood_board_id',
        'wood_board_price_id',
        'book_quantity',
        'actual_quantity',
        'difference',
        'reason',
    ];

    protected $casts = [
        'book_quantity' => 'double',
        'actual_quantity' => 'double',
        'difference' => 'double',
    ];

    public function stocktake(): BelongsTo
    {
        return $this->belongsTo(InventoryStocktake::class, 'stocktake_id');
    }

    public function woodBoard(): BelongsTo
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function boardPrice(): BelongsTo
    {
        return $this->belongsTo(WoodBoardPrice::class, 'wood_board_price_id');
    }

    /**
     * Backward-compatible alias for existing views
     */
     public function material(): BelongsTo
     {
         return $this->woodBoard();
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
