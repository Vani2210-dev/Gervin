<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QrDevice;
use App\Models\QrScanLog;
use App\Models\User;
use App\Services\CNCService;
use App\Services\PressingService;
use App\Services\EdgeBandingService;
use App\Services\FinishingService;
use App\Services\QCService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class QrScanController extends Controller
{
    protected $cncService;
    protected $pressingService;
    protected $edgeBandingService;
    protected $finishingService;
    protected $qcService;

    public function __construct(
        CNCService $cncService,
        PressingService $pressingService,
        EdgeBandingService $edgeBandingService,
        FinishingService $finishingService,
        QCService $qcService
    ) {
        // Chỉ áp dụng middleware auth cho các routes quản trị
        $this->middleware('auth')->except('receiveScan');

        $this->cncService = $cncService;
        $this->pressingService = $pressingService;
        $this->edgeBandingService = $edgeBandingService;
        $this->finishingService = $finishingService;
        $this->qcService = $qcService;
    }

    /**
     * Endpoint POST /scan nhận dữ liệu quét từ thiết bị phần cứng ESP32.
     */
    public function receiveScan(Request $request)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empty payload'
            ], 400);
        }

        // Chuẩn hóa payload thành mảng nếu thiết bị chỉ gửi 1 đối tượng đơn lẻ
        if (isset($payload['device_id'])) {
            $payload = [$payload];
        }

        $receivedCount = 0;

        foreach ($payload as $packet) {
            $deviceId = $packet['device_id'] ?? null;
            $timeRaw = $packet['time'] ?? null;
            $barcode = $packet['result'] ?? null;

            if (!$deviceId || !$barcode) {
                continue;
            }

            $receivedCount++;

            // Chuẩn hóa định dạng thời gian
            $scannedAt = null;
            if ($timeRaw) {
                try {
                    $scannedAt = Carbon::createFromFormat('d-m-Y H:i:s.u', $timeRaw);
                } catch (\Exception $e) {
                    try {
                        $scannedAt = Carbon::createFromFormat('d-m-Y H:i:s', explode('.', $timeRaw)[0]);
                    } catch (\Exception $ex) {
                        $scannedAt = now();
                    }
                }
            } else {
                $timeRaw = now()->format('d-m-Y H:i:s.000');
                $scannedAt = now();
            }

            // Chống trùng lặp (Idempotent): nếu có log trùng (device_id, scanned_at_raw, barcode) thì bỏ qua xử lý nghiệp vụ
            $existingLog = QrScanLog::where('device_id', $deviceId)
                ->where('scanned_at_raw', $timeRaw)
                ->where('barcode', $barcode)
                ->first();

            if ($existingLog) {
                continue;
            }

            // Tìm hoặc tự động đăng ký thiết bị mới
            $device = QrDevice::find($deviceId);
            if (!$device) {
                $device = QrDevice::create([
                    'id'               => $deviceId,
                    'name'             => 'Mắt đọc ' . $deviceId,
                    'process_step'     => null,
                    'action_type'      => null,
                    'operator_user_id' => null,
                    'is_active'        => false,
                    'notes'            => 'Thiết bị tự động đăng ký từ tín hiệu gửi về.',
                ]);
            }

            // Nếu thiết bị chưa được kích hoạt
            if (!$device->is_active) {
                QrScanLog::create([
                    'device_id'      => $deviceId,
                    'barcode'        => $barcode,
                    'scanned_at'     => $scannedAt,
                    'scanned_at_raw' => $timeRaw,
                    'status'         => 'unmapped',
                    'message'        => 'Thiết bị chưa được kích hoạt hoạt động.',
                    'payload'        => $packet,
                ]);
                continue;
            }

            // Nếu thiết bị chưa gán công đoạn
            if (empty($device->process_step)) {
                QrScanLog::create([
                    'device_id'      => $deviceId,
                    'barcode'        => $barcode,
                    'scanned_at'     => $scannedAt,
                    'scanned_at_raw' => $timeRaw,
                    'status'         => 'unmapped',
                    'message'        => 'Thiết bị chưa được cấu hình công đoạn sản xuất.',
                    'payload'        => $packet,
                ]);
                continue;
            }

            // Giả lập tài khoản thao tác của nhân viên được phân công cho máy quét
            if ($device->operator_user_id) {
                Auth::loginUsingId($device->operator_user_id);
            }

            // Gọi các Service nghiệp vụ tương ứng
            $res = null;
            try {
                $notes = 'Quét tự động từ Thiết bị quét QR [' . $device->name . ']';

                switch ($device->process_step) {
                    case 'cnc':
                        $res = $this->cncService->completeOrRollback([
                            'product_code' => $barcode,
                            'action_type'  => $device->action_type ?: 'complete',
                            'notes'        => $notes,
                        ]);
                        break;

                    case 'pressing':
                        $res = $this->pressingService->completeOrRollback([
                            'product_code' => $barcode,
                            'action_type'  => $device->action_type ?: 'ép đơn',
                            'notes'        => $notes,
                        ]);
                        break;

                    case 'edge_banding':
                        $res = $this->edgeBandingService->completeOrRollback([
                            'product_code' => $barcode,
                            'action_type'  => $device->action_type ?: 'complete',
                            'notes'        => $notes,
                        ]);
                        break;

                    case 'finishing':
                        $res = $this->finishingService->completeOrRollback([
                            'product_code' => $barcode,
                            'action_type'  => $device->action_type ?: 'complete',
                            'notes'        => $notes,
                        ]);
                        break;

                    case 'qc':
                        $res = $this->qcService->completeOrRollback([
                            'product_code' => $barcode,
                            'action_type'  => $device->action_type ?: 'complete',
                            'notes'        => $notes,
                        ]);
                        break;

                    default:
                        $res = [
                            'success'     => false,
                            'message'     => 'Công đoạn "' . $device->process_step . '" chưa được hỗ trợ.',
                            'status_code' => 400
                        ];
                        break;
                }
            } catch (\Exception $e) {
                $res = [
                    'success'     => false,
                    'message'     => 'Lỗi Service: ' . $e->getMessage(),
                    'status_code' => 500
                ];
            }

            // Lưu lịch sử quét
            QrScanLog::create([
                'device_id'      => $deviceId,
                'barcode'        => $barcode,
                'scanned_at'     => $scannedAt,
                'scanned_at_raw' => $timeRaw,
                'status'         => ($res && $res['success']) ? 'success' : 'failed',
                'message'        => $res['message'] ?? 'Lỗi không rõ nguyên nhân.',
                'payload'        => $packet,
            ]);
        }

        return response()->json([
            'status'   => 'success',
            'received' => $receivedCount
        ], 200);
    }

    /**
     * GET /processes/qr-scans
     * Giao diện quản lý thiết bị và nhật ký quét QR.
     */
    public function index(Request $request)
    {
        $devices = QrDevice::with('operator')->get();
        $users = User::orderBy('name', 'asc')->get();

        // Xây dựng bộ lọc nhật ký quét
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $deviceFilter = $request->input('device_id', '');

        $query = QrScanLog::with('device.operator')->orderBy('id', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('barcode', 'like', '%' . $search . '%')
                  ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($deviceFilter) {
            $query->where('device_id', $deviceFilter);
        }

        $logs = $query->paginate($perPage);

        return view('processes.qr_scans', compact(
            'devices',
            'users',
            'logs',
            'perPage',
            'search',
            'statusFilter',
            'deviceFilter'
        ));
    }

    /**
     * POST /processes/qr-scans/devices/{device}
     * Cập nhật thông tin cấu hình thiết bị.
     */
    public function updateDevice(Request $request, QrDevice $device)
    {
        abort_unless(auth()->user()->can('edit qr device'), 403);

        $request->validate([
            'name'             => 'required|string|max:255',
            'process_step'     => 'nullable|string|in:cnc,pressing,edge_banding,finishing,qc',
            'action_type'      => 'nullable|string|max:255',
            'operator_user_id' => 'nullable|exists:users,id',
            'is_active'        => 'boolean',
            'notes'            => 'nullable|string',
        ]);

        $data = $request->only([
            'name',
            'process_step',
            'action_type',
            'operator_user_id',
            'notes'
        ]);
        
        $data['is_active'] = $request->has('is_active') ? true : false;

        $device->update($data);

        return redirect()->route('processes.qr-scans')->with('success', 'Đã cập nhật cấu hình thiết bị quét thành công.');
    }

    /**
     * DELETE /processes/qr-scans/devices/{device}
     * Xóa thiết bị khỏi danh sách.
     */
    public function deleteDevice(QrDevice $device)
    {
        abort_unless(auth()->user()->can('delete qr device'), 403);

        $device->delete();

        return redirect()->route('processes.qr-scans')->with('success', 'Đã xóa thiết bị quét thành công.');
    }
}
