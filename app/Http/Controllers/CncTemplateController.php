<?php

namespace App\Http\Controllers;

use App\Models\CncTemplate;
use Illuminate\Http\Request;

class CncTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view supply',   ['only' => ['index']]);
        $this->middleware('permission:add supply',    ['only' => ['store']]);
        $this->middleware('permission:edit supply',   ['only' => ['update']]);
        $this->middleware('permission:delete supply', ['only' => ['destroy']]);
    }

    public function index()
    {
        $templates = CncTemplate::orderBy('id', 'asc')->get();
        return view('cnc_templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:cnc_templates,name',
            'offset_left'   => 'nullable|integer',
            'offset_right'  => 'nullable|integer',
            'offset_top'    => 'nullable|integer',
            'offset_bottom' => 'nullable|integer',
            'mill_left'     => 'nullable|integer',
            'mill_right'    => 'nullable|integer',
            'mill_top'      => 'nullable|integer',
            'mill_bottom'   => 'nullable|integer',
            'mill_width'    => 'nullable|integer',
            'mill_depth'    => 'nullable|integer',
            'mill_left_2'   => 'nullable|integer',
            'mill_right_2'  => 'nullable|integer',
            'mill_top_2'    => 'nullable|integer',
            'mill_bottom_2' => 'nullable|integer',
            'mill_width_2'  => 'nullable|integer',
            'mill_depth_2'  => 'nullable|integer',
        ]);

        CncTemplate::create($request->only([
            'name',
            'offset_left', 'offset_right', 'offset_top', 'offset_bottom',
            'mill_left', 'mill_right', 'mill_top', 'mill_bottom', 'mill_width', 'mill_depth',
            'mill_left_2', 'mill_right_2', 'mill_top_2', 'mill_bottom_2', 'mill_width_2', 'mill_depth_2',
        ]));

        return redirect()->route('cnc_templates.index')->with('success', 'Thêm mẫu CNC thành công.');
    }

    public function update(Request $request, CncTemplate $cncTemplate)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:cnc_templates,name,' . $cncTemplate->id,
            'offset_left'   => 'nullable|integer',
            'offset_right'  => 'nullable|integer',
            'offset_top'    => 'nullable|integer',
            'offset_bottom' => 'nullable|integer',
            'mill_left'     => 'nullable|integer',
            'mill_right'    => 'nullable|integer',
            'mill_top'      => 'nullable|integer',
            'mill_bottom'   => 'nullable|integer',
            'mill_width'    => 'nullable|integer',
            'mill_depth'    => 'nullable|integer',
            'mill_left_2'   => 'nullable|integer',
            'mill_right_2'  => 'nullable|integer',
            'mill_top_2'    => 'nullable|integer',
            'mill_bottom_2' => 'nullable|integer',
            'mill_width_2'  => 'nullable|integer',
            'mill_depth_2'  => 'nullable|integer',
        ]);

        $cncTemplate->update($request->only([
            'name',
            'offset_left', 'offset_right', 'offset_top', 'offset_bottom',
            'mill_left', 'mill_right', 'mill_top', 'mill_bottom', 'mill_width', 'mill_depth',
            'mill_left_2', 'mill_right_2', 'mill_top_2', 'mill_bottom_2', 'mill_width_2', 'mill_depth_2',
        ]));

        return redirect()->route('cnc_templates.index')->with('success', 'Cập nhật mẫu CNC thành công.');
    }

    public function destroy(CncTemplate $cncTemplate)
    {
        $cncTemplate->delete();
        return redirect()->route('cnc_templates.index')->with('success', 'Đã xóa mẫu CNC.');
    }
}
