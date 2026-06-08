<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CncTemplate extends Model
{
    protected $fillable = [
        'name',
        'offset_left',
        'offset_right',
        'offset_top',
        'offset_bottom',
        'mill_left',
        'mill_right',
        'mill_top',
        'mill_bottom',
        'mill_width',
        'mill_depth',
        'mill_left_2',
        'mill_right_2',
        'mill_top_2',
        'mill_bottom_2',
        'mill_width_2',
        'mill_depth_2',
    ];
}
