<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlassOrderItemCode extends Model
{
    protected $table = 'glass_order_item_codes';

    protected $fillable = [
        'glass_order_item_id',
        'product_id',
        'status',
        'assigned_worker_id',
    ];

    protected $casts = [
        'status' => 'array',
    ];

    public function glassOrderItem()
    {
        return $this->belongsTo(GlassOrderItem::class, 'glass_order_item_id');
    }

    public function assignedWorker()
    {
        return $this->belongsTo(User::class, 'assigned_worker_id');
    }
}
