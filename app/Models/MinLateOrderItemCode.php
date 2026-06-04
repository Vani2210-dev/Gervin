<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinLateOrderItemCode extends Model
{
    protected $table = 'min_late_order_item_codes';

    protected $fillable = [
        'min_late_order_item_id',
        'product_id',
        'status',
        'assigned_worker_id',
    ];

    protected $casts = [
        'status' => 'array',
    ];

    public function minLateOrderItem()
    {
        return $this->belongsTo(MinLateOrderItem::class, 'min_late_order_item_id');
    }

    public function assignedWorker()
    {
        return $this->belongsTo(User::class, 'assigned_worker_id');
    }
}
