<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'material_id',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'double',
        'unit_price' => 'double',
        'total_price' => 'double',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(InventoryReceipt::class, 'receipt_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
