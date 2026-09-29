<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'warehouse_id',
        'wood_board_id',
        'date',
        'type', // 'receipt', 'issue', 'stocktake', 'initial'
        'voucher_code',
        'reference_id',
        'reference_type',
        'in_qty',
        'out_qty',
        'stock_after',
        'unit_price',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'in_qty' => 'double',
        'out_qty' => 'double',
        'stock_after' => 'double',
        'unit_price' => 'double',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function woodBoard(): BelongsTo
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
