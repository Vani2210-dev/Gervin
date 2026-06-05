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

    public function cnc()
    {
        $history = $this->cncService->getHistory();
        return view('processes.cnc', compact('history'));
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

    public function pressing()
    {
        $history = $this->pressingService->getHistory();
        return view('processes.pressing', compact('history'));
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

    public function edgeBanding()
    {
        $history = $this->edgeBandingService->getHistory();
        return view('processes.edge_banding', compact('history'));
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

    public function finishing()
    {
        $history = $this->finishingService->getHistory();
        return view('processes.finishing', compact('history'));
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

    public function qc()
    {
        $history = $this->qcService->getHistory();
        return view('processes.qc', compact('history'));
    }

    public function completeQc(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'nullable|string|in:complete,rollback',
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
