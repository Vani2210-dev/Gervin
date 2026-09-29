<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryStocktake extends Model
{
    protected $fillable = [
        'code',
        'date',
        'warehouse_id',
        'creator_name',
        'total_book_quantity',
        'total_actual_quantity',
        'total_difference',
        'notes',
        'status',
        'balanced_at',
        'balanced_by',
    ];

    protected $casts = [
        'date' => 'date',
        'balanced_at' => 'datetime',
        'total_book_quantity' => 'double',
        'total_actual_quantity' => 'double',
        'total_difference' => 'double',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryStocktakeItem::class, 'stocktake_id');
    }

    public function balancedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'balanced_by');
    }
}
