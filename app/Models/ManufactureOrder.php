<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufactureOrder extends Model
{
    protected $table = 'manufacture_orders';

    protected $fillable = [
        'code',
        'status',
        'notes',
        'tech_approved_by',
        'tech_approved_at',
        'manager_approved_by',
        'manager_approved_at',
        'stamps_received_by',
        'stamps_received_at',
        'production_started_by',
        'production_started_at',
        'completed_by',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'tech_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'stamps_received_at' => 'datetime',
        'production_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'manufacture_order_order', 'manufacture_order_id', 'order_id')
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function techApprover()
    {
        return $this->belongsTo(User::class, 'tech_approved_by');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function stampsReceiver()
    {
        return $this->belongsTo(User::class, 'stamps_received_by');
    }

    public function productionStarter()
    {
        return $this->belongsTo(User::class, 'production_started_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Aggregate and normalize all items from linked orders.
     */
    public function getAllItems()
    {
        $items = collect();

        foreach ($this->orders as $order) {
            // Load items based on order type
            if ($order->type === 'min_late') {
                $order->load('supplies.minLateItems');
                foreach ($order->supplies as $supply) {
                    foreach ($supply->minLateItems as $item) {
                        $size = $item->size ?? [];
                        if (is_string($size)) {
                            $size = json_decode($size, true) ?? [];
                        }
                        $h = $size['height'] ?? '';
                        $w = $size['width'] ?? '';
                        $dims = ($h && $w) ? "{$h} x {$w}" : '';
                        
                        $items->push((object)[
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name ?? $item->name ?? '—',
                            'dimensions'   => $dims,
                            'height'       => $h,
                            'width'        => $w,
                            'quantity'     => $item->quantity,
                            'type'         => 'min_late',
                            'order_code'   => $order->order_code,
                            'notes'        => $item->notes,
                            'supply_name'  => $supply->supply_name,
                            'raw_item'     => $item,
                        ]);
                    }
                }
            } elseif ($order->type === 'glass') {
                $order->load('supplies.glassItems');
                foreach ($order->supplies as $supply) {
                    foreach ($supply->glassItems as $item) {
                        $dims = ($item->height && $item->width) ? "{$item->height} x {$item->width}" : '';
                        $items->push((object)[
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name ?? '—',
                            'dimensions'   => $dims,
                            'height'       => $item->height,
                            'width'        => $item->width,
                            'quantity'     => $item->wing_quantity ?? $item->quantity ?? 1,
                            'type'         => 'glass',
                            'order_code'   => $order->order_code,
                            'notes'        => $item->notes,
                            'supply_name'  => $supply->supply_name,
                            'raw_item'     => $item,
                        ]);
                    }
                }
            } else {
                // acrylic
                $order->load('supplies.items');
                foreach ($order->supplies as $supply) {
                    foreach ($supply->items as $item) {
                        $dims = ($item->height && $item->width) ? "{$item->height} x {$item->width}" : '';
                        $items->push((object)[
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name ?? '—',
                            'dimensions'   => $dims,
                            'height'       => $item->height,
                            'width'        => $item->width,
                            'quantity'     => $item->quantity,
                            'type'         => 'acrylic',
                            'order_code'   => $order->order_code,
                            'notes'        => $item->notes,
                            'supply_name'  => $supply->supply_name,
                            'raw_item'     => $item,
                        ]);
                    }
                }
            }
        }

        return $items;
    }
}
