<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStocktakeItem extends Model
{
    protected $fillable = [
        'stocktake_id',
        'wood_board_id',
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

    /**
     * Backward-compatible alias for existing views
     */
    public function material(): BelongsTo
    {
        return $this->woodBoard();
    }
}
