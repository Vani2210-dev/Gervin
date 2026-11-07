<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\Order;
use App\Models\OrderSupply;
use App\Models\QrMeasurement;
use Illuminate\Support\Facades\Auth;

class QrCodeGeneratorController extends Controller
{
    /**
     * GET /qrcode
     * 1. Tra cứu thông tin tấm / đơn hàng nếu có mã QR: ?code=... hoặc ?=... hoặc ?qr=...
     * 2. Hoặc hiển thị giao diện tạo mã QR SVG nếu truy cập thông thường từ trình duyệt.
     */
    public function index(Request $request)
    {
        $code = $this->extractQrCodeFromRequest($request);

        // Nếu có mã QR cần tra cứu -> trả về JSON thông tin tấm
        if ($code !== null && $code !== '') {
            return $this->lookupPlate($code, $request);
        }

        // Nếu client yêu cầu JSON mà không truyền mã QR
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã QR (VD: /qrcode?code=... hoặc /qrcode?=...)',
            ], 400);
        }

        // Giao diện Web tạo QR SVG cũ (yêu cầu đăng nhập nếu vào trực tiếp)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'text'             => ['nullable', 'string', 'max:1000'],
            'size'             => ['nullable', 'integer', 'min:120', 'max:600'],
            'margin'           => ['nullable', 'integer', 'min:0', 'max:10'],
            'error_correction' => ['nullable', 'in:L,M,Q,H'],
        ]);

        $text = $request->query('text') === null ? 'DA00003' : trim((string) ($validated['text'] ?? ''));
        $size = (int) ($validated['size'] ?? $request->query('size', 240));
        $margin = (int) ($validated['margin'] ?? $request->query('margin', 2));
        $errorCorrection = (string) ($validated['error_correction'] ?? $request->query('error_correction', 'M'));

        $qrSvg = null;
        if ($text !== '') {
            $qrSvg = QrCode::format('svg')
                ->size($size)
                ->margin($margin)
                ->errorCorrection($errorCorrection)
                ->generate($text);

            $qrSvg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrSvg);
        }

        return view('qrcode.index', compact('text', 'size', 'margin', 'errorCorrection', 'qrSvg'));
    }

    /**
     * GET /qrcode/{code}
     * Tra cứu theo đường dẫn RESTful (VD: /qrcode/26.5723.GV21.TP.PVC.1)
     */
    public function lookupByParam(Request $request, string $code)
    {
        return $this->lookupPlate($code, $request);
    }

    /**
     * Tra cứu thông tin chi tiết tấm theo mã QR.
     */
    protected function lookupPlate(string $code, Request $request)
    {
        $plateInfo = $this->findPlateByCode($code);

        if (!$plateInfo) {
            return response()->json([
                'success' => false,
                'found'   => false,
                'message' => 'Không tìm thấy thông tin cho mã QR: ' . $code,
                'qr_code' => $code,
            ], 404, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        // Loại bỏ object nội bộ trước khi trả về
        unset($plateInfo['code_record']);

        return response()->json(array_merge([
            'success' => true,
            'found'   => true,
        ], $plateInfo), 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * POST /qrcode hoặc POST /qrcode/measurement
     * Nhận và lưu kết quả đo kích thước từ thước đo / máy đo tự động / công nhân QC.
     */
    public function storeMeasurement(Request $request)
    {
        $qrCode = $this->extractQrCodeFromRequest($request);

        if (empty($qrCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã QR (qr_code / code).',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        // Tìm thông tin tấm trong hệ thống
        $plateInfo = $this->findPlateByCode($qrCode);

        // Kích thước thực tế đo được
        $actualHeight = $request->input('actual_height')
            ?? $request->input('actual_length')
            ?? $request->input('height_actual')
            ?? $request->input('length_actual')
            ?? $request->input('dai_thuc_te')
            ?? $request->input('dai_thuc_do')
            ?? $request->input('measured_height')
            ?? $request->input('measured_length')
            ?? null;

        $actualWidth = $request->input('actual_width')
            ?? $request->input('width_actual')
            ?? $request->input('rong_thuc_te')
            ?? $request->input('rong_thuc_do')
            ?? $request->input('measured_width')
            ?? null;

        // Kích thước yêu cầu (thiết kế)
        $reqHeight = $request->input('required_height')
            ?? $request->input('required_length')
            ?? $request->input('height_required')
            ?? $request->input('length_required')
            ?? $request->input('dai_yeu_cau')
            ?? $request->input('dai')
            ?? ($plateInfo['required_height'] ?? null);

        $reqWidth = $request->input('required_width')
            ?? $request->input('width_required')
            ?? $request->input('rong_yeu_cau')
            ?? $request->input('rong')
            ?? ($plateInfo['required_width'] ?? null);

        // Sai số cho phép (nếu có, mặc định ±1.0 mm)
        $tolerance = (float) ($request->input('tolerance')
            ?? $request->input('sai_so')
            ?? $request->input('sai_so_cho_phep')
            ?? $request->input('allowed_tolerance')
            ?? 1.0);

        $tolH = (float) ($request->input('tolerance_height')
            ?? $request->input('tolerance_length')
            ?? $tolerance);

        $tolW = (float) ($request->input('tolerance_width')
            ?? $tolerance);

        // Kiểm tra hợp lệ dữ liệu
        if ($actualHeight === null || $actualWidth === null) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp chiều dài và chiều rộng thực đo (actual_height, actual_width).',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        if ($reqHeight === null || $reqWidth === null) {
            return response()->json([
                'success' => false,
                'message' => 'Không xác định được chiều dài và chiều rộng yêu cầu (required_height, required_width). Vui lòng kiểm tra mã QR hoặc truyền giá trị.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $actH = (float) $actualHeight;
        $actW = (float) $actualWidth;
        $reqH = (float) $reqHeight;
        $reqW = (float) $reqWidth;

        // Tính toán độ sai lệch
        $diffH = round($actH - $reqH, 2);
        $diffW = round($actW - $reqW, 2);

        $isPassedH = abs($diffH) <= $tolH;
        $isPassedW = abs($diffW) <= $tolW;
        $isPassed = $isPassedH && $isPassedW;
        $status = $isPassed ? 'passed' : 'failed';

        $operatorName = $request->input('operator_name')
            ?? $request->input('user')
            ?? (Auth::user()?->name ?? 'Thiết bị đo');

        $notes = $request->input('notes') ?? $request->input('ghi_chu') ?? null;
        $deviceId = $request->input('device_id') ?? $request->input('device_name') ?? null;

        // Lưu bản ghi vào bảng qr_measurements
        $measurement = QrMeasurement::create([
            'qr_code'          => $qrCode,
            'order_id'         => $plateInfo['order']['id'] ?? null,
            'order_code'       => $plateInfo['order']['order_code'] ?? null,
            'customer_name'    => $plateInfo['order']['customer_name'] ?? null,
            'item_type'        => $plateInfo['item_type'] ?? null,
            'item_id'          => $plateInfo['item_id'] ?? null,
            'product_name'     => $plateInfo['product_name'] ?? null,
            'required_height'  => $reqH,
            'required_width'   => $reqW,
            'actual_height'    => $actH,
            'actual_width'     => $actW,
            'diff_height'      => $diffH,
            'diff_width'       => $diffW,
            'tolerance'        => $tolerance,
            'tolerance_height' => $tolH,
            'tolerance_width'  => $tolW,
            'is_passed'        => $isPassed,
            'status'           => $status,
            'device_id'        => $deviceId,
            'operator_name'    => $operatorName,
            'notes'            => $notes,
            'measured_at'      => now(),
            'raw_payload'      => $request->all(),
        ]);

        // Cập nhật nhật ký công đoạn của mã tấm nếu có
        if (!empty($plateInfo['code_record'])) {
            $codeRecord = $plateInfo['code_record'];
            $currentStatus = $codeRecord->status ?? [];
            if (is_string($currentStatus)) {
                $currentStatus = json_decode($currentStatus, true) ?? [];
            }

            $diffHStr = ($diffH >= 0 ? "+{$diffH}" : (string)$diffH);
            $diffWStr = ($diffW >= 0 ? "+{$diffW}" : (string)$diffW);
            $actionText = 'Đo KT: ' . ($isPassed ? 'ĐẠT' : 'LỖI SAI SỐ') . 
                " (Dài: {$actH}/{$reqH}mm [{$diffHStr}mm], Rộng: {$actW}/{$reqW}mm [{$diffWStr}mm])";

            $currentStatus[] = [
                'action'      => $actionText,
                'time'        => now()->format('Y-m-d H:i:s'),
                'user'        => $operatorName,
                'is_passed'   => $isPassed,
                'diff_height' => $diffH,
                'diff_width'  => $diffW,
            ];

            $codeRecord->update(['status' => $currentStatus]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ghi nhận kết quả đo thành công [' . ($isPassed ? 'ĐẠT' : 'KHÔNG ĐẠT') . ']',
            'data'    => [
                'id'               => $measurement->id,
                'qr_code'          => $measurement->qr_code,
                'order_code'       => $measurement->order_code,
                'customer_name'    => $measurement->customer_name,
                'product_name'     => $measurement->product_name,
                'required_height'  => $measurement->required_height,
                'required_width'   => $measurement->required_width,
                'actual_height'    => $measurement->actual_height,
                'actual_width'     => $measurement->actual_width,
                'diff_height'      => $measurement->diff_height,
                'diff_width'       => $measurement->diff_width,
                'tolerance'        => $measurement->tolerance,
                'tolerance_height' => $measurement->tolerance_height,
                'tolerance_width'  => $measurement->tolerance_width,
                'is_passed'        => $measurement->is_passed,
                'status'           => $measurement->status,
                'operator_name'    => $measurement->operator_name,
                'device_id'        => $measurement->device_id,
                'notes'            => $measurement->notes,
                'measured_at'      => $measurement->measured_at?->format('Y-m-d H:i:s'),
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * GET /qrcode/measurements
     * Danh sách lịch sử đo kích thước.
     */
    public function measurementsList(Request $request)
    {
        $query = QrMeasurement::query();

        if ($request->filled('search')) {
            $kw = trim($request->search);
            $query->where(function ($q) use ($kw) {
                $q->where('qr_code', 'like', "%{$kw}%")
                  ->orWhere('order_code', 'like', "%{$kw}%")
                  ->orWhere('customer_name', 'like', "%{$kw}%")
                  ->orWhere('product_name', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('qr_code')) {
            $query->where('qr_code', trim($request->qr_code));
        }

        if ($request->filled('order_code')) {
            $query->where('order_code', trim($request->order_code));
        }

        $measurements = $query->orderBy('id', 'desc')->paginate((int)$request->input('per_page', 20));

        return response()->json($measurements, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Trích xuất mã QR từ Request dưới nhiều định dạng khác nhau:
     * - ?code=...
     * - ?qr=...
     * - ?qr_code=...
     * - ?barcode=...
     * - ?q=...
     * - ?=... (khóa rỗng: /qrcode?=26.5723...)
     * - /qrcode?26.5723...
     * - JSON body: { "code": "..." } hoặc { "qr_code": "..." }
     */
    protected function extractQrCodeFromRequest(Request $request, ?string $paramCode = null): ?string
    {
        if (!empty($paramCode)) {
            return trim($paramCode);
        }

        $candidates = ['code', 'qr', 'qr_code', 'barcode', 'q', 'data'];
        foreach ($candidates as $k) {
            if ($request->filled($k)) {
                return trim((string) $request->input($k));
            }
        }

        // Xử lý query kiểu ?=... (khóa rỗng)
        if ($request->query('') !== null && trim((string) $request->query('')) !== '') {
            return trim((string) $request->query(''));
        }

        // Kiểm tra QUERY_STRING trực tiếp
        $qs = trim((string) $request->server('QUERY_STRING', ''));
        if ($qs !== '') {
            if (str_starts_with($qs, '=')) {
                $val = ltrim($qs, '=');
                $val = explode('&', $val)[0];
                if ($val !== '') return urldecode(trim($val));
            } elseif (!str_contains($qs, '=')) {
                return urldecode(trim($qs));
            }
        }

        // Kiểm tra tham số 'text' (nếu không phải là mẫu mặc định DA00003 của generator hoặc khi gọi API)
        if ($request->filled('text')) {
            $t = trim((string) $request->input('text'));
            if ($t !== 'DA00003' || $request->wantsJson()) {
                return $t;
            }
        }

        // Kiểm tra JSON payload nếu gửi qua raw JSON body
        $json = $request->json()->all();
        if (!empty($json)) {
            foreach ($candidates as $k) {
                if (!empty($json[$k])) {
                    return trim((string) $json[$k]);
                }
            }
        }

        return null;
    }

    /**
     * Tìm thông tin tấm trong CSDL theo mã QR (hỗ trợ Acrylic, Kính, Min Late, Đơn hàng).
     */
    protected function findPlateByCode(string $rawCode): ?array
    {
        $codeStr = trim($rawCode);
        $cleanCode = $codeStr;

        // Bỏ hậu tố _ nesting nếu có (VD: 5213.GV40.TP.016_16 -> 5213.GV40.TP.016)
        if (str_contains($cleanCode, '_')) {
            $cleanCode = explode('_', $cleanCode)[0];
        }

        // 1. Tìm trong AcrylicOrderItemCode
        $codeRecord = AcrylicOrderItemCode::where('product_id', $cleanCode)
            ->orWhere('product_id', $codeStr)
            ->first();
        if ($codeRecord) {
            $item = $codeRecord->acrylicOrderItem;
            $supply = $item?->orderSupply;
            $order = $supply?->order;

            return $this->formatPlateResponse(
                $codeRecord->product_id,
                'acrylic',
                $item?->id,
                $item?->product_name ?? 'Tấm Acrylic',
                (float) ($item?->height ?? 0),
                (float) ($item?->width ?? 0),
                (float) ($item?->thickness ?? 0),
                $item?->grain_direction,
                $item?->notes,
                $order,
                $supply,
                $codeRecord
            );
        }

        // 2. Tìm trong GlassOrderItemCode
        $codeRecord = GlassOrderItemCode::where('product_id', $cleanCode)
            ->orWhere('product_id', $codeStr)
            ->first();
        if ($codeRecord) {
            $item = $codeRecord->glassOrderItem;
            $supply = $item?->orderSupply;
            $order = $supply?->order;

            return $this->formatPlateResponse(
                $codeRecord->product_id,
                'glass',
                $item?->id,
                $item?->product_name ?? 'Cánh kính',
                (float) ($item?->height ?? 0),
                (float) ($item?->width ?? 0),
                (float) ($item?->thickness ?? 0),
                $item?->wing_opening_direction,
                $item?->notes,
                $order,
                $supply,
                $codeRecord
            );
        }

        // 3. Tìm trong MinLateOrderItemCode
        $codeRecord = MinLateOrderItemCode::where('product_id', $cleanCode)
            ->orWhere('product_id', $codeStr)
            ->first();
        if ($codeRecord) {
            $item = $codeRecord->minLateOrderItem;
            $supply = $item?->orderSupply;
            $order = $supply?->order;

            $s = is_array($item?->size) ? $item->size : (json_decode($item?->size ?? '[]', true) ?? []);
            $h = (float) ($s['height'] ?? $s['dai'] ?? $s['dài'] ?? 0);
            $w = (float) ($s['width'] ?? $s['rong'] ?? $s['rộng'] ?? 0);

            return $this->formatPlateResponse(
                $codeRecord->product_id,
                'min_late',
                $item?->id,
                $item?->name ?? 'Tấm Min Late',
                $h,
                $w,
                (float) ($item?->thickness ?? 0),
                $item?->direction,
                $item?->notes,
                $order,
                $supply,
                $codeRecord
            );
        }

        // 4. Tìm theo Mã Đơn Hàng nếu là mã đơn (Order)
        $order = Order::where('order_code', $cleanCode)
            ->orWhere('order_code', $codeStr)
            ->with(['supplies.items.codes', 'supplies.glassItems.codes', 'supplies.minLateItems.codes'])
            ->first();
        if ($order) {
            return [
                'type'          => 'order',
                'order_id'      => $order->id,
                'order_code'    => $order->order_code,
                'customer_name' => $order->customer_name,
                'order_type'    => $order->type,
                'order_date'    => $order->order_date ? (string)$order->order_date : null,
                'status'        => $order->status,
                'total_amount'  => (float) $order->total_amount,
                'supplies'      => $order->supplies->map(function ($s) {
                    return [
                        'supply_code' => $s->order_supply_code,
                        'supply_name' => $s->supply_name,
                        'quantity'    => (float) $s->quantity,
                    ];
                }),
            ];
        }

        return null;
    }

    /**
     * Định dạng cấu trúc JSON trả về cho tấm chi tiết.
     */
    protected function formatPlateResponse(
        string $qrCode,
        string $itemType,
        ?int $itemId,
        string $productName,
        float $height,
        float $width,
        float $thickness,
        ?string $grainDirection,
        ?string $notes,
        ?Order $order,
        ?OrderSupply $supply,
        $codeRecord
    ): array {
        $measurements = QrMeasurement::where('qr_code', $qrCode)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $latestMeasurement = $measurements->first();

        $rawStatus = $codeRecord ? ($codeRecord->status ?? []) : [];
        if (is_string($rawStatus)) {
            $rawStatus = json_decode($rawStatus, true) ?? [];
        }

        return [
            'qr_code'             => $qrCode,
            'item_type'           => $itemType,
            'item_id'             => $itemId,
            'product_name'        => $productName,
            'required_height'     => $height, // Dài yêu cầu (mm)
            'required_width'      => $width,  // Rộng yêu cầu (mm)
            'thickness'           => $thickness,
            'grain_direction'     => $grainDirection,
            'notes'               => $notes,
            'order'               => $order ? [
                'id'            => $order->id,
                'order_code'    => $order->order_code,
                'customer_name' => $order->customer_name,
                'phone'         => $order->phone,
                'address'       => $order->address,
                'order_date'    => $order->order_date ? (string)$order->order_date : null,
                'status'        => $order->status,
                'type'          => $order->type,
            ] : null,
            'supply'              => $supply ? [
                'id'                => $supply->id,
                'order_supply_code' => $supply->order_supply_code,
                'supply_name'       => $supply->supply_name,
            ] : null,
            'tolerances'          => [
                'default'          => 1.0,
                'height_tolerance' => 1.0,
                'width_tolerance'  => 1.0,
                'unit'             => 'mm',
            ],
            'latest_measurement'  => $latestMeasurement ? [
                'id'             => $latestMeasurement->id,
                'actual_height'  => $latestMeasurement->actual_height,
                'actual_width'   => $latestMeasurement->actual_width,
                'diff_height'    => $latestMeasurement->diff_height,
                'diff_width'     => $latestMeasurement->diff_width,
                'is_passed'      => $latestMeasurement->is_passed,
                'status'         => $latestMeasurement->status,
                'measured_at'    => $latestMeasurement->measured_at?->format('Y-m-d H:i:s'),
                'operator_name'  => $latestMeasurement->operator_name,
            ] : null,
            'measurement_history' => $measurements->map(function ($m) {
                return [
                    'id'            => $m->id,
                    'actual_height' => $m->actual_height,
                    'actual_width'  => $m->actual_width,
                    'diff_height'   => $m->diff_height,
                    'diff_width'    => $m->diff_width,
                    'tolerance'     => $m->tolerance,
                    'is_passed'     => $m->is_passed,
                    'status'        => $m->status,
                    'measured_at'   => $m->measured_at?->format('Y-m-d H:i:s'),
                    'operator_name' => $m->operator_name,
                    'notes'         => $m->notes,
                ];
            }),
            'process_status'      => $rawStatus,
            'code_record'         => $codeRecord,
        ];
    }
}
