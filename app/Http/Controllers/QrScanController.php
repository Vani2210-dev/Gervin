<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QrDevice;
use App\Models\QrScanLog;
use App\Models\QrConfigCommand;
use App\Models\User;
use App\Services\CNCService;
use App\Services\PressingService;
use App\Services\EdgeBandingService;
use App\Services\FinishingService;
use App\Services\QCService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // Kiểm tra nếu là GET ping kiểm tra kết nối từ browser / thiết bị
        if ($request->isMethod('get') || $request->isMethod('head')) {
            // Nếu có tham số barcode/result trong query URL thì tiếp tục xử lý
            if (!$request->has('result') && !$request->has('barcode') && !$request->has('data') && !$request->has('code')) {
                return response()->json([
                    'status' => 'online',
                    'message' => 'QR Scan Service is ready'
                ]);
            }
        }

        // 1. Trích xuất payload từ JSON body
        $payload = $request->json()->all();

        // 2. Làm sạch raw content (loại bỏ UTF-8 BOM, ép UTF-8 hợp lệ, trim khoảng trắng)
        $rawContent = (string) $request->getContent();
        if (!empty($rawContent)) {
            $rawContentClean = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF\xFF\xFE]+/', '', $rawContent);
            $rawContentClean = mb_convert_encoding($rawContentClean, 'UTF-8', 'UTF-8');
            $rawContentClean = trim($rawContentClean, " \t\n\r\0\x0B");
        } else {
            $rawContentClean = '';
        }

        // 3. Nếu payload rỗng, thử decode từ raw content đã làm sạch
        if (empty($payload) && !empty($rawContentClean)) {
            $decoded = json_decode($rawContentClean, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        // 4. Nếu vẫn rỗng, kiểm tra form-data ($request->all()) - phòng trường hợp thiết bị gửi form urlencoded
        if (empty($payload)) {
            $all = $request->all();
            if (!empty($all)) {
                // Kiểm tra nếu key của form-data chính là chuỗi JSON
                foreach ($all as $k => $v) {
                    if (is_string($k) && (str_starts_with(trim($k), '[') || str_starts_with(trim($k), '{'))) {
                        $kClean = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF\xFF\xFE]+/', '', $k);
                        $kClean = mb_convert_encoding($kClean, 'UTF-8', 'UTF-8');
                        $decoded = json_decode(trim($kClean), true);
                        if (is_array($decoded)) {
                            $payload = $decoded;
                            break;
                        }
                    }
                }

                if (empty($payload)) {
                    $payload = $all;
                }
            }
        }

        // 5. Nếu vẫn rỗng nhưng có raw content text thuần (máy quét gửi trực tiếp chuỗi barcode đơn giản)
        if (empty($payload) && !empty($rawContentClean)) {
            // Nếu raw content bắt đầu bằng '[' hoặc '{', đây là gói JSON bị đứt đoạn / lỗi cú pháp khi truyền mạng (VD: '[{"time":"...","device')
            // Tuyệt đối không coi chuỗi JSON vỡ này là mã barcode
            if (str_starts_with($rawContentClean, '[') || str_starts_with($rawContentClean, '{')) {
                \Illuminate\Support\Facades\Log::info('Ignored broken/truncated JSON payload from scanner: ' . mb_substr($rawContentClean, 0, 100));
                return response()->json([
                    'status'   => 'success',
                    'message'  => 'Broken JSON payload ignored',
                    'received' => 0
                ], 200);
            }

            $payload = [
                'result' => $rawContentClean,
            ];
        }

        // Nếu hoàn toàn không có dữ liệu: trả về 200 để máy quét xóa gói lỗi khỏi bộ nhớ đệm
        if (empty($payload)) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Empty payload acknowledged',
                'received' => 0
            ], 200);
        }

        // Chuẩn hóa payload thành mảng
        if (!is_array($payload)) {
            $payload = [];
        } elseif (
            isset($payload['device_id']) || 
            isset($payload['deviceId']) || 
            isset($payload['result']) || 
            isset($payload['barcode']) || 
            isset($payload['data']) || 
            isset($payload['code'])
        ) {
            $payload = [$payload];
        }

        // Giải nén các packet nếu trường result/barcode thực chất chứa chuỗi JSON lồng nhau
        $flattenedPayload = [];
        foreach ($payload as $p) {
            if (is_array($p)) {
                $innerStr = $p['result'] ?? $p['barcode'] ?? null;
                if (is_string($innerStr) && (str_starts_with(trim($innerStr), '[') || str_starts_with(trim($innerStr), '{'))) {
                    $cleanInner = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF\xFF\xFE]+/', '', $innerStr);
                    $cleanInner = mb_convert_encoding($cleanInner, 'UTF-8', 'UTF-8');
                    $subDecoded = json_decode(trim($cleanInner), true);
                    if (is_array($subDecoded)) {
                        if (isset($subDecoded[0]) && is_array($subDecoded[0])) {
                            foreach ($subDecoded as $subItem) {
                                if (is_array($subItem)) {
                                    $flattenedPayload[] = $subItem;
                                }
                            }
                        } else {
                            $flattenedPayload[] = $subDecoded;
                        }
                        continue;
                    }
                }
                $flattenedPayload[] = $p;
            } elseif (is_string($p)) {
                $pClean = trim($p);
                if (str_starts_with($pClean, '[') || str_starts_with($pClean, '{')) {
                    $cleanInner = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF\xFF\xFE]+/', '', $pClean);
                    $cleanInner = mb_convert_encoding($cleanInner, 'UTF-8', 'UTF-8');
                    $subDecoded = json_decode($cleanInner, true);
                    if (is_array($subDecoded)) {
                        if (isset($subDecoded[0]) && is_array($subDecoded[0])) {
                            foreach ($subDecoded as $subItem) {
                                if (is_array($subItem)) {
                                    $flattenedPayload[] = $subItem;
                                }
                            }
                        } else {
                            $flattenedPayload[] = $subDecoded;
                        }
                        continue;
                    } else {
                        // Chuỗi JSON bị đứt đoạn / lỗi cú pháp, bỏ qua để không thành barcode rác
                        continue;
                    }
                }
                $flattenedPayload[] = ['result' => $pClean];
            }
        }
        $payload = $flattenedPayload;

        $receivedCount = 0;

        foreach ($payload as $packet) {
            if (!is_array($packet)) {
                if (is_string($packet) && !empty(trim($packet))) {
                    $packet = ['result' => trim($packet)];
                } else {
                    continue;
                }
            }

            $rawDeviceId = $packet['device_id'] 
                ?? $packet['deviceId'] 
                ?? $packet['id'] 
                ?? $packet['device'] 
                ?? $packet['sn'] 
                ?? $packet['mac'] 
                ?? null;

            // Chuẩn hóa deviceId thành số nguyên dương hợp lệ cho cột unsignedBigInteger trong database
            if ($rawDeviceId !== null && is_numeric($rawDeviceId)) {
                $deviceId = (int) $rawDeviceId;
            } elseif (!empty($rawDeviceId)) {
                $deviceId = sprintf('%u', crc32((string) $rawDeviceId));
            } else {
                // Nếu máy quét không gửi device_id, sử dụng ID mặc định dựa theo IP thiết bị
                $deviceId = sprintf('%u', crc32($request->ip() ?: '1'));
            }
            if ($deviceId <= 0) {
                $deviceId = 1;
            }

            $timeRaw = $packet['time'] ?? $packet['timestamp'] ?? $packet['scanned_at'] ?? null;
            $barcode = $packet['result'] 
                ?? $packet['barcode'] 
                ?? $packet['data'] 
                ?? $packet['code'] 
                ?? $packet['qr'] 
                ?? null;

            // Chuẩn hóa và làm sạch chuỗi barcode (loại bỏ ký tự điều khiển ẩn và ép UTF-8 hợp lệ)
            $barcode = (string) $barcode;
            $barcode = mb_convert_encoding($barcode, 'UTF-8', 'UTF-8');
            $barcode = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $barcode));

            // 1. Loại bỏ các ký tự dấu ?, *, khoảng trắng ở đầu hoặc cuối chuỗi do nhiễu UART/Serial máy quét gửi về
            $barcode = trim($barcode, "?*#~`^ \t\n\r\0\x0B");

            // 2. Bỏ qua nếu là mảnh JSON bị đứt đoạn vô tình lọt vào (VD: [{"time":... hoặc {"device":...)
            if ($barcode === '' || str_starts_with($barcode, '[{') || str_starts_with($barcode, '{"') || str_starts_with($barcode, '[') || str_starts_with($barcode, '{')) {
                continue;
            }

            // 3. Bỏ qua nếu quét nhầm mã cấu hình máy quét
            if (str_starts_with($barcode, '<cmd>') || str_starts_with($barcode, 'cmd:')) {
                continue;
            }

            // 4. Trích xuất mã sản phẩm nếu chuỗi chứa định dạng tấm gỗ chuẩn (\d{3,6}\.[A-Za-z0-9_.-]+)
            if (preg_match('/((?:\d{2}\.)?\d{1,8}\.[A-Za-z0-9_.-]+)/', $barcode, $matches)) {
                $barcode = $matches[1];
            }

            // 5. Chuẩn hóa mã tấm: nếu phần mềm nesting Wood xuất mã kèm hậu tố _ (VD: 5213.GV40.TP.016_16) -> chỉ lấy mã tấm trước dấu _
            if (str_contains($barcode, '_')) {
                $barcode = explode('_', $barcode)[0];
            }

            $barcode = trim($barcode, "?*#~`^ \t\n\r\0\x0B");

            // 6. Cắt độ dài tối đa an toàn (190 ký tự) để không bao giờ vượt quá giới hạn VARCHAR(255) của MySQL
            $barcode = mb_substr($barcode, 0, 190);

            if ($barcode === '') {
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
                try {
                    QrScanLog::create([
                        'device_id'      => $deviceId,
                        'barcode'        => $barcode,
                        'scanned_at'     => $scannedAt,
                        'scanned_at_raw' => $timeRaw,
                        'status'         => 'unmapped',
                        'message'        => 'Thiết bị chưa được kích hoạt hoạt động.',
                        'payload'        => $packet,
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to log QrScan: ' . $e->getMessage());
                }
                continue;
            }

            // Nếu thiết bị chưa gán công đoạn
            if (empty($device->process_step)) {
                try {
                    QrScanLog::create([
                        'device_id'      => $deviceId,
                        'barcode'        => $barcode,
                        'scanned_at'     => $scannedAt,
                        'scanned_at_raw' => $timeRaw,
                        'status'         => 'unmapped',
                        'message'        => 'Thiết bị chưa được cấu hình công đoạn sản xuất.',
                        'payload'        => $packet,
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to log QrScan: ' . $e->getMessage());
                }
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
            try {
                QrScanLog::create([
                    'device_id'      => $deviceId,
                    'barcode'        => $barcode,
                    'scanned_at'     => $scannedAt,
                    'scanned_at_raw' => $timeRaw,
                    'status'         => ($res && $res['success']) ? 'success' : 'failed',
                    'message'        => $res['message'] ?? 'Lỗi không rõ nguyên nhân.',
                    'payload'        => $packet,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to log QrScan: ' . $e->getMessage());
            }
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
        $devices = QrDevice::with(['operator', 'latestLog'])->get();
        $users = User::orderBy('name', 'asc')->get();

        // Xây dựng bộ lọc nhật ký quét
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $deviceFilter = $request->input('device_id', '');

        $query = QrScanLog::with('device.operator')->orderBy('id', 'desc');
        $this->applyDateFilter($query, $request, 'created_at');

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

        // Lấy danh sách lệnh cấu hình máy quét
        $configCommands = QrConfigCommand::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('step', 'asc')
            ->get();

        if ($configCommands->isEmpty()) {
            foreach (QrConfigCommand::defaultCommands() as $cmd) {
                QrConfigCommand::create($cmd);
            }
            $configCommands = QrConfigCommand::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();
        }

        // Xác định tab đang mở
        $currTab = $request->input('tab');
        if (!$currTab) {
            if ($request->hasAny(['search', 'status', 'device_id', 'page'])) {
                $currTab = 'logs';
            } else {
                $currTab = 'config';
            }
        }

        return view('processes.qr_scans', compact(
            'devices',
            'users',
            'logs',
            'perPage',
            'search',
            'statusFilter',
            'deviceFilter',
            'currTab',
            'configCommands'
        ));
    }

    /**
     * GET /processes/qr-scans/export-pdf
     * Xuất toàn bộ bộ mã QR cấu hình ra file PDF chuẩn A4.
     */
    public function exportPdf(Request $request)
    {
        $commands = QrConfigCommand::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('step', 'asc')
            ->get();

        if ($commands->isEmpty()) {
            foreach (QrConfigCommand::defaultCommands() as $cmd) {
                QrConfigCommand::create($cmd);
            }
            $commands = QrConfigCommand::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('processes.qr_config_pdf', compact('commands'))
            ->setPaper('a4', 'portrait');

        $fileName = 'Cau_Hinh_May_Quet_QR_Gervin.pdf';

        if ($request->has('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    /**
     * POST /processes/qr-scans/config-commands
     * Cập nhật danh sách các lệnh mã QR cấu hình máy quét.
     */
    public function updateConfigCommands(Request $request)
    {
        $request->validate([
            'commands'         => 'required|array|min:1',
            'commands.*.title' => 'required|string|max:255',
            'commands.*.cmd'   => 'required|string',
            'commands.*.badge' => 'nullable|string|max:255',
            'commands.*.desc'  => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            QrConfigCommand::truncate();
            foreach ($request->commands as $idx => $cmdData) {
                $stepNumber = $idx + 1;
                QrConfigCommand::create([
                    'step'       => $stepNumber,
                    'badge'      => !empty($cmdData['badge']) ? $cmdData['badge'] : ('Bước ' . $stepNumber),
                    'title'      => $cmdData['title'],
                    'desc'       => $cmdData['desc'] ?? null,
                    'cmd'        => $cmdData['cmd'],
                    'sort_order' => $stepNumber,
                    'is_active'  => true,
                ]);
            }
        });

        return redirect()->route('processes.qr-scans', ['tab' => 'config'])
            ->with('success', 'Đã lưu và cập nhật cấu hình bộ mã QR thành công!');
    }

    /**
     * POST /processes/qr-scans/config-commands/reset
     * Khôi phục bộ lệnh QR về mặc định gốc.
     */
    public function resetConfigCommands()
    {
        DB::transaction(function () {
            QrConfigCommand::truncate();
            foreach (QrConfigCommand::defaultCommands() as $cmd) {
                QrConfigCommand::create($cmd);
            }
        });

        return redirect()->route('processes.qr-scans', ['tab' => 'config'])
            ->with('success', 'Đã khôi phục các mã QR cấu hình về mặc định ban đầu!');
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
