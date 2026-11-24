<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Gervin Wood Models
use App\Models\Order;
use App\Models\OrderSupply;
use App\Models\AcrylicOrderItem;
use App\Models\MinLateOrderItem;
use App\Models\GlassOrderItem;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\MarketGroup;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\WoodBoard;
use App\Models\WoodBoardPrice;
use App\Models\Material;
use App\Models\Warehouse;
use App\Models\InventoryReceipt;
use App\Models\InventoryIssue;
use App\Models\QrMeasurement;
use App\Models\Vehicle;
use App\Models\VehicleFuelLog;
use App\Models\PackingPackage;
use App\Models\User;

class AiController extends Controller
{
    protected string $apiKey;
    protected string $defaultModel;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openrouter.api_key', env('OPENROUTER_API_KEY', ''));
        $this->defaultModel = (string) config('services.openrouter.default_model', env('OPENROUTER_DEFAULT_MODEL', 'openrouter/free'));
    }

    /**
     * Màn hình Trợ lý AI Gervin Wood ERP
     */
    public function index()
    {
        $context = $this->getSystemContext();
        return view('ai.index', compact('context'));
    }

    /**
     * API Chat với OpenRouter tích hợp toàn diện 7 phân hệ dữ liệu Gervin Wood
     */
    public function chat(Request $request)
    {
        @set_time_limit(120);
        @ini_set('max_execution_time', '120');

        $request->validate([
            'message' => 'required|string',
            'model'   => 'nullable|string',
            'history' => 'nullable|array',
        ]);

        $userMessage = $request->input('message');
        $model = $request->input('model') ?: $this->defaultModel;
        $history = $request->input('history', []);

        // 1. Trích xuất ngữ cảnh tổng quan hệ thống
        $baseContext = $this->getSystemContext();

        // 2. Tra cứu dữ liệu chuyên sâu theo chủ đề câu hỏi người dùng
        $targetedData = $this->extractTargetedData($userMessage);

        $fullContext = [
            'overview' => $baseContext,
            'targeted_lookup_data' => $targetedData,
        ];

        $contextText = json_encode($fullContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // 3. System Prompt chuẩn mực dành riêng cho Gervin Wood ERP
        $systemPrompt = "Bạn là Trợ lý AI Quản trị Doanh nghiệp Cấp cao của Công ty Sản xuất Nội thất Gỗ Gervin (Gervin Wood ERP - gervinwood.vn).\n" .
            "Bạn được kết nối trực tiếp với 7 phân hệ dữ liệu thời gian thực của nhà máy và văn phòng công ty:\n" .
            "1. Quản lý Đơn hàng sản xuất (/orders): Đơn hàng Acrylic, Min/Late, Kính (Glass), Flywood, Gia công dịch vụ; tiến trình xét duyệt 2 bước (Kế toán duyệt, Kỹ thuật duyệt), trạng thái sản xuất (Chờ duyệt, Đang sản xuất, Đã hoàn thành, Đã giao hàng).\n" .
            "2. Khách hàng & Công nợ (/customers): Mã khách hàng, tên xưởng/đại lý, số điện thoại, địa chỉ, nhóm thị trường (Nhóm A, B, C, Thợ, Đại lý...), hạn mức nợ, dư nợ hiện tại, chiết khấu mặc định, lịch sử thanh toán nợ.\n" .
            "3. Nhà cung cấp & Công nợ mua hàng (/suppliers): Danh mục các nhà cung cấp ván gỗ, chỉ dán cạnh, keo PUR, phụ kiện; số dư công nợ phải trả và lịch sử chi trả.\n" .
            "4. Kho vật tư, Ván gỗ & Nẹp chỉ (/warehouses): Danh mục ván gỗ (MDF, HDF, Plywood, mã màu, mã vân, xuất xứ, độ dày, khổ tấm), nẹp chỉ dán cạnh (PVC, ABS), vật tư keo, thẻ kho nhập xuất và cảnh báo tồn kho tối thiểu.\n" .
            "5. Tiến độ sản xuất & Đo đạc KCS / QC (/processes/qc): Dữ liệu quét mã QR từng tấm phôi, kết quả đo đạc kích thước thực tế (Dài x Rộng) bằng thước đo điện tử, độ lệch mm so với bản vẽ, tiêu chuẩn sai số ±1mm, các bước Cắt CNC, Dán cạnh, Làm đẹp, KCS (QC).\n" .
            "6. Quản lý Đội xe & Vận chuyển giao hàng (/vehicles, /processes/dispatch): Đội xe tải giao hàng, biển số xe, tài xế phụ trách, nhật trình đổ dầu, các kiện hàng đóng gói xuất xưởng giao khách.\n" .
            "7. Nhân sự & Hệ thống (/users): Danh sách nhân viên, phòng ban (Kế toán, Kỹ thuật xưởng, Quản đốc, KCS, Thợ vận hành máy, Lái xe).\n\n" .
            "DƯỚI ĐÂY LÀ DỮ LIỆU THỰC TẾ TRÍCH XUẤT TỪ HỆ THỐNG GERVIN WOOD:\n" .
            "```json\n" . $contextText . "\n```\n\n" .
            "QUY TẮC BẮT BUỘC KHI TRẢ LỜI:\n" .
            "- TUYỆT ĐỐI KHÔNG SỬ DỤNG BẤT KỲ EMOJI, ICON HOẶC BIỂU TƯỢNG CẢM XÚC NÀO (nghiêm cấm toàn bộ ký tự emoji, mặt cười, icon như 📊, 💰, ⚠️, 😊, 📦, 📈, 📉, 🎯, 🚚, 🏢, v.v.).\n" .
            "- Toàn bộ nội dung trả lời, tiêu đề, danh sách, bảng biểu chỉ dùng chữ viết tiếng Việt chuẩn mực và số liệu văn bản thuần túy.\n" .
            "- Trả lời bám sát đúng số liệu thực tế được cung cấp. Nêu rõ mã đơn hàng (ví dụ #26.6188), tên khách hàng, mã tấm phôi, kích thước mm, số tiền VND.\n" .
            "- Khi người dùng hỏi về tiến độ tấm/đơn hoặc lỗi KCS, trích dẫn chính xác kích thước yêu cầu, kích thước đo thực tế và độ lệch mm.\n" .
            "- Định dạng bảng biểu (Markdown table) ngay ngắn, chuẩn xác với các cột số liệu rõ ràng.\n" .
            "- Giữ phong thái chuyên nghiệp, chuẩn mực, hỗ trợ đắc lực cho ban giám đốc, phòng kế toán, kỹ thuật và xưởng sản xuất.";

        // 4. Chuẩn bị Messages
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        if (!empty($history) && is_array($history)) {
            $recentHistory = array_slice($history, -6);
            foreach ($recentHistory as $msg) {
                if (isset($msg['role'], $msg['content'])) {
                    $messages[] = [
                        'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                        'content' => $this->removeEmojis((string)$msg['content'])
                    ];
                }
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // 5. Gửi Request OpenRouter API với cơ chế tự động Fallback linh hoạt
        try {
            $client = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer'  => config('app.url', 'https://gervinwood.vn'),
                'X-Title'       => 'Gervin Wood ERP AI Assistant',
                'Content-Type'  => 'application/json',
            ])->timeout(35);

            $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'       => $model,
                'messages'    => $messages,
                'temperature' => 0.4,
            ]);

            // Nếu model chính bị lỗi hoặc timeout, tự động thử fallback sang model miễn phí ổn định khác
            if (!$response->successful() && $model === 'openrouter/free') {
                $fallbackModels = [
                    'google/gemma-4-31b-it:free',
                    'meta-llama/llama-3.3-70b-instruct:free',
                    'liquid/lfm-3-1.7b:free',
                ];
                foreach ($fallbackModels as $fbModel) {
                    try {
                        $fallbackResponse = $client->timeout(20)->post('https://openrouter.ai/api/v1/chat/completions', [
                            'model'       => $fbModel,
                            'messages'    => $messages,
                            'temperature' => 0.4,
                        ]);
                        if ($fallbackResponse->successful()) {
                            $response = $fallbackResponse;
                            $model = $fbModel;
                            break;
                        }
                    } catch (\Throwable $ex) {
                        continue;
                    }
                }
            }

            if ($response->successful()) {
                $resData = $response->json();
                $reply = $resData['choices'][0]['message']['content'] ?? 'Không nhận được phản hồi từ AI.';
                $reply = $this->removeEmojis($reply);
                $usage = $resData['usage'] ?? null;

                return response()->json([
                    'success' => true,
                    'reply'   => $reply,
                    'model'   => $model,
                    'usage'   => $usage,
                ]);
            }

            Log::error('OpenRouter AI Error (Gervin): ' . $response->body());
            $errMsg = $response->json('error.message') ?? ('Mã phản hồi HTTP ' . $response->status());
            return response()->json([
                'success' => false,
                'error'   => 'Lỗi kết nối dịch vụ AI: ' . $errMsg . '. Vui lòng thử lại hoặc chọn mô hình khác!',
            ], 200);

        } catch (\Throwable $e) {
            Log::error('OpenRouter AI Exception (Gervin): ' . $e->getMessage());
            $userFriendlyError = (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'Maximum execution time'))
                ? 'Thời gian phản hồi của mô hình AI quá lâu (Timeout). Vui lòng gửi lại câu hỏi hoặc chọn mô hình khác!'
                : 'Lỗi xử lý AI: ' . $e->getMessage();

            return response()->json([
                'success' => false,
                'error'   => $userFriendlyError,
            ], 200);
        }
    }

    /**
     * Tra cứu dữ liệu bổ sung thông minh bám sát 7 phân hệ của Gervin Wood
     */
    protected function extractTargetedData(string $query): array
    {
        $q = mb_strtolower($query, 'UTF-8');
        $result = [];

        // =========================================================================
        // 1. ĐƠN HÀNG SẢN XUẤT (Orders, Supplies, Acrylic, MinLate, Glass, Flywood)
        // =========================================================================
        if (str_contains($q, 'đơn') || str_contains($q, 'don') || str_contains($q, 'order') || str_contains($q, 'acrylic') || str_contains($q, 'min') || str_contains($q, 'late') || str_contains($q, 'kính') || str_contains($q, 'glass') || str_contains($q, 'flywood') || str_contains($q, 'doanh thu') || str_contains($q, 'tiến độ') || str_contains($q, 'sản xuất') || preg_match('/\b\d{3,6}\b/', $q)) {
            
            $orderQuery = Order::query()->with(['customer:id,name,customer_code,phone', 'supplies']);

            // Tìm theo mã số đơn cụ thể (ví dụ 6188, 6178, 1224)
            if (preg_match('/(?:#|đơn\s*|order\s*)?(\d{3,6})/i', $query, $matches)) {
                $codeNum = $matches[1];
                $orderQuery->where(function($oq) use ($codeNum) {
                    $oq->where('order_code', 'like', "%{$codeNum}%")
                       ->orWhere('id', $codeNum);
                });
            } else {
                // Lấy đơn hàng mới nhất hoặc lọc theo khách
                $orderQuery->where(function($oq) use ($query) {
                    $oq->where('order_code', 'like', "%{$query}%")
                       ->orWhereHas('customer', function($cq) use ($query) {
                           $cq->where('name', 'like', "%{$query}%");
                       });
                });
            }

            $orders = $orderQuery->latest()->take(10)->get();

            if ($orders->isEmpty()) {
                $orders = Order::with('customer:id,name,customer_code,phone')->latest()->take(6)->get();
            }

            $orderList = [];
            foreach ($orders as $ord) {
                $suppliesSummary = [];
                foreach ($ord->supplies as $sup) {
                    $itemCount = 0;
                    if ($ord->type === 'acrylic') {
                        $itemCount = AcrylicOrderItem::where('order_supply_id', $sup->id)->count();
                    } elseif ($ord->type === 'glass') {
                        $itemCount = GlassOrderItem::where('order_supply_id', $sup->id)->count();
                    } else {
                        $itemCount = MinLateOrderItem::where('order_supply_id', $sup->id)->count();
                    }
                    $suppliesSummary[] = [
                        'supply_name' => $sup->supply_name,
                        'supply_code' => $sup->order_supply_code,
                        'items_count' => $itemCount,
                    ];
                }

                $orderList[] = [
                    'order_id'            => $ord->id,
                    'order_code'          => $ord->order_code ?: ('Đơn #' . $ord->id),
                    'customer'            => $ord->customer?->name ?? 'Khách vãng lai',
                    'type'                => strtoupper($ord->type ?? 'ACRYLIC'),
                    'status'              => $ord->status,
                    'accountant_approved' => (bool)$ord->accountant_approved,
                    'technical_approved'  => (bool)$ord->technical_approved,
                    'total_amount'        => floatval($ord->total_amount ?? 0),
                    'paid_amount'         => floatval($ord->paid_amount ?? 0),
                    'debt_amount'         => floatval($ord->debt_amount ?? 0),
                    'order_date'          => $ord->order_date ? Carbon::parse($ord->order_date)->format('d/m/Y') : null,
                    'supplies'            => $suppliesSummary,
                ];
            }

            $result['orders_search_results'] = $orderList;
        }

        // =========================================================================
        // 2. KHÁCH HÀNG & CÔNG NỢ (/customers)
        // =========================================================================
        if (str_contains($q, 'khách') || str_contains($q, 'khach') || str_contains($q, 'nợ') || str_contains($q, 'no') || str_contains($q, 'công nợ') || str_contains($q, 'thị trường') || str_contains($q, 'thi truong') || str_contains($q, 'chiết khấu') || str_contains($q, 'customers')) {
            $custQuery = Customer::query()->with('marketGroup:id,name');

            $custQuery->where(function($sq) use ($query) {
                $sq->where('name', 'like', "%{$query}%")
                   ->orWhere('phone', 'like', "%{$query}%")
                   ->orWhere('customer_code', 'like', "%{$query}%")
                   ->orWhere('debt', '>', 0);
            });

            $customers = $custQuery->orderByDesc('debt')->take(12)->get()->map(fn($c) => [
                'code'             => $c->customer_code,
                'name'             => $c->name,
                'phone'            => $c->phone,
                'market_group'     => $c->marketGroup?->name ?? '---',
                'current_debt'     => floatval($c->debt ?? 0),
                'debt_limit'       => floatval($c->debt_limit ?? 0),
                'default_discount' => floatval($c->default_discount_percent ?? 0) . '%',
                'address'          => $c->address,
            ])->toArray();

            $result['customers_data'] = $customers;
        }

        // =========================================================================
        // 3. NHÀ CUNG CẤP & CÔNG NỢ VẬT TƯ (/suppliers)
        // =========================================================================
        if (str_contains($q, 'nhà cung cấp') || str_contains($q, 'nha cung cap') || str_contains($q, 'ncc') || str_contains($q, 'nợ ncc') || str_contains($q, 'nhập hàng') || str_contains($q, 'suppliers')) {
            if (Schema::hasTable('suppliers')) {
                try {
                    $suppliers = Supplier::where(function($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%")
                           ->orWhere('code', 'like', "%{$query}%")
                           ->orWhere('phone', 'like', "%{$query}%")
                           ->orWhere('debt', '>', 0);
                    })->orderByDesc('debt')->take(10)->get()->map(fn($s) => [
                        'code'         => $s->code,
                        'name'         => $s->name,
                        'phone'        => $s->phone,
                        'current_debt' => floatval($s->debt ?? 0),
                        'address'      => $s->address,
                    ])->toArray();

                    $result['suppliers_data'] = $suppliers;
                } catch (\Throwable $e) {
                    Log::warning('AiController suppliers lookup error: ' . $e->getMessage());
                }
            }
        }

        // =========================================================================
        // 4. KHO VẬT TƯ, VÁN GỖ & NẸP CHỈ (/warehouses)
        // =========================================================================
        if (str_contains($q, 'kho') || str_contains($q, 'ván') || str_contains($q, 'van') || str_contains($q, 'gỗ') || str_contains($q, 'go') || str_contains($q, 'mdf') || str_contains($q, 'hdf') || str_contains($q, 'plywood') || str_contains($q, 'nẹp') || str_contains($q, 'nep') || str_contains($q, 'chỉ') || str_contains($q, 'vật tư') || str_contains($q, 'tồn kho') || str_contains($q, 'nhập kho') || str_contains($q, 'xuất kho')) {
            
            if (Schema::hasTable('wood_boards')) {
                try {
                    // Tìm kiếm ván gỗ theo màu / mã / chủng loại
                    $boards = WoodBoard::where(function($bq) use ($query) {
                        $bq->where('color_code', 'like', "%{$query}%")
                           ->orWhere('foil_name', 'like', "%{$query}%")
                           ->orWhere('price_group', 'like', "%{$query}%")
                           ->orWhere('nature', 'like', "%{$query}%");
                    })->take(12)->get()->map(fn($wb) => [
                        'color_code'         => $wb->color_code,
                        'foil_name'          => $wb->foil_name,
                        'price_group'        => $wb->price_group,
                        'nature'             => $wb->nature,
                        'current_stock'      => floatval($wb->current_stock ?? 0),
                        'min_stock'          => floatval($wb->min_stock ?? 0),
                        'unit'               => $wb->unit ?: 'tấm',
                        'cost_price'         => floatval($wb->cost_price ?? 0),
                        'edge_band_stock'    => floatval($wb->edge_band_current_stock ?? 0),
                        'edge_band_unit'     => $wb->edge_band_unit ?: 'mét',
                    ])->toArray();

                    if (empty($boards)) {
                        // Fallback: Lấy top ván có lượng tồn kho hoặc cảnh báo
                        $boards = WoodBoard::orderBy('current_stock')->take(8)->get()->map(fn($wb) => [
                            'color_code'      => $wb->color_code,
                            'foil_name'       => $wb->foil_name,
                            'price_group'     => $wb->price_group,
                            'current_stock'   => floatval($wb->current_stock ?? 0),
                            'min_stock'       => floatval($wb->min_stock ?? 0),
                            'unit'            => $wb->unit ?: 'tấm',
                            'edge_band_stock' => floatval($wb->edge_band_current_stock ?? 0),
                        ])->toArray();
                    }
                    $result['wood_boards_stock'] = $boards;
                } catch (\Throwable $e) {
                    Log::warning('AiController wood_boards error: ' . $e->getMessage());
                }
            }

            if (Schema::hasTable('inventory_receipts')) {
                try {
                    $recentReceipts = InventoryReceipt::latest()->take(5)->get()->map(fn($r) => [
                        'code'           => $r->code,
                        'date'           => $r->date ? Carbon::parse($r->date)->format('d/m/Y') : null,
                        'supplier_name'  => $r->supplier_name,
                        'total_quantity' => floatval($r->total_quantity ?? 0),
                        'total_amount'   => floatval($r->total_amount ?? 0),
                        'status'         => $r->status,
                    ])->toArray();
                    $result['recent_inventory_receipts'] = $recentReceipts;
                } catch (\Throwable $e) {}
            }

            if (Schema::hasTable('inventory_issues')) {
                try {
                    $recentIssues = InventoryIssue::latest()->take(5)->get()->map(fn($i) => [
                        'code'           => $i->code,
                        'date'           => $i->date ? Carbon::parse($i->date)->format('d/m/Y') : null,
                        'recipient'      => $i->recipient,
                        'reason'         => $i->reason,
                        'total_quantity' => floatval($i->total_quantity ?? 0),
                        'status'         => $i->status,
                    ])->toArray();
                    $result['recent_inventory_issues'] = $recentIssues;
                } catch (\Throwable $e) {}
            }
        }

        // =========================================================================
        // 5. TIẾN ĐỘ SẢN XUẤT, KCS (QC) & ĐO ĐẠC QR CODE (/processes/qc)
        // =========================================================================
        if (str_contains($q, 'kcs') || str_contains($q, 'qc') || str_contains($q, 'đo') || str_contains($q, 'kích thước') || str_contains($q, 'sai số') || str_contains($q, 'lỗi') || str_contains($q, 'tem') || str_contains($q, 'qr') || str_contains($q, 'cnc') || str_contains($q, 'dán cạnh') || str_contains($q, 'làm đẹp') || str_contains($q, 'pvc')) {
            
            if (Schema::hasTable('qr_measurements')) {
                try {
                    $measQuery = QrMeasurement::query();

                    // Tìm kiếm theo mã tem hoặc mã đơn
                    if (preg_match('/[0-9]{2}\.[0-9]{4,5}\.[A-Za-z0-9.]+/i', $query, $mMatches)) {
                        $measQuery->where('qr_code', 'like', "%{$mMatches[0]}%");
                    } else {
                        $measQuery->where(function($mq) use ($query) {
                            $mq->where('qr_code', 'like', "%{$query}%")
                               ->orWhere('order_code', 'like', "%{$query}%")
                               ->orWhere('customer_name', 'like', "%{$query}%")
                               ->orWhere('product_name', 'like', "%{$query}%");
                        });
                    }

                    $measurements = $measQuery->latest()->take(10)->get()->map(fn($m) => [
                        'qr_code'         => $m->qr_code,
                        'order_code'      => $m->order_code,
                        'customer_name'   => $m->customer_name,
                        'product_name'    => $m->product_name,
                        'required_size'   => ($m->required_height ?? 0) . ' x ' . ($m->required_width ?? 0) . ' mm',
                        'actual_size'     => ($m->actual_height ?? 0) . ' x ' . ($m->actual_width ?? 0) . ' mm',
                        'diff_height'     => floatval($m->diff_height ?? 0) . ' mm',
                        'diff_width'      => floatval($m->diff_width ?? 0) . ' mm',
                        'is_passed'       => (bool)$m->is_passed,
                        'qc_status'       => $m->status,
                        'measured_at'     => $m->created_at ? $m->created_at->format('H:i d/m/Y') : null,
                    ])->toArray();

                    if (empty($measurements)) {
                        $measurements = QrMeasurement::latest()->take(6)->get()->map(fn($m) => [
                            'qr_code'       => $m->qr_code,
                            'order_code'    => $m->order_code,
                            'required_size' => ($m->required_height ?? 0) . ' x ' . ($m->required_width ?? 0) . ' mm',
                            'actual_size'   => ($m->actual_height ?? 0) . ' x ' . ($m->actual_width ?? 0) . ' mm',
                            'is_passed'     => (bool)$m->is_passed,
                            'measured_at'   => $m->created_at ? $m->created_at->format('H:i d/m/Y') : null,
                        ])->toArray();
                    }

                    $result['qc_measurements_data'] = $measurements;
                } catch (\Throwable $e) {
                    Log::warning('AiController qr_measurements error: ' . $e->getMessage());
                }
            } elseif (Schema::hasTable('qr_scan_logs')) {
                try {
                    $scanLogs = DB::table('qr_scan_logs')
                        ->where(function($sq) use ($query) {
                            $sq->where('qr_code', 'like', "%{$query}%")
                               ->orWhere('note', 'like', "%{$query}%");
                        })
                        ->latest()
                        ->take(10)
                        ->get()
                        ->map(fn($log) => [
                            'qr_code'    => $log->qr_code,
                            'step'       => $log->step ?? 'qc',
                            'status'     => $log->status ?? 'success',
                            'note'       => $log->note,
                            'scanned_at' => $log->created_at ? Carbon::parse($log->created_at)->format('H:i d/m/Y') : null,
                        ])->toArray();
                    $result['qc_scan_logs'] = $scanLogs;
                } catch (\Throwable $e) {}
            }
        }

        // =========================================================================
        // 6. ĐỘI XE & GIAO HÀNG VẬN CHUYỂN (/vehicles)
        // =========================================================================
        if (str_contains($q, 'xe') || str_contains($q, 'tài xế') || str_contains($q, 'tai xe') || str_contains($q, 'giao hàng') || str_contains($q, 'giao vận') || str_contains($q, 'vận chuyển') || str_contains($q, 'dầu') || str_contains($q, 'nhật trình') || str_contains($q, 'đóng gói') || str_contains($q, 'kiện')) {
            if (Schema::hasTable('vehicles')) {
                try {
                    $vehicles = Vehicle::all()->map(fn($v) => [
                        'plate_number'    => $v->plate_number,
                        'name'            => $v->name,
                        'driver_name'     => $v->driver_name,
                        'driver_phone'    => $v->driver_phone,
                        'fuel_type'       => $v->fuel_type,
                        'current_km'      => floatval($v->current_km ?? 0),
                        'status'          => $v->status,
                    ])->toArray();
                    $result['vehicles_fleet'] = $vehicles;
                } catch (\Throwable $e) {}
            }

            if (Schema::hasTable('vehicle_fuel_logs')) {
                try {
                    $recentFuelLogs = VehicleFuelLog::with('vehicle:id,plate_number')
                        ->latest()
                        ->take(6)
                        ->get()
                        ->map(fn($f) => [
                            'plate_number' => $f->vehicle?->plate_number ?? '---',
                            'date'         => $f->log_date ? Carbon::parse($f->log_date)->format('d/m/Y') : null,
                            'liters'       => floatval($f->liters ?? 0),
                            'total_cost'   => floatval($f->total_cost ?? 0),
                            'current_km'   => floatval($f->current_km ?? 0),
                        ])->toArray();
                    $result['recent_fuel_logs'] = $recentFuelLogs;
                } catch (\Throwable $e) {}
            }

            if (Schema::hasTable('packing_packages')) {
                try {
                    $recentPackages = PackingPackage::with('order:id,order_code')
                        ->latest()
                        ->take(6)
                        ->get()
                        ->map(fn($p) => [
                            'package_code'   => $p->package_code,
                            'order_code'     => $p->order?->order_code ?? ('#' . $p->order_id),
                            'package_number' => $p->package_number,
                            'total_items'    => $p->total_items,
                            'status'         => $p->status,
                        ])->toArray();
                    $result['recent_packing_packages'] = $recentPackages;
                } catch (\Throwable $e) {}
            }
        }

        // =========================================================================
        // 7. NHÂN SỰ & HỆ THỐNG (/users)
        // =========================================================================
        if (str_contains($q, 'nhân sự') || str_contains($q, 'nhân viên') || str_contains($q, 'user') || str_contains($q, 'vai trò') || str_contains($q, 'admin') || str_contains($q, 'kế toán') || str_contains($q, 'kỹ thuật') || str_contains($q, 'quản đốc') || str_contains($q, 'xưởng')) {
            $users = User::take(20)->get()->map(fn($u) => [
                'name'   => $u->name,
                'email'  => $u->email,
                'role'   => $u->role ?: ($u->roles?->pluck('name')->implode(', ') ?: 'Nhân viên'),
                'phone'  => $u->phone,
                'status' => $u->status ?? 'active',
            ])->toArray();

            $result['users_directory'] = $users;
        }

        return $result;
    }

    /**
     * API xem ngữ cảnh hệ thống
     */
    public function contextApi()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->getSystemContext()
        ]);
    }

    /**
     * Thu thập số liệu tổng quan thời gian thực toàn công ty Gervin Wood
     */
    public function getSystemContext(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Thống kê Đơn hàng & Doanh số
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $todayRevenue = Order::whereDate('created_at', $today)->sum('total_amount');
        $monthRevenue = Order::where('created_at', '>=', $startOfMonth)->sum('total_amount');

        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status ?? 'unknown' => [
                    'count'        => $item->count,
                    'total_amount' => round(floatval($item->total ?? 0), 0)
                ]];
            })->toArray();

        $ordersByType = Order::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->type ?? 'other' => $item->count])
            ->toArray();

        // 2. Thống kê Khách hàng & Công nợ
        $totalCustomers = Customer::count();
        $totalDebt = Customer::sum('debt');

        $topDebtCustomers = Customer::where('debt', '>', 0)
            ->with('marketGroup:id,name')
            ->orderByDesc('debt')
            ->take(8)
            ->get(['id', 'customer_code', 'name', 'phone', 'debt', 'debt_limit', 'market_group_id'])
            ->map(fn($c) => [
                'name'         => $c->name,
                'code'         => $c->customer_code,
                'phone'        => $c->phone,
                'debt'         => floatval($c->debt ?? 0),
                'debt_limit'   => floatval($c->debt_limit ?? 0),
                'market_group' => $c->marketGroup?->name ?? 'Chưa phân nhóm',
            ])->toArray();

        // 3. Thống kê Nhà cung cấp & Công nợ phải trả
        $totalSuppliers = 0;
        $totalSupplierDebt = 0;
        if (Schema::hasTable('suppliers')) {
            try {
                $totalSuppliers = Supplier::count();
                $totalSupplierDebt = Supplier::sum('debt');
            } catch (\Throwable $e) {}
        }

        // 4. Kho Ván gỗ & Nẹp chỉ
        $totalWoodBoards = 0;
        $lowStockWoodBoards = [];
        if (Schema::hasTable('wood_boards')) {
            try {
                $totalWoodBoards = WoodBoard::count();
                $lowStockWoodBoards = WoodBoard::whereColumn('current_stock', '<=', 'min_stock')
                    ->orWhere('current_stock', '<=', 5)
                    ->take(8)
                    ->get(['color_code', 'foil_name', 'price_group', 'current_stock', 'min_stock', 'unit'])
                    ->map(fn($wb) => [
                        'color_code' => $wb->color_code,
                        'foil_name'  => $wb->foil_name,
                        'stock'      => floatval($wb->current_stock ?? 0),
                        'min_stock'  => floatval($wb->min_stock ?? 0),
                        'unit'       => $wb->unit ?: 'tấm',
                    ])->toArray();
            } catch (\Throwable $e) {}
        }

        // 5. Thống kê KCS & Đo đạc QR Code
        $todayQcCount = 0;
        $todayQcPassed = 0;
        $todayQcFailed = 0;
        if (Schema::hasTable('qr_measurements')) {
            try {
                $todayQcCount = QrMeasurement::whereDate('created_at', $today)->count();
                $todayQcPassed = QrMeasurement::whereDate('created_at', $today)->where('is_passed', 1)->count();
                $todayQcFailed = $todayQcCount - $todayQcPassed;
            } catch (\Throwable $e) {}
        } elseif (Schema::hasTable('qr_scan_logs')) {
            try {
                $todayQcCount = DB::table('qr_scan_logs')->whereDate('created_at', $today)->count();
                $todayQcPassed = DB::table('qr_scan_logs')->whereDate('created_at', $today)->where('status', 'success')->count();
                $todayQcFailed = $todayQcCount - $todayQcPassed;
            } catch (\Throwable $e) {}
        }

        // 6. Đội xe vận chuyển
        $totalVehicles = 0;
        if (Schema::hasTable('vehicles')) {
            try {
                $totalVehicles = Vehicle::count();
            } catch (\Throwable $e) {}
        }

        // 7. Nhân sự
        $totalUsers = 0;
        if (Schema::hasTable('users')) {
            try {
                $totalUsers = User::count();
            } catch (\Throwable $e) {}
        }

        return [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'business_overview' => [
                'today_orders_count'       => $todayOrders,
                'today_revenue'            => round(floatval($todayRevenue ?? 0), 0),
                'month_revenue'            => round(floatval($monthRevenue ?? 0), 0),
                'total_orders_all_time'    => $totalOrders,
                'total_customers'          => $totalCustomers,
                'total_receivable_debt'    => round(floatval($totalDebt ?? 0), 0),
                'total_suppliers'          => $totalSuppliers,
                'total_payable_debt'       => round(floatval($totalSupplierDebt ?? 0), 0),
                'total_wood_board_types'   => $totalWoodBoards,
                'today_qc_scans'           => $todayQcCount,
                'today_qc_passed'          => $todayQcPassed,
                'today_qc_failed'          => $todayQcFailed,
                'fleet_vehicles_count'     => $totalVehicles,
                'active_employees'         => $totalUsers,
            ],
            'orders_by_status'             => $ordersByStatus,
            'orders_by_type'               => $ordersByType,
            'top_debt_customers'           => $topDebtCustomers,
            'low_stock_wood_boards'        => $lowStockWoodBoards,
        ];
    }

    /**
     * Loại bỏ toàn bộ các ký tự Emoji và biểu tượng cảm xúc
     */
    protected function removeEmojis(string $string): string
    {
        return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}\x{200D}\x{FE0E}\x{FE0F}]/u', '', $string);
    }
}
