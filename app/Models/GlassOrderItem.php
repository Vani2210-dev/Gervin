<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlassOrderItem extends Model
{
    protected $table = 'glass_order_items';

    protected $appends = ['product_code'];

    protected $fillable = [
        'order_supply_id',
        'product_name',
        'thickness',
        'wing_opening_direction',
        'aluminum_color',
        'glass_color',
        'height',
        'width',
        'unit',
        'wing_quantity',
        'area_m2',
        'unit_price',
        'total_price',
        'notes',
        'old_size',
        'formula',
    ];

    protected $casts = [
        'height' => 'float',
        'width' => 'float',
        'area_m2' => 'float',
        'unit_price' => 'float',
        'total_price' => 'float',
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

    public function orderSupply()
    {
        return $this->belongsTo(OrderSupply::class, 'order_supply_id');
    }

    public function codes()
    {
        return $this->hasMany(GlassOrderItemCode::class, 'glass_order_item_id');
    }

    public function getProductCodeAttribute()
    {
        $firstCode = $this->codes()->first();
        if ($firstCode) {
            $pid = $firstCode->product_id;
            $pos = strrpos($pid, '.');
            if ($pos !== false) {
                return substr($pid, 0, $pos);
            }
            return $pid;
        }
        return '—';
    }
}
