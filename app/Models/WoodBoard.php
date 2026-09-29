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
        'origin_code',
        'unit',
        'cost_price',
        'min_stock',
        'initial_stock',
        'current_stock',
        'warehouse_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'cost_price'    => 'double',
        'min_stock'     => 'double',
        'initial_stock' => 'double',
        'current_stock' => 'double',
    ];

    protected $appends = [
        'code',
        'name',
        'category',
    ];

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
