<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'item_type',
        'wood_board_id',
        'wood_board_price_id',
        'height',
        'width',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'height' => 'double',
        'width' => 'double',
        'quantity' => 'double',
        'unit_price' => 'double',
        'total_price' => 'double',
    ];

    public function getDimensionsAttribute(): string
    {
        if ($this->height && $this->width) {
            $h = (fmod($this->height, 1) == 0) ? number_format($this->height, 0, ',', '.') : rtrim(rtrim(number_format($this->height, 2, ',', '.'), '0'), ',');
            $w = (fmod($this->width, 1) == 0) ? number_format($this->width, 0, ',', '.') : rtrim(rtrim(number_format($this->width, 2, ',', '.'), '0'), ',');
            return "{$h} x {$w}";
        }
        return '—';
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(InventoryReceipt::class, 'receipt_id');
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
        if ($this->boardPrice) {
            return $this->boardPrice->code;
        }
        if ($this->wood_board_id) {
            $maybePrice = WoodBoardPrice::find($this->wood_board_id);
            if ($maybePrice) {
                return $maybePrice->code;
            }
        }
        return $this->woodBoard?->code ?? '-';
    }

    public function getItemNameAttribute(): string
    {
        if ($this->item_type === 'edge_band') {
            return $this->woodBoard?->edge_band_name ?? ('Chỉ dán viền ' . ($this->woodBoard?->color_code ?? ''));
        }
        if ($this->boardPrice) {
            return $this->boardPrice->name;
        }
        if ($this->wood_board_id) {
            $maybePrice = WoodBoardPrice::find($this->wood_board_id);
            if ($maybePrice) {
                return $maybePrice->name;
            }
        }
        return $this->woodBoard?->name ?? '-';
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

    public function getItemOriginCodeAttribute(): string
    {
        return $this->woodBoard?->origin_code ?? '';
    }

    /**
     * Accessor for $item->material
     * Ưu tiên WoodBoardPrice (mã SKU đầy đủ như GV125.TP.PVC.1M) thay vì rơi về mã foil của WoodBoard (GV125)
     */
    public function getMaterialAttribute()
    {
        if ($this->item_type === 'edge_band' && $this->woodBoard) {
            return (object) [
                'id'          => $this->woodBoard->id,
                'code'        => $this->woodBoard->edge_band_code ?? ('CHI-' . ($this->woodBoard->color_code ?? '')),
                'name'        => $this->woodBoard->edge_band_name ?? ('Chỉ dán viền ' . ($this->woodBoard->color_code ?? '')),
                'origin_code' => $this->woodBoard->origin_code ?? '',
                'category'    => 'Chỉ dán viền (' . ($this->woodBoard->price_group ?? 'Tiêu chuẩn') . ')',
                'unit'        => $this->woodBoard->edge_band_unit ?? 'm',
            ];
        }

        if ($this->boardPrice) {
            return (object) [
                'id'          => $this->boardPrice->id,
                'code'        => $this->boardPrice->code,
                'name'        => $this->boardPrice->name,
                'origin_code' => $this->woodBoard?->origin_code ?? '',
                'category'    => $this->woodBoard?->category ?? 'Tấm ván',
                'unit'        => $this->woodBoard?->unit ?? 'Tấm',
                'thickness'   => $this->boardPrice->thickness,
            ];
        }

        if (!$this->boardPrice && $this->wood_board_id && $this->item_type !== 'edge_band') {
            $maybePrice = WoodBoardPrice::find($this->wood_board_id);
            if ($maybePrice) {
                return (object) [
                    'id'          => $maybePrice->id,
                    'code'        => $maybePrice->code,
                    'name'        => $maybePrice->name,
                    'origin_code' => $maybePrice->board?->origin_code ?? '',
                    'category'    => $maybePrice->board?->category ?? 'Tấm ván',
                    'unit'        => $maybePrice->board?->unit ?? 'Tấm',
                    'thickness'   => $maybePrice->thickness,
                ];
            }
        }

        return $this->woodBoard;
    }
}
