<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStocktakeItem extends Model
{
    protected $fillable = [
        'stocktake_id',
        'material_id',
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

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
