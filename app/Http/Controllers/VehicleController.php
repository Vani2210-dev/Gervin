<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleFuelLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Danh sách xe và tổng hợp chi phí/nhiên liệu
     */
    public function index(Request $request)
    {
        $query = Vehicle::query()->with('fuelLogs');

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('plate_number', 'like', "%{$kw}%")
                  ->orWhere('name', 'like', "%{$kw}%")
                  ->orWhere('driver_name', 'like', "%{$kw}%")
                  ->orWhere('driver_phone', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        $vehicles = $query->latest('id')->get();

        // Tính các chỉ số cho từng xe
        foreach ($vehicles as $vehicle) {
            $fuelOnly = $vehicle->fuelLogs->where('type', 'fuel');
            $vehicle->total_liters = $fuelOnly->sum('liters');
            $vehicle->total_fuel_cost = $fuelOnly->sum('total_price');

            // Tính km kể từ lần thay dầu gần nhất
            $oilChangeLogs = $vehicle->fuelLogs->where('type', 'oil_change')->sortByDesc('date');
            $lastOilLog = $oilChangeLogs->first();
            $lastOilKm = $lastOilLog ? $lastOilLog->odometer_km : ($vehicle->last_oil_change_km ?? $vehicle->initial_km ?? 0);
            
            $vehicle->current_km_since_oil = max(0, $vehicle->current_km - $lastOilKm);
            $vehicle->oil_interval = $vehicle->oil_change_interval_km ?: 5000;
            $vehicle->oil_remaining = max(0, $vehicle->oil_interval - $vehicle->current_km_since_oil);
            $vehicle->oil_percent = min(100, round(($vehicle->current_km_since_oil / $vehicle->oil_interval) * 100));

            // Hiệu suất trung bình km/lít
            $logsWithTrip = $fuelOnly->whereNotNull('trip_km')->where('trip_km', '>', 0);
            $sumTrip = $logsWithTrip->sum('trip_km');
            $sumLiters = $logsWithTrip->sum('liters');
            $vehicle->avg_km_per_liter = $sumLiters > 0 ? round($sumTrip / $sumLiters, 2) : 0;
        }

        // Thống kê tổng hợp toàn đội xe
        $totalVehicles = $vehicles->count();
        $activeVehicles = $vehicles->where('status', 'active')->count();
        $needOilChangeCount = $vehicles->filter(fn($v) => $v->current_km_since_oil >= ($v->oil_interval - 500))->count();
        $totalAllCost = $vehicles->sum('total_fuel_cost');
        $totalAllLiters = $vehicles->sum('total_liters');

        return view('vehicles.index', compact(
            'vehicles',
            'totalVehicles',
            'activeVehicles',
            'needOilChangeCount',
            'totalAllCost',
            'totalAllLiters'
        ));
    }

    /**
     * Thêm xe mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number',
            'name' => 'required|string|max:191',
            'driver_name' => 'nullable|string|max:191',
            'driver_phone' => 'nullable|string|max:50',
            'fuel_type' => 'required|string|max:50',
            'initial_km' => 'nullable|numeric|min:0',
            'oil_change_interval_km' => 'nullable|numeric|min:1000',
            'status' => 'required|string|in:active,maintenance,inactive',
            'notes' => 'nullable|string',
        ]);

        $initialKm = $request->initial_km ? (float)$request->initial_km : 0;

        $vehicle = Vehicle::create([
            'plate_number' => mb_strtoupper(trim($request->plate_number)),
            'name' => trim($request->name),
            'driver_name' => $request->driver_name ? trim($request->driver_name) : null,
            'driver_phone' => $request->driver_phone ? trim($request->driver_phone) : null,
            'fuel_type' => $request->fuel_type,
            'initial_km' => $initialKm,
            'current_km' => $initialKm,
            'oil_change_interval_km' => $request->oil_change_interval_km ?: 5000,
            'last_oil_change_km' => $initialKm,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('vehicles.show', $vehicle->id)->with('success', "Đã thêm phương tiện [{$vehicle->plate_number}] thành công!");
    }

    /**
     * Chi tiết xe & Bảng nhật ký đổ dầu / thay dầu
     */
    public function show(Vehicle $vehicle, Request $request)
    {
        $query = $vehicle->fuelLogs()->orderBy('date', 'asc')->orderBy('id', 'asc');

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }
        if ($request->filled('log_type')) {
            $query->where('type', $request->log_type);
        }

        $logs = $query->get();

        // Tính các chỉ số tổng hợp cho xe này
        $allLogs = $vehicle->fuelLogs;
        $fuelLogs = $allLogs->where('type', 'fuel');
        $totalLiters = $fuelLogs->sum('liters');
        $totalExpense = $fuelLogs->sum('total_price');

        $oilChangeLogs = $allLogs->where('type', 'oil_change')->sortByDesc('date');
        $lastOilLog = $oilChangeLogs->first();
        $lastOilKm = $lastOilLog ? $lastOilLog->odometer_km : ($vehicle->last_oil_change_km ?? $vehicle->initial_km ?? 0);
        $lastOilDate = $lastOilLog ? $lastOilLog->date : $vehicle->last_oil_change_date;

        $kmSinceOil = max(0, $vehicle->current_km - $lastOilKm);
        $oilInterval = $vehicle->oil_change_interval_km ?: 5000;
        $kmUntilNextOil = max(0, $oilInterval - $kmSinceOil);
        $oilPercent = min(100, round(($kmSinceOil / $oilInterval) * 100));

        // Quãng đường đã tính toán hiệu suất
        $validTripLogs = $fuelLogs->whereNotNull('trip_km')->where('trip_km', '>', 0);
        $totalDistanceLogged = $validTripLogs->sum('trip_km');
        $totalLitersForTrip = $validTripLogs->sum('liters');
        $avgKmPerLiter = $totalLitersForTrip > 0 ? round($totalDistanceLogged / $totalLitersForTrip, 2) : 0;

        return view('vehicles.show', compact(
            'vehicle',
            'logs',
            'totalLiters',
            'totalExpense',
            'lastOilKm',
            'lastOilDate',
            'kmSinceOil',
            'oilInterval',
            'kmUntilNextOil',
            'oilPercent',
            'avgKmPerLiter'
        ));
    }

    /**
     * Cập nhật thông tin xe
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number,' . $vehicle->id,
            'name' => 'required|string|max:191',
            'driver_name' => 'nullable|string|max:191',
            'driver_phone' => 'nullable|string|max:50',
            'fuel_type' => 'required|string|max:50',
            'initial_km' => 'nullable|numeric|min:0',
            'oil_change_interval_km' => 'nullable|numeric|min:1000',
            'status' => 'required|string|in:active,maintenance,inactive',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update([
            'plate_number' => mb_strtoupper(trim($request->plate_number)),
            'name' => trim($request->name),
            'driver_name' => $request->driver_name ? trim($request->driver_name) : null,
            'driver_phone' => $request->driver_phone ? trim($request->driver_phone) : null,
            'fuel_type' => $request->fuel_type,
            'initial_km' => $request->initial_km ? (float)$request->initial_km : 0,
            'oil_change_interval_km' => $request->oil_change_interval_km ?: 5000,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        $vehicle->recalculateLogs();

        return redirect()->back()->with('success', "Cập nhật thông tin xe [{$vehicle->plate_number}] thành công!");
    }

    /**
     * Xóa xe
     */
    public function destroy(Vehicle $vehicle)
    {
        $plate = $vehicle->plate_number;
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', "Đã xóa phương tiện [{$plate}] thành công!");
    }

    /**
     * Thêm nhật ký đổ dầu / thay dầu
     */
    public function storeFuelLog(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:fuel,oil_change,maintenance',
            'odometer_km' => 'nullable|numeric|min:0',
            'liters' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $odometer = $request->odometer_km !== null ? (float)$request->odometer_km : null;
        $liters = $request->liters !== null ? (float)$request->liters : null;
        $unitPrice = $request->unit_price !== null ? (float)$request->unit_price : null;
        $totalPrice = ($liters !== null && $unitPrice !== null) ? round($liters * $unitPrice) : null;

        VehicleFuelLog::create([
            'vehicle_id' => $vehicle->id,
            'date' => $request->date,
            'type' => $request->type,
            'odometer_km' => $odometer,
            'liters' => $liters,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'notes' => $request->notes,
        ]);

        $vehicle->recalculateLogs();

        $actionText = match ($request->type) {
            'oil_change' => 'Ghi nhận Thay dầu máy',
            'maintenance' => 'Ghi nhận Bảo dưỡng',
            default => 'Thêm nhật ký đổ xăng/dầu'
        };

        return redirect()->back()->with('success', "{$actionText} thành công!");
    }

    /**
     * Cập nhật nhật ký đổ dầu / thay dầu
     */
    public function updateFuelLog(Request $request, Vehicle $vehicle, VehicleFuelLog $fuelLog)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:fuel,oil_change,maintenance',
            'odometer_km' => 'nullable|numeric|min:0',
            'liters' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $odometer = $request->odometer_km !== null ? (float)$request->odometer_km : null;
        $liters = $request->liters !== null ? (float)$request->liters : null;
        $unitPrice = $request->unit_price !== null ? (float)$request->unit_price : null;
        $totalPrice = ($liters !== null && $unitPrice !== null) ? round($liters * $unitPrice) : null;

        $fuelLog->update([
            'date' => $request->date,
            'type' => $request->type,
            'odometer_km' => $odometer,
            'liters' => $liters,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'notes' => $request->notes,
        ]);

        $vehicle->recalculateLogs();

        return redirect()->back()->with('success', "Cập nhật bản ghi ngày {$request->date} thành công!");
    }

    /**
     * Xóa nhật ký đổ dầu / thay dầu
     */
    public function destroyFuelLog(Vehicle $vehicle, VehicleFuelLog $fuelLog)
    {
        $fuelLog->delete();
        $vehicle->recalculateLogs();

        return redirect()->back()->with('success', "Đã xóa bản ghi nhật ký thành công!");
    }
}
