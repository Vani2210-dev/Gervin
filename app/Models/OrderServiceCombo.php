<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderServiceCombo extends Model
{
    use HasFactory;

    protected $table = 'order_service_combos';

    protected $fillable = [
        'order_type',
        'name',
        'description',
        'is_default',
        'sort_order',
        'items',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'items'      => 'array',
    ];

    /**
     * Get label for order_type
     */
    public function getOrderTypeLabelAttribute(): string
    {
        return match($this->order_type) {
            'Flywood'  => 'Flywood',
            'min_late' => 'Lệnh sản xuất (Min-late)',
            'acrylic'  => 'Acrylic',
            'glass'    => 'Kính',
            'service'  => 'Dịch vụ & Vật tư',
            default    => ucfirst($this->order_type),
        };
    }
}

