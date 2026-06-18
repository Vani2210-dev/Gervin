<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WoodBoardPriceGroup;
use Illuminate\Support\Facades\DB;

try {
    DB::transaction(function () {
        $group = WoodBoardPriceGroup::create(['name' => 'Test Group ' . time()]);
        $price = $group->prices()->create([
            'wood_board_type_id' => 1,
            'name'               => 'Test Price',
            'thickness'          => '17mm',
            'price_board'        => 350000,
            'price_m2'           => 150000,
        ]);
        echo "Successfully inserted price ID: " . $price->id . "\n";
    });
} catch (\Exception $e) {
    echo "Error inserting: " . $e->getMessage() . "\n";
}
