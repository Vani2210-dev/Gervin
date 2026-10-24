<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WoodBoard extends Model
{
    protected $table = 'wood_boards';

    protected $fillable = [
        'price_group',
        'color_code',
        'foil_name',
        'origin_code',
        'unit',
        'cost_price',
        'min_stock',
        'initial_stock',
        'current_stock',
        'warehouse_id',
        'status',
        'notes',
        'edge_band_unit',
        'edge_band_current_stock',
        'edge_band_min_stock',
        'edge_band_cost_price',
        'edge_band_initial_stock',
    ];

    protected $casts = [
        'cost_price'              => 'double',
        'min_stock'               => 'double',
        'initial_stock'           => 'double',
        'current_stock'           => 'double',
        'edge_band_current_stock' => 'double',
        'edge_band_min_stock'     => 'double',
        'edge_band_cost_price'    => 'double',
        'edge_band_initial_stock' => 'double',
    ];

    protected $appends = [
        'code',
        'name',
        'category',
        'total_stock',
        'edge_band_code',
        'edge_band_name',
    ];

    public function getEdgeBandCodeAttribute(): string
    {
        return 'CHI-' . (string)($this->color_code ?? '');
    }

    public function getEdgeBandNameAttribute(): string
    {
        return 'Chỉ dán viền ' . (string)($this->color_code ?? '');
    }

    public function getTotalStockAttribute(): float
    {
        $pricesSum = $this->relationLoaded('prices') ? $this->prices->sum('stock') : $this->prices()->sum('stock');
        return $pricesSum > 0 ? (float)$pricesSum : (float)($this->attributes['current_stock'] ?? 0);
    }

    public function getCodeAttribute(): string
    {
        return (string)($this->color_code ?? '');
    }

    public function getNameAttribute(): string
    {
        return 'Tấm ' . ($this->color_code ?? '');
    }

    public function getCategoryAttribute(): string
    {
        return (string)($this->price_group ?: 'Tấm cốt gỗ / Acrylic');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(WoodBoardPrice::class, 'wood_board_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'wood_board_id')->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class, 'wood_board_id');
    }

    public function issueItems(): HasMany
    {
        return $this->hasMany(InventoryIssueItem::class, 'wood_board_id');
    }

    public function stocktakeItems(): HasMany
    {
        return $this->hasMany(InventoryStocktakeItem::class, 'wood_board_id');
    }
}
