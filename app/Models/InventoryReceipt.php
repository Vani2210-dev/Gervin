<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryReceipt extends Model
{
    protected $fillable = [
        'code',
        'date',
        'warehouse_id',
        'supplier_name',
        'deliverer',
        'receiver',
        'total_quantity',
        'total_amount',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_quantity' => 'double',
        'total_amount' => 'double',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class, 'receipt_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
