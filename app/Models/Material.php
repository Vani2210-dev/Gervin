<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'code',
        'name',
        'origin_code',
        'category',
        'unit',
        'cost_price',
        'selling_price',
        'min_stock',
        'max_stock',
        'current_stock',
        'initial_stock',
        'warehouse_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'cost_price' => 'double',
        'selling_price' => 'double',
        'min_stock' => 'double',
        'max_stock' => 'double',
        'current_stock' => 'double',
        'initial_stock' => 'double',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class);
    }

    public function issueItems(): HasMany
    {
        return $this->hasMany(InventoryIssueItem::class);
    }

    public function stocktakeItems(): HasMany
    {
        return $this->hasMany(InventoryStocktakeItem::class);
    }

    // Helper status badge
    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        }
        if ($this->current_stock <= $this->min_stock) {
            return 'low_stock';
        }
        return 'in_stock';
    }
}
