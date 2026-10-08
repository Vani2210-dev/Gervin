<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CNCService;
use App\Services\PressingService;
use App\Services\EdgeBandingService;
use App\Services\FinishingService;
use App\Services\QCService;

class ManufactureStepController extends Controller
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
        $this->middleware('auth');
        $this->cncService = $cncService;
        $this->pressingService = $pressingService;
        $this->edgeBandingService = $edgeBandingService;
        $this->finishingService = $finishingService;
        $this->qcService = $qcService;
    }

    private function paginateHistory($history, Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $page = $request->input('page', 1);

        $dateParams = $this->getDateFilterParams($request);
        $startDate = $dateParams['start_date'];
        $endDate = $dateParams['end_date'];

        if ($startDate || $endDate) {
            $history = $history->filter(function ($item) use ($startDate, $endDate) {
                $itemDate = null;
                if (!empty($item->action_time)) {
                    $itemDate = \Carbon\Carbon::parse($item->action_time)->toDateString();
                } elseif (!empty($item->time)) {
                    // Try parsing time if format is H:i:s d/m/Y or d/m/Y
                    try {
                        $itemDate = \Carbon\Carbon::parse($item->time)->toDateString();
                    } catch (\Exception $e) {}
                } elseif (!empty($item->created_at)) {
                    $itemDate = \Carbon\Carbon::parse($item->created_at)->toDateString();
                }

                if (!$itemDate) return true;

                if ($startDate && $endDate) {
                    return $itemDate >= $startDate && $itemDate <= $endDate;
                } elseif ($startDate) {
                    return $itemDate >= $startDate;
                } elseif ($endDate) {
                    return $itemDate <= $endDate;
                }
                return true;
            });
        }

        if ($search) {
            $history = $history->filter(function ($item) use ($search) {
                return str_contains(mb_strtolower($item->product_code ?? ''), mb_strtolower($search))
                    || str_contains(mb_strtolower($item->product_name ?? ''), mb_strtolower($search))
                    || str_contains(mb_strtolower($item->operator ?? ''), mb_strtolower($search))
                    || str_contains(mb_strtolower($item->notes ?? ''), mb_strtolower($search));
            });
        }

        $offset = ($page * $perPage) - $perPage;
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $history->slice($offset, $perPage)->values(),
            $history->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function cnc(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $history = $this->paginateHistory($this->cncService->getHistory(), $request);
        return view('processes.cnc', compact('history', 'perPage', 'search'));
    }

    public function completeCnc(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'nullable|string|in:complete,rollback',
            'cnc_machine' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['product_code'] = $this->sanitizeProductCode($data['product_code']);

        $res = $this->cncService->completeOrRollback($data);

        return response()->json([
            'success' => $res['success'],
            'message' => $res['message'],
            'action_type' => $res['action_type'] ?? null,
            'data' => $res['data'] ?? null
        ], $res['status_code']);
    }

    public function getCncProductStatus(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
        ]);

        $code = $this->sanitizeProductCode($request->product_code);
        $res = $this->cncService->getProductStatus($code);

        return response()->json($res, $res['status_code'] ?? 200);
    }

    public function pressing(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $history = $this->paginateHistory($this->pressingService->getHistory(), $request);
        
        $woodBoardPrices = \App\Models\WoodBoardPrice::with('type')
            ->orderBy('code', 'asc')
            ->get()
            ->groupBy(function ($price) {
                return $price->type->name ?? 'Loại khác';
            });

        return view('processes.pressing', compact('history', 'perPage', 'search', 'woodBoardPrices'));
    }

    public function completePressing(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'required|string|in:làm lệnh ép,xuất kho ván,ép đơn,ép dự trữ,rollback',
        ]);

        $data = $request->all();
        $data['product_code'] = $this->sanitizeProductCode($data['product_code']);

        $res = $this->pressingService->completeOrRollback($data);

        return response()->json([
            'success' => $res['success'],
            'message' => $res['message'],
            'is_bulk' => $res['is_bulk'] ?? null,
            'data' => $res['data'] ?? null
        ], $res['status_code']);
    }

    public function edgeBanding(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $history = $this->paginateHistory($this->edgeBandingService->getHistory(), $request);
        return view('processes.edge_banding', compact('history', 'perPage', 'search'));
    }

    public function completeEdgeBanding(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'edge_banding_length' => 'nullable|numeric|min:0',
            'action_type' => 'nullable|string|in:complete,rollback',
        ]);

        $data = $request->all();
        $data['product_code'] = $this->sanitizeProductCode($data['product_code']);

        $res = $this->edgeBandingService->completeOrRollback($data);

        return response()->json([
            'success' => $res['success'],
            'message' => $res['message'],
            'action_type' => $res['action_type'] ?? null,
            'data' => $res['data'] ?? null
        ], $res['status_code']);
    }

    public function getEdgeBandingProductStatus(Request $request)
    {
        $request->validate(['product_code' => 'required|string']);
        $code = $this->sanitizeProductCode($request->product_code);
        $res = $this->edgeBandingService->getProductStatus($code);
        return response()->json($res, $res['status_code'] ?? 200);
    }

    public function finishing(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $history = $this->paginateHistory($this->finishingService->getHistory(), $request);
        return view('processes.finishing', compact('history', 'perPage', 'search'));
    }

    public function completeFinishing(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'nullable|string|in:complete,rollback',
        ]);

        $data = $request->all();
        $data['product_code'] = $this->sanitizeProductCode($data['product_code']);

        $res = $this->finishingService->completeOrRollback($data);

        return response()->json([
            'success' => $res['success'],
            'message' => $res['message'],
            'action_type' => $res['action_type'] ?? null,
            'data' => $res['data'] ?? null
        ], $res['status_code']);
    }

    public function getFinishingProductStatus(Request $request)
    {
        $request->validate(['product_code' => 'required|string']);
        $code = $this->sanitizeProductCode($request->product_code);
        $res = $this->finishingService->getProductStatus($code);
        return response()->json($res, $res['status_code'] ?? 200);
    }

    public function qc(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $history = $this->paginateHistory($this->qcService->getHistory(), $request);
        return view('processes.qc', compact('history', 'perPage', 'search'));
    }

    public function completeQc(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'nullable|string',
            'error_type' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['product_code'] = $this->sanitizeProductCode($data['product_code']);

        $res = $this->qcService->completeOrRollback($data);

        return response()->json([
            'success' => $res['success'],
            'message' => $res['message'],
            'action_type' => $res['action_type'] ?? null,
            'data' => $res['data'] ?? null
        ], $res['status_code']);
    }

    public function getProductInfo(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
        ]);

        $code = $this->sanitizeProductCode($request->product_code);
        $res = $this->qcService->getProductInfo($code);

        return response()->json($res, $res['status_code'] ?? 200);
    }

    public function packing()
    {
        return view('processes.packing');
    }

    /**
     * Chuẩn hóa mã tấm: loại bỏ dấu _ và các ký tự sau dấu _ do phần mềm nesting Wood xuất ra
     * Ví dụ: 5213.GV40.TP.016_16 -> 5213.GV40.TP.016
     */
    private function sanitizeProductCode(?string $code): string
    {
        $code = trim((string) $code);
        if (str_contains($code, '_')) {
            $code = explode('_', $code)[0];
        }
        return trim($code);
    }
}
