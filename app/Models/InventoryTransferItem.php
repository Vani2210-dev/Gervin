<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransferItem extends Model
{
    protected $fillable = [
        'transfer_id',
        'from_material_id',
        'to_material_id',
        'wood_board_id',
        'wood_board_price_id',
        'item_type',
        'code',
        'name',
        'unit',
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

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class, 'transfer_id');
    }

    public function fromMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'from_material_id');
    }

    public function toMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'to_material_id');
    }

    public function woodBoard(): BelongsTo
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function boardPrice(): BelongsTo
    {
        return $this->belongsTo(WoodBoardPrice::class, 'wood_board_price_id');
    }
}
