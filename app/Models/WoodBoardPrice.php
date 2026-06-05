<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoodBoardPrice extends Model
{
    protected $table = 'wood_board_prices';

    protected $fillable = [
        'wood_board_id',
        'wood_board_type_id',
        'code',
        'name',
        'thickness',
        'price_board',
        'price_m2',
    ];

    public function board()
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function type()
    {
        return $this->belongsTo(WoodBoardType::class, 'wood_board_type_id');
    }
}
