<?php

namespace App\Console\Commands;

use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Console\Command;

class SeedStandardWarehousesCommand extends Command
{
    protected $signature = 'inventory:seed-warehouses {--import : Tự động nạp toàn bộ danh mục vật tư từ thư mục p_code_excels}';
    protected $description = 'Tự động khởi tạo và chuẩn hóa danh mục 24 kho hàng theo chuẩn Gervin';

    public function handle(InventoryService $inventoryService): int
    {
        $this->info('Đang tiến hành dọn dẹp kho cũ và chuẩn hóa danh mục 24 kho hàng...');

        $deletedOld = $inventoryService->cleanupOldWarehouses();
        if (!empty($deletedOld)) {
            $this->warn('Đã xóa bỏ các kho cũ tạm thời: ' . implode(', ', $deletedOld));
        }

        $res = $inventoryService->ensureStandardWarehouses();
        $this->info("Đã xử lý danh mục kho: Tạo mới {$res['created']} kho, Cập nhật chuẩn hóa {$res['updated']} kho.");

        if ($this->option('import')) {
            $this->info('Đang nạp toàn bộ danh mục mã vật tư từ thư mục p_code_excels...');
            $importRes = $inventoryService->importAllWarehousesFromFolder();
            $this->info("Nạp danh mục hoàn tất! Đã xử lý {$importRes['files_count']} file kho, thêm mới {$importRes['created_count']} mã, cập nhật {$importRes['updated_count']} mã.");
        }

        $warehouses = Warehouse::orderBy('id')->get(['id', 'code', 'name', 'item_name', 'status']);
        $rows = [];
        foreach ($warehouses as $w) {
            $rows[] = [$w->id, $w->code, $w->name, $w->item_name ?: '---', $w->status];
        }

        $this->table(['ID', 'Mã kho', 'Tên kho', 'Loại vật tư chính', 'Trạng thái'], $rows);

        return Command::SUCCESS;
    }
}
