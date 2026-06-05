<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$code = \App\Models\AcrylicOrderItemCode::first();
if ($code) {
    print_r($code->toArray());
} else {
    echo "No item codes found.\n";
}
