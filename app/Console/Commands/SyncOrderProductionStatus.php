<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class SyncOrderProductionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-production-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ trạng thái Đang sản xuất (in_production) cho các đơn hàng đã có tấm được gia công/quét mã';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu kiểm tra và đồng bộ trạng thái sản xuất cho các đơn hàng...');

        $orders = Order::whereNotIn('status', ['draft', 'cancelled'])
            ->with([
                'supplies.items.codes',
                'supplies.minLateItems.codes',
                'supplies.glassItems.codes',
            ])
            ->get();

        $prodCount = 0;
        $compCount = 0;

        foreach ($orders as $order) {
            if (in_array($order->status, ['transferred', 'pending'])) {
                if ($order->checkAndMarkInProduction()) {
                    $this->line(" - Đơn hàng #{$order->order_code} (ID: {$order->id}) -> Đã chuyển sang 'in_production'");
                    $prodCount++;
                }
            }
            if ($order->status !== 'completed') {
                if ($order->checkAndMarkCompleted()) {
                    $this->line(" - Đơn hàng #{$order->order_code} (ID: {$order->id}) -> Đã hoàn thành tất cả tấm và chuyển sang 'completed'");
                    $compCount++;
                }
            }
        }

        $this->info("Đã đồng bộ: {$prodCount} đơn sang 'Đang sản xuất', {$compCount} đơn sang 'Hoàn thành'.");

        return Command::SUCCESS;
    }
}