<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\CustomerDebtService;
use Illuminate\Console\Command;

class RebuildCustomerDailyDebts extends Command
{
    protected $signature = 'customers:rebuild-daily-debts {customer_id? : ID của khách hàng cần đồng bộ (để trống để đồng bộ toàn bộ)}';

    protected $description = 'Đồng bộ và tính toán lại toàn bộ Sổ cái công nợ theo ngày (customer_daily_debts) cho khách hàng';

    public function handle(): int
    {
        $customerId = $this->argument('customer_id');

        if ($customerId) {
            $customer = Customer::find($customerId);
            if (!$customer) {
                $this->error("Không tìm thấy khách hàng ID {$customerId}");
                return 1;
            }
            $customers = collect([$customer]);
        } else {
            $customers = Customer::where('status', '!=', 'archived')->get();
        }

        $this->info("Bắt đầu đồng bộ Sổ cái công nợ theo ngày cho {$customers->count()} khách hàng...");
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $c) {
            try {
                CustomerDebtService::syncCustomerDailyDebts($c);
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Lỗi khi đồng bộ khách hàng ID {$c->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Đã hoàn tất đồng bộ Sổ cái công nợ theo ngày thành công!");

        return 0;
    }
}

