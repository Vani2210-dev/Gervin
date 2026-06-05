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

        $res = $this->cncService->completeOrRollback($request->all());

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

        $res = $this->cncService->getProductStatus($request->product_code);

        return response()->json($res, $res['status_code'] ?? 200);
    }

    public function pressing(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $history = $this->paginateHistory($this->pressingService->getHistory(), $request);
        return view('processes.pressing', compact('history', 'perPage', 'search'));
    }

    public function completePressing(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'required|string|in:làm lệnh ép,xuất kho ván,ép đơn,ép dự trữ,rollback',
        ]);

        $res = $this->pressingService->completeOrRollback($request->all());

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

        $res = $this->edgeBandingService->completeOrRollback($request->all());

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
        $res = $this->edgeBandingService->getProductStatus($request->product_code);
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

        $res = $this->finishingService->completeOrRollback($request->all());

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
        $res = $this->finishingService->getProductStatus($request->product_code);
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

        $res = $this->qcService->completeOrRollback($request->all());

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

        $res = $this->qcService->getProductInfo($request->product_code);

        return response()->json($res, $res['status_code'] ?? 200);
    }

    public function packing()
    {
        return view('processes.packing');
    }

    public function shipped()
    {
        return view('processes.shipped');
    }
}
