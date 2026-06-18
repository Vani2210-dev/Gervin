<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = ['wood_board_prices', 'wood_board_price_group_prices'];

foreach ($tables as $table) {
    try {
        $results = DB::select("DESCRIBE {$table}");
        echo "\nTable: {$table}\n";
        foreach ($results as $row) {
            if (in_array($row->Field, ['price_board', 'price_m2'])) {
                print_r($row);
            }
        }
    } catch (\Exception $e) {
        echo "Error on {$table}: " . $e->getMessage() . "\n";
    }
}
