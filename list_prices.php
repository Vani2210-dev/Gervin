<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WoodBoardPrice;

$prices = WoodBoardPrice::all();
echo "Total wood_board_prices rows: " . $prices->count() . "\n";
foreach ($prices as $p) {
    echo "ID: {$p->id}, Board ID: {$p->wood_board_id}, Type ID: {$p->wood_board_type_id}, Code: {$p->code}, Name: {$p->name}, Thickness: {$p->thickness}, Price Board: {$p->price_board}, Price M2: {$p->price_m2}\n";
}
