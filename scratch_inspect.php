<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "receipt_items: " . json_encode(Schema::getColumnListing('inventory_receipt_items')) . "\n";
echo "issue_items: " . json_encode(Schema::getColumnListing('inventory_issue_items')) . "\n";
echo "stocktake_items: " . json_encode(Schema::getColumnListing('inventory_stocktake_items')) . "\n";
echo "transactions: " . json_encode(Schema::getColumnListing('inventory_transactions')) . "\n";
