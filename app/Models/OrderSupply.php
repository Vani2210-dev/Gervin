<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSupply extends Model
{
    protected $table = 'order_supplies';

    protected $fillable = [
        'order_id',
        'order_supply_code',
        'supply_name',
        'quantity',
        'inventory_action',
        'warehouse_id',
        'formula',
    ];

    protected $casts = [
        'formula' => 'array',
    ];

    public function setFormulaAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }
        $this->attributes['formula'] = !empty($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : null;
    }

    public function getFormulasAttribute()
    {
        return is_array($this->formula) ? $this->formula : (json_decode($this->formula ?? '[]', true) ?: []);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function items()
    {
        return $this->hasMany(AcrylicOrderItem::class, 'order_supply_id');
    }

    public function minLateItems()
    {
        return $this->hasMany(MinLateOrderItem::class, 'order_supply_id');
    }

    public function glassItems()
    {
        return $this->hasMany(GlassOrderItem::class, 'order_supply_id');
    }
}
