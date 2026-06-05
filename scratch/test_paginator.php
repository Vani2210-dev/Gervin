<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = new \Illuminate\Pagination\LengthAwarePaginator([1, 2], 100, 10, 6, [
    'path' => 'http://localhost/test'
]);
$p->onEachSide(1);

print_r($p->linkCollection()->toArray());
