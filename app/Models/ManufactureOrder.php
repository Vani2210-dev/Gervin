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

    public function stampDistributions()
    {
        return $this->hasMany(ManufactureStampDistribution::class, 'manufacture_order_id');
    }

    /**
     * Aggregate and normalize all items from linked orders.
     */
    public function getAllItems()
    {
        $items = collect();

        foreach ($this->orders as $order) {
            // Load items based on order type
            if (in_array($order->type, ['min_late', 'plywood'])) {
                $order->load('supplies.minLateItems.codes');
                foreach ($order->supplies as $supply) {
                    foreach ($supply->minLateItems as $item) {
                        $size = $item->size ?? [];
                        if (is_string($size)) {
                            $size = json_decode($size, true) ?? [];
                        }
                        $h = $size['height'] ?? '';
                        $w = $size['width'] ?? '';
                        $dims = ($h && $w) ? "{$h} x {$w}" : '';
                        
                        foreach ($item->codes as $code) {
                            $items->push((object)[
                                'id'                 => $code->id,
                                'product_code'       => $code->product_id,
                                'product_name'       => $item->product_name ?? $item->name ?? '—',
                                'dimensions'         => $dims,
                                'height'             => $h,
                                'width'              => $w,
                                'quantity'           => 1,
                                'type'               => $order->type,
                                'order_code'         => $order->order_code,
                                'notes'              => $item->notes,
                                'supply_name'        => $supply->supply_name,
                                'status'             => $code->status,
                                'raw_item'           => $item,
                            ]);
                        }
                    }
                }
            } elseif ($order->type === 'glass') {
                $order->load('supplies.glassItems.codes');
                foreach ($order->supplies as $supply) {
                    foreach ($supply->glassItems as $item) {
                        $dims = ($item->height && $item->width) ? "{$item->height} x {$item->width}" : '';
                        foreach ($item->codes as $code) {
                            $items->push((object)[
                                'id'                 => $code->id,
                                'product_code'       => $code->product_id,
                                'product_name'       => $item->product_name ?? '—',
                                'dimensions'         => $dims,
                                'height'             => $item->height,
                                'width'              => $item->width,
                                'quantity'           => 1,
                                'type'               => 'glass',
                                'order_code'         => $order->order_code,
                                'notes'              => $item->notes,
                                'supply_name'        => $supply->supply_name,
                                'status'             => $code->status,
                                'raw_item'           => $item,
                            ]);
                        }
                    }
                }
            } else {
                // acrylic
                $order->load('supplies.items.codes');
                foreach ($order->supplies as $supply) {
                    foreach ($supply->items as $item) {
                        $dims = ($item->height && $item->width) ? "{$item->height} x {$item->width}" : '';
                        foreach ($item->codes as $code) {
                            $items->push((object)[
                                'id'                 => $code->id,
                                'product_code'       => $code->product_id,
                                'product_name'       => $item->product_name ?? '—',
                                'dimensions'         => $dims,
                                'height'             => $item->height,
                                'width'              => $item->width,
                                'quantity'           => 1,
                                'type'               => 'acrylic',
                                'order_code'         => $order->order_code,
                                'notes'              => $item->notes,
                                'supply_name'        => $supply->supply_name,
                                'status'             => $code->status,
                                'raw_item'           => $item,
                            ]);
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Tự động sinh mã Lệnh sản xuất tiếp theo theo định dạng LSX-Ymd-xxxx
     */
    public static function generateNextCode(): string
    {
        $today = date('Ymd');
        $lastMO = self::where('code', 'like', "LSX-{$today}-%")->orderBy('id', 'desc')->first();
        $nextNumber = 1;
        if ($lastMO) {
            $parts = explode('-', $lastMO->code);
            $nextNumber = intval(end($parts)) + 1;
        }
        return "LSX-{$today}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tính toán tiến độ gia công thực tế theo lượt quét QR của các tấm
     */
    public function getProgress(): array
    {
        $total = 0;
        $completed = 0;
        $inProgress = 0;

        foreach ($this->orders as $order) {
            $supplies = $order->relationLoaded('supplies') ? $order->supplies : $order->supplies()->with(['items.codes', 'minLateItems.codes', 'glassItems.codes'])->get();

            foreach ($supplies as $supply) {
                if ($order->type === 'glass') {
                    $items = $supply->relationLoaded('glassItems') ? $supply->glassItems : $supply->glassItems()->with('codes')->get();
                } elseif (in_array($order->type, ['min_late', 'plywood'])) {
                    $items = $supply->relationLoaded('minLateItems') ? $supply->minLateItems : $supply->minLateItems()->with('codes')->get();
                } else {
                    $items = $supply->relationLoaded('items') ? $supply->items : $supply->items()->with('codes')->get();
                }

                foreach ($items as $item) {
                    $codes = $item->relationLoaded('codes') ? $item->codes : $item->codes;
                    foreach ($codes as $code) {
                        $total++;
                        $statusLogs = $code->status ?? [];
                        if (is_string($statusLogs)) {
                            $statusLogs = json_decode($statusLogs, true) ?? [];
                        }

                        $isDone = false;
                        $hasStarted = false;
                        foreach ($statusLogs as $log) {
                            $action = mb_strtolower($log['action'] ?? '');
                            if ($action === 'hoàn thành qc' || $action === 'hoàn thành làm đẹp') {
                                $isDone = true;
                            }
                            if (
                                str_contains($action, 'ép') ||
                                str_contains($action, 'cnc') ||
                                str_contains($action, 'dán cạnh') ||
                                str_contains($action, 'làm đẹp') ||
                                str_contains($action, 'lỗi')
                            ) {
                                $hasStarted = true;
                            }
                        }

                        if ($isDone) {
                            $completed++;
                        } elseif ($hasStarted) {
                            $inProgress++;
                        }
                    }
                }
            }
        }

        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

        return [
            'total'            => $total,
            'completed'        => $completed,
            'in_progress'      => $inProgress,
            'remaining'        => max(0, $total - $completed),
            'percentage'       => $percentage,
            'is_all_completed' => $total > 0 && $completed === $total,
        ];
    }
}
