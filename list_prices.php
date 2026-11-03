<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderSupply;
use App\Models\Order;

$supplies = OrderSupply::all();
$codes = $supplies->pluck('order_supply_code')->unique()->values();
echo "Total unique supply codes in OrderSupply: " . count($codes) . "\n";
foreach($codes as $c) {
    echo " - '{$c}'\n";
}

$nonGvSupplies = OrderSupply::where(function($q) {
    $q->whereNull('order_supply_code')
      ->orWhere('order_supply_code', 'NOT LIKE', 'GV%');
})->take(20)->get(['id', 'order_id', 'order_supply_code', 'supply_name']);

echo "\nSample non-GV supplies:\n";
foreach($nonGvSupplies as $s) {
    echo "ID: {$s->id}, OrderID: {$s->order_id}, code: '{$s->order_supply_code}', name: '{$s->supply_name}'\n";
}

