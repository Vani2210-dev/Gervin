<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerCareLog;
use Illuminate\Http\Request;

class CustomerCareLogController extends Controller
{
    public function index(Customer $customer)
    {
        $logs = $customer->careLogs()->with('user')->get()->map(function ($l) {
            return [
                'id'                 => $l->id,
                'visit_date'         => $l->visit_date ? $l->visit_date->format('d/m/Y') : '—',
                'visit_date_raw'     => $l->visit_date ? $l->visit_date->format('Y-m-d') : '',
                'user_name'          => $l->user ? ($l->user->user_code ? '[' . $l->user->user_code . '] ' : '') . $l->user->name : 'N/A',
                'status'             => $l->status,
                'feedback'           => $l->feedback,
                'customer_proposal'  => $l->customer_proposal,
                'sale_proposal'      => $l->sale_proposal,
                'photos'             => $l->photos ?: [],
                'latitude'           => $l->latitude,
                'longitude'          => $l->longitude,
                'notes'              => $l->notes,
                'created_at'         => $l->created_at ? $l->created_at->format('H:i d/m/Y') : '',
            ];
        });

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'            => $customer->id,
                'name'          => $customer->name,
                'customer_code' => $customer->customer_code,
                'address'       => $customer->address,
                'phone'         => $customer->phone,
            ],
            'logs'     => $logs,
        ]);
    }

    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'visit_date'        => 'required|date',
            'status'            => 'nullable|string|max:100',
            'feedback'          => 'nullable|string',
            'customer_proposal' => 'nullable|string',
            'sale_proposal'     => 'nullable|string',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'notes'             => 'nullable|string',
        ]);

        $photos = $this->processLogPhotos($request);

        $log = CustomerCareLog::create([
            'customer_id'       => $customer->id,
            'user_id'           => auth()->id(),
            'visit_date'        => $request->visit_date,
            'status'            => $request->status,
            'feedback'          => $request->feedback,
            'customer_proposal' => $request->customer_proposal,
            'sale_proposal'     => $request->sale_proposal,
            'photos'            => $photos,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
            'notes'             => $request->notes,
        ]);

        // Cập nhật thông tin mới nhất vào hồ sơ khách hàng
        $updateData = [];
        if ($request->filled('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->filled('feedback')) {
            $updateData['feedback'] = $request->feedback;
        }
        if ($request->filled('customer_proposal')) {
            $updateData['customer_proposal'] = $request->customer_proposal;
        }
        if ($request->filled('sale_proposal')) {
            $updateData['sale_proposal'] = $request->sale_proposal;
        }
        if (!empty($photos)) {
            $existingPhotos = is_array($customer->photos) ? $customer->photos : (is_string($customer->photos) ? (json_decode($customer->photos, true) ?: []) : []);
            $updateData['photos'] = array_values(array_unique(array_merge($photos, $existingPhotos)));
        }
        if ($request->filled('latitude')) {
            $updateData['latitude'] = $request->latitude;
        }
        if ($request->filled('longitude')) {
            $updateData['longitude'] = $request->longitude;
        }

        if (!empty($updateData)) {
            $customer->update($updateData);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã ghi nhận chăm sóc thị trường thành công.',
                'log'     => $log,
            ]);
        }

        return redirect()->back()->with('success', 'Đã lưu nhật ký đi thị trường của khách hàng ' . $customer->name . '.');
    }

    public function destroy(Customer $customer, CustomerCareLog $careLog)
    {
        $careLog->delete();
        return redirect()->back()->with('success', 'Đã xóa lượt ghi nhận chăm sóc.');
    }

    private function processLogPhotos(Request $request): array
    {
        $photos = [];
        $uploadDir = public_path('uploads/customers');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 1. Ảnh base64 đã gắn timestamp từ canvas
        if ($request->filled('photo_data')) {
            $base64List = (array) $request->photo_data;
            foreach ($base64List as $b64) {
                if (empty($b64)) continue;
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) {
                    $raw = substr($b64, strpos($b64, ',') + 1);
                    $decoded = base64_decode($raw);
                    if ($decoded !== false) {
                        $ext = strtolower($type[1]);
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) $ext = 'jpg';
                        $filename = 'care_' . uniqid() . '_' . time() . '.' . $ext;
                        file_put_contents($uploadDir . '/' . $filename, $decoded);
                        $photos[] = 'uploads/customers/' . $filename;
                    }
                }
            }
        }

        // 2. File upload trực tiếp
        if ($request->hasFile('photos_files')) {
            foreach ((array)$request->file('photos_files') as $file) {
                if ($file && $file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = 'care_' . uniqid() . '_' . time() . '.' . $ext;
                    $file->move($uploadDir, $filename);
                    $photos[] = 'uploads/customers/' . $filename;
                }
            }
        }

        return array_values(array_unique($photos));
    }
}
