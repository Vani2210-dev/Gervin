<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $table = 'payment_details';

    protected $fillable = [
        'order_id',
        'name',
        'unit',
        'length',
        'width',
        'quantity',
        'price',
        'price_only',
        'total',
        'formula',
    ];

    protected $casts = [
        'length' => 'float',
        'width' => 'float',
        'quantity' => 'float',
        'price' => 'float',
        'price_only' => 'float',
        'total' => 'float',
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

    public function getDimensionsAttribute(): string
    {
        return ($this->length && $this->width) ? "{$this->length} x {$this->width}" : '—';
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
