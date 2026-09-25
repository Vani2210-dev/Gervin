<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleFuelLog extends Model
{
    use HasFactory;

    protected $table = 'vehicle_fuel_logs';

    protected $fillable = [
        'vehicle_id',
        'date',
        'type',
        'odometer_km',
        'liters',
        'unit_price',
        'total_price',
        'trip_km',
        'km_per_liter',
        'km_since_oil_change',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'odometer_km' => 'double',
        'liters' => 'double',
        'unit_price' => 'double',
        'total_price' => 'double',
        'trip_km' => 'double',
        'km_per_liter' => 'double',
        'km_since_oil_change' => 'double',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
