<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueTarget extends Model
{
    use HasFactory;

    // Tên bảng trong cơ sở dữ liệu
    protected $table = 'revenue_targets';

    protected $fillable = [
        'year',
        'month',
        'target_amount',
        'target_day_amount',
        'lost_months',
        'returning_months',
    ];
}
