<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('wood-boards:sync-types', function () {
    $types = \App\Models\WoodBoardType::all();
    $boards = \App\Models\WoodBoard::all();
    $count = 0;

    foreach ($types as $type) {
        $prefix = $type->prefix ?? '';
        foreach ($boards as $board) {
            $computedCode = $board->color_code . $prefix;
            $price = \App\Models\WoodBoardPrice::where('wood_board_id', $board->id)
                ->where('wood_board_type_id', $type->id)
                ->first();

            if (!$price) {
                \App\Models\WoodBoardPrice::create([
                    'wood_board_id'      => $board->id,
                    'wood_board_type_id' => $type->id,
                    'code'               => $computedCode,
                    'name'               => $type->name,
                    'thickness'          => null,
                    'price_board'        => 0,
                    'price_m2'           => 0,
                    'stock'              => 0,
                ]);
                $count++;
            } else {
                $updates = [];
                if (empty($price->code)) {
                    $updates['code'] = $computedCode;
                }
                if (empty($price->name)) {
                    $updates['name'] = $type->name;
                }
                if (!empty($updates)) {
                    $price->update($updates);
                    $count++;
                }
            }
        }
    }

    $this->info("Đã đồng bộ xong {$count} bản ghi giá ván theo loại ván.");
})->purpose('Đồng bộ mã số (mã màu + tiền tố) và tên hàng cho tất cả loại ván');
