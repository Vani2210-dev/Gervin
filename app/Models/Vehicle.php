<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $fillable = [
        'plate_number',
        'name',
        'driver_name',
        'driver_phone',
        'fuel_type',
        'initial_km',
        'current_km',
        'oil_change_interval_km',
        'last_oil_change_km',
        'last_oil_change_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'initial_km' => 'double',
        'current_km' => 'double',
        'oil_change_interval_km' => 'double',
        'last_oil_change_km' => 'double',
        'last_oil_change_date' => 'date',
    ];

    public function fuelLogs()
    {
        return $this->hasMany(VehicleFuelLog::class)->orderBy('date', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Tự động tính toán lại toàn bộ nhật ký đổ dầu và thay dầu cho xe
     */
    public function recalculateLogs(): void
    {
        $logs = $this->fuelLogs()->get();
        if ($logs->isEmpty()) {
            return;
        }

        // Tách và tìm mốc thay dầu & công tơ mét
        $lastOilKm = $this->last_oil_change_km;
        $maxKm = $this->initial_km ?? 0;

        // Lưu danh sách các log đổ nhiên liệu để liên kết quãng đường giữa 2 lần đổ
        $fuelLogIndices = [];

        foreach ($logs as $index => $log) {
            // Tính số tiền = đơn giá * số lít
            if ($log->liters !== null && $log->unit_price !== null) {
                $log->total_price = round($log->liters * $log->unit_price);
            }

            if ($log->odometer_km !== null) {
                if ($log->odometer_km > $maxKm) {
                    $maxKm = $log->odometer_km;
                }
            }

            // Nếu đây là lần thay dầu máy
            if ($log->type === 'oil_change') {
                $lastOilKm = $log->odometer_km ?? $maxKm;
                $log->km_since_oil_change = null;
            } else {
                // Tính km kể từ lần thay dầu gần nhất
                if ($lastOilKm !== null && $log->odometer_km !== null && $log->odometer_km >= $lastOilKm) {
                    $log->km_since_oil_change = round($log->odometer_km - $lastOilKm, 1);
                } else {
                    $log->km_since_oil_change = null;
                }
            }

            if ($log->type === 'fuel' && $log->odometer_km !== null) {
                $fuelLogIndices[] = $index;
            }
        }

        // Tính quãng đường đi được và số km/lít theo phong cách bảng kê:
        // Lượng xăng đổ lần này chạy được cho đến lần đổ tiếp theo
        for ($k = 0; $k < count($fuelLogIndices); $k++) {
            $currIdx = $fuelLogIndices[$k];
            $currLog = $logs[$currIdx];

            if ($k + 1 < count($fuelLogIndices)) {
                $nextIdx = $fuelLogIndices[$k + 1];
                $nextLog = $logs[$nextIdx];

                $dist = $nextLog->odometer_km - $currLog->odometer_km;
                if ($dist >= 0) {
                    $currLog->trip_km = round($dist, 1);
                    if ($currLog->liters > 0) {
                        $currLog->km_per_liter = round($dist / $currLog->liters, 2);
                    } else {
                        $currLog->km_per_liter = null;
                    }
                } else {
                    $currLog->trip_km = null;
                    $currLog->km_per_liter = null;
                }
            } else {
                // Lần đổ mới nhất (đang chạy, chưa có lần đổ tiếp theo)
                $currLog->trip_km = null;
                $currLog->km_per_liter = null;
            }
        }

        // Lưu tất cả logs
        foreach ($logs as $log) {
            $log->saveQuietly();
        }

        // Cập nhật lại số km hiện tại và thông tin thay dầu của xe
        $this->current_km = $maxKm;
        if ($lastOilKm !== null) {
            $this->last_oil_change_km = $lastOilKm;
        }
        $this->saveQuietly();
    }
}
