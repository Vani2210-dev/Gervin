<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'code',
        'name',
        'warehouse_code',
        'warehouse_id',
        'category',
        'unit',
        'item_type',
        'origin',
        'cost_price',
        'min_stock',
        'initial_stock',
        'current_stock',
        'wood_board_id',
        'wood_board_price_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'cost_price'    => 'double',
        'min_stock'     => 'double',
        'initial_stock' => 'double',
        'current_stock' => 'double',
    ];

    public function getOriginCodeAttribute(): string
    {
        return (string)($this->origin ?? '');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function woodBoard(): BelongsTo
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function boardPrice(): BelongsTo
    {
        return $this->belongsTo(WoodBoardPrice::class, 'wood_board_price_id');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class, 'material_id');
    }

    public function issueItems(): HasMany
    {
        return $this->hasMany(InventoryIssueItem::class, 'material_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'material_id')->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function isBoard(): bool
    {
        return $this->item_type === 'board' || mb_strtolower($this->unit) === 'tấm';
    }

    public function isEdgeBand(): bool
    {
        return $this->item_type === 'edge_band' || str_starts_with($this->code, 'CHI-');
    }

    public function isHardware(): bool
    {
        return $this->item_type === 'hardware' || in_array($this->warehouse_code, ['153_PK', '153_PHU']);
    }

    public function isChemical(): bool
    {
        return $this->item_type === 'chemical' || in_array($this->warehouse_code, ['152_HÓA CHẤT', '152_PHU']);
    }
}
