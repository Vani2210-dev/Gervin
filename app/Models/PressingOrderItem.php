<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PressingOrderItem extends Model
{
    protected $table = 'pressing_order_items';

    protected $fillable = [
        'pressing_order_id',
        'piece_code',
        'item_index',
        'status',
        'status_logs',
        'scanned_at',
    ];

    protected $casts = [
        'item_index'  => 'integer',
        'status_logs' => 'array',
        'scanned_at'  => 'datetime',
    ];

    public function pressingOrder(): BelongsTo
    {
        return $this->belongsTo(PressingOrder::class, 'pressing_order_id');
    }
}
