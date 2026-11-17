<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\WoodBoard;
use App\Models\WoodBoardPrice;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportMaterialsFromExcelCommand extends Command
{
    protected $signature = 'materials:import {file?}';
    protected $description = 'Import danh mục kho và vật tư tổng quát từ file mã.xlsx (MISA)';

    public function handle()
    {
        $filePath = $this->argument('file') ?: base_path('mã.xlsx');
        if (!file_exists($filePath)) {
            $this->error("Không tìm thấy file: {$filePath}");
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}...");

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Báo cáo') ?: $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $warehouseNameMap = [
            '152 - KÍNH'             => 'Kho Kính (152)',
            '152 - NHÔM'             => 'Kho Nhôm (152)',
            '152 Melamin Panex'      => 'Kho Melamin Panex (152)',
            '152_ACRYLIC'            => 'Kho Tấm Acrylic (152)',
            '152_ACRYLIC ĐÀ NẴNG'    => 'Kho Acrylic Đà Nẵng (152)',
            '152_CHI'                => 'Kho Chỉ Dán Viền (152)',
            '152_COT'                => 'Kho Cốt Ván (MDF, PVC, Plywood) (152)',
            '152_HÓA CHẤT'           => 'Kho Hóa Chất - Keo Dán (152)',
            '152_LAMINATE'           => 'Kho Laminate (152)',
            '152_LAMINATE BMH'       => 'Kho Laminate BMH (152)',
            '152_MELAMIN VƯƠNG THANH'=> 'Kho Melamin Vương Thanh (152)',
            '152_MELAMIN_ NHAVIET'   => 'Kho Melamin Nhà Việt (152)',
            '152_MELAMIN_MP'         => 'Kho Melamin MP (152)',
            '152_MELAMIN_TTĐ'        => 'Kho Melamin TTĐ (152)',
            '152_MELAMIN_XN'         => 'Kho Melamin XN (152)',
            '152_MIN An Cường'       => 'Kho Melamin An Cường (152)',
            '152_MIN_ THUYTRINH'     => 'Kho Melamin Thùy Trinh (152)',
            '152_PHU'                => 'Kho Vật Tư Phụ (152)',
            '153_PHU'                => 'Kho Phụ Tùng - Vật Tư Phụ (153)',
            '153_PK'                 => 'Kho Phụ Kiện - Kim Khí (153)',
            '155_CÁNH KÍNH'          => 'Kho Cánh Kính Thành Phẩm (155)',
            '155_EP_TAM'             => 'Kho Ép Tấm Thành Phẩm (155)',
            '155_Thành Phẩm'         => 'Kho Thành Phẩm Gia Công (155)',
            'Melamin Minh Long'      => 'Kho Melamin Minh Long',
        ];

        $currentWhRaw = '';
        $whCache = [];

        // Lấy toàn bộ WoodBoard và WoodBoardPrice để ánh xạ
        $boards = WoodBoard::all()->keyBy(function($b) { return strtoupper(trim($b->color_code)); });
        $prices = WoodBoardPrice::all()->keyBy(function($p) { return strtoupper(trim($p->code)); });

        $importedCount = 0;
        $updatedCount = 0;

        for ($r = 4; $r <= $highestRow; $r++) {
            $c1 = $sheet->getCell("A{$r}")->getValue();
            $c2 = $sheet->getCell("B{$r}")->getValue();
            $c3 = $sheet->getCell("C{$r}")->getValue();
            $c4 = $sheet->getCell("D{$r}")->getValue();
            $c5 = $sheet->getCell("E{$r}")->getValue();
            $c6 = $sheet->getCell("F{$r}")->getValue();

            if ($c1 && str_starts_with(trim((string)$c1), 'Tên kho :')) {
                $currentWhRaw = trim(str_replace('Tên kho :', '', (string)$c1));
                continue;
            }

            $code = trim((string)$c3);
            if ($code === '' || $code === 'Mã hàng') {
                continue;
            }

            $name = trim((string)$c4);
            $unit = trim((string)$c5) ?: 'Cái';
            $origin = trim((string)$c6);

            $whRaw = trim((string)$c2) ?: $currentWhRaw;
            $whCode = preg_replace('/\s*\(\d+\s*\)$/u', '', $whRaw);
            $whCode = trim($whCode);

            // Tìm hoặc tạo kho trong bảng warehouses
            if (!isset($whCache[$whCode])) {
                $whName = $warehouseNameMap[$whCode] ?? $whCode;
                $warehouse = Warehouse::firstOrCreate(
                    ['code' => $whCode],
                    [
                        'name'   => $whName,
                        'status' => 'active',
                    ]
                );
                $whCache[$whCode] = $warehouse;
            }
            $warehouse = $whCache[$whCode];

            // Phân loại item_type
            $itemType = 'hardware';
            $upperWh = strtoupper($whCode);
            if ($upperWh === '152_CHI' || str_starts_with($code, 'CHI') || str_starts_with($code, 'CML.') || str_starts_with($code, 'ASA.CHI')) {
                $itemType = 'edge_band';
            } elseif ($upperWh === '153_PK') {
                $itemType = 'hardware';
            } elseif (in_array($upperWh, ['152_HÓA CHẤT', '152_PHU', '153_PHU'])) {
                $itemType = 'chemical';
            } elseif ($upperWh === '152 - NHÔM') {
                $itemType = 'aluminum';
            } elseif (in_array($upperWh, ['152 - KÍNH', '155_CÁNH KÍNH'])) {
                $itemType = 'glass';
            } elseif ($upperWh === '155_THÀNH PHẨM') {
                $itemType = 'finished';
            } elseif (
                in_array($upperWh, ['152_ACRYLIC', '152_ACRYLIC ĐÀ NẴNG', '152_COT', '152_LAMINATE', '152_LAMINATE BMH', '155_EP_TAM']) ||
                str_contains($upperWh, 'MELAMIN') || str_contains($upperWh, 'MIN') ||
                mb_strtolower($unit) === 'tấm'
            ) {
                $itemType = 'board';
            }

            // Ánh xạ WoodBoard / WoodBoardPrice nếu có
            $upperCode = strtoupper($code);
            $cleanCodeNoStars = rtrim($upperCode, '*');
            $matchedBoard = $boards->get($upperCode) ?: $boards->get($cleanCodeNoStars) ?: $boards->get($cleanCodeNoStars . '**');
            $matchedPrice = $prices->get($upperCode) ?: $prices->get($cleanCodeNoStars) ?: $prices->get($cleanCodeNoStars . '**');

            $mat = Material::where('warehouse_code', $whCode)->where('code', $code)->first();
            if (!$mat) {
                $mat = Material::where('code', $code)->first();
            }

            $data = [
                'code'                => $code,
                'name'                => $name ?: $code,
                'warehouse_code'      => $whCode,
                'warehouse_id'        => $warehouse->id,
                'category'            => $whCode,
                'unit'                => $unit,
                'item_type'           => $itemType,
                'origin'              => $origin ?: null,
                'wood_board_id'       => $matchedBoard?->id ?: ($matchedPrice?->wood_board_id ?? null),
                'wood_board_price_id' => $matchedPrice?->id ?? null,
                'status'              => 'active',
            ];

            if ($mat) {
                $mat->update($data);
                $updatedCount++;
            } else {
                Material::create($data);
                $importedCount++;
            }
        }

        // Đồng bộ thêm các mã tấm ván trong WoodBoard / WoodBoardPrice chưa có trong Material
        foreach (WoodBoardPrice::with('board')->get() as $p) {
            $pCode = trim($p->code);
            if ($pCode === '') continue;
            $exists = Material::where('code', $pCode)->exists();
            if (!$exists) {
                $board = $p->board;
                $defaultWh = Warehouse::where('code', 'KHO-VAN')->first() ?: Warehouse::first();
                Material::create([
                    'code'                => $pCode,
                    'name'                => $p->name ?: ('Tấm ' . $pCode),
                    'warehouse_code'      => $defaultWh?->code ?? 'KHO-VAN',
                    'warehouse_id'        => $defaultWh?->id,
                    'category'            => $board?->price_group ?: 'Tấm cốt gỗ',
                    'unit'                => 'Tấm',
                    'item_type'           => 'board',
                    'origin'              => $board?->origin_code ?? '',
                    'cost_price'          => (float)$p->price_board,
                    'current_stock'       => (float)$p->stock,
                    'wood_board_id'       => $p->wood_board_id,
                    'wood_board_price_id' => $p->id,
                    'status'              => 'active',
                ]);
                $importedCount++;
            }
        }

        $this->info("Hoàn thành! Thêm mới: {$importedCount} vật tư, Cập nhật: {$updatedCount} vật tư.");
        $this->info("Tổng số vật tư trong bảng materials: " . Material::count());
        $this->info("Tổng số kho trong bảng warehouses: " . Warehouse::count());

        return 0;
    }
}
