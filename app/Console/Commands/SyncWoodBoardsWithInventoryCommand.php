<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class SyncWoodBoardsWithInventoryCommand extends Command
{
    protected $signature = 'inventory:sync-wood-boards';
    protected $description = 'Đồng bộ 2 chiều dữ liệu tồn kho giữa Bảng giá tấm (WoodBoard/WoodBoardPrice) và Kho vật tư (Material)';

    public function handle(InventoryService $inventoryService): int
    {
        $this->info('Đang tiến hành đồng bộ Bảng giá tấm với Kho vật tư...');

        $res = $inventoryService->syncWoodBoardsWithInventory();

        $this->info('Đồng bộ hoàn tất!');
        $this->table(
            ['Chỉ số', 'Số lượng'],
            [
                ['Biến thể tấm (WoodBoardPrice) cập nhật tồn', $res['prices_synced']],
                ['Mã màu ván (WoodBoard) tổng hợp tồn', $res['boards_synced']],
                ['Mã vật tư mới tạo thêm', $res['materials_created']],
                ['Mã vật tư đã liên kết ID', $res['materials_linked']],
                ['Chỉ dán viền liên kết mã màu', $res['edge_bands_linked']],
            ]
        );

        return Command::SUCCESS;
    }
}
