<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PressingOrder extends Model
{
    protected $table = 'pressing_orders';

    protected $fillable = [
        'code',
        'purpose',
        'order_id',
        'wood_board_id',
        'material_name',
        'core_material',
        'thickness',
        'dimensions',
        'qty_needed',
        'qty_done',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'qty_needed' => 'integer',
        'qty_done'   => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PressingOrderItem::class, 'pressing_order_id')->orderBy('item_index');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function woodBoard(): BelongsTo
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate next pressing order code in format: LLE-YYMM-XX (or EVK-YYMM-XX)
     */
    public static function generateCode(): string
    {
        $prefix = 'LLE-' . date('ym');
        $last = self::where('code', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last && preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/', $last->code, $m)) {
            $nextSeq = intval($m[1]) + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . '-' . str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
    }
}
