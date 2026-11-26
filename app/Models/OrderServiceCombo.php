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
            'min_late'   => 'Lệnh sản xuất (Min-late)',
            'acrylic'    => 'Acrylic',
            'Flywood'    => 'Flywood',
            'glass'      => 'Kính',
            'service'    => 'Dịch vụ & Vật tư',
            'rework'     => 'Đơn sửa tấm',
            'additional' => 'Đơn bổ sung',
            'warranty'   => 'Đơn bảo hành',
            'reuse'      => 'Đơn tận dụng',
            default      => ucfirst($this->order_type),
        };
    }

    /**
     * Safely evaluate a formula string (e.g. =D, =(E-H-I)*1.05, =F*1.05) against column totals.
     */
    public static function evaluateFormulaString(?string $formula, array $colTotals, float $defaultVal = 1.0): float
    {
        if ($formula === null || trim($formula) === '') {
            return $defaultVal;
        }

        $str = trim($formula);
        if (str_starts_with($str, '=')) {
            $str = substr($str, 1);
        }
        $str = trim($str);

        // Normalize decimal commas (1,05 -> 1.05)
        $str = preg_replace('/(\d+),(\d+)/', '$1.$2', $str);

        // Replace letter variables (A through L)
        $str = preg_replace_callback('/\b([A-Za-z])\b/', function ($matches) use ($colTotals) {
            $col = strtoupper($matches[1]);
            return isset($colTotals[$col]) ? (string)$colTotals[$col] : '0';
        }, $str);

        // Ensure safe characters: digits, +, -, *, /, (, ), ., whitespace
        if (!preg_match('/^[\d+\-*\/().\s]+$/', $str)) {
            return is_numeric($str) ? (float)$str : $defaultVal;
        }

        try {
            $result = @eval('return (' . $str . ');');
            if (is_numeric($result)) {
                return round((float)$result, 2);
            }
        } catch (\Throwable $e) {
            // fallback to default
        }

        return $defaultVal;
    }
}

