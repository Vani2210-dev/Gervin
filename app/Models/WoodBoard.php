<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoodBoard extends Model
{
    protected $table = 'wood_boards';

    protected $fillable = [
        'price_group',
        'color_code',
    ];

    public function prices()
    {
        return $this->hasMany(WoodBoardPrice::class, 'wood_board_id');
    }

    public function material()
    {
        return $this->hasOne(Material::class, 'wood_board_id');
    }

    public function getCurrentStockAttribute(): float
    {
        if ($this->relationLoaded('material') && $this->material) {
            return (float)$this->material->current_stock;
        }

        $mat = Material::where('wood_board_id', $this->id)
            ->orWhere('code', $this->color_code)
            ->first();

        return $mat ? (float)$mat->current_stock : 0;
    }

    protected static function booted()
    {
        static::created(function (WoodBoard $board) {
            static::syncWithMaterial($board);
        });

        static::updated(function (WoodBoard $board) {
            static::syncWithMaterial($board);
        });

        static::deleting(function (WoodBoard $board) {
            $material = Material::where('wood_board_id', $board->id)->first();
            if ($material) {
                // If material has transactions, keep history but set inactive
                if ($material->transactions()->exists()) {
                    $material->update(['status' => 'inactive', 'wood_board_id' => null]);
                } else {
                    $material->delete();
                }
            }
        });
    }

    public static function syncWithMaterial(WoodBoard $board): Material
    {
        $defaultWarehouse = Warehouse::first();
        if (!$defaultWarehouse) {
            $defaultWarehouse = Warehouse::create(['name' => 'Kho Chính', 'code' => 'KHO-CHINH']);
        }

        // Check if material already exists by wood_board_id or matching code/color_code
        $material = Material::where('wood_board_id', $board->id)
            ->orWhere('code', $board->color_code)
            ->first();

        if ($material) {
            $material->update([
                'wood_board_id' => $board->id,
                'code'          => $board->color_code,
                'category'      => $material->category ?: ($board->price_group ?: 'Tấm cốt gỗ / Acrylic'),
            ]);
        } else {
            $material = Material::create([
                'wood_board_id' => $board->id,
                'code'          => $board->color_code,
                'name'          => 'Tấm ' . $board->color_code,
                'category'      => $board->price_group ?: 'Tấm cốt gỗ / Acrylic',
                'unit'          => 'Tấm',
                'cost_price'    => 0,
                'min_stock'     => 50,
                'current_stock' => 0,
                'warehouse_id'  => $defaultWarehouse->id,
                'status'        => 'active',
            ]);
        }

        return $material;
    }
}
