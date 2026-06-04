<?php

$filePath = 'd:/TAILIEUMONHOCS/TailwindApp/Gervin/resources/views/manufactures/show.blade.php';
$content = file_get_contents($filePath);

// Replace techApprover history color classes
$content = str_replace(
    'techApprover ? \'bg-neutral-100 text-neutral-800\' : \'bg-neutral-50 text-neutral-400\'',
    'techApprover ? \'bg-success-50 text-success-600\' : \'bg-neutral-100 text-neutral-400\'',
    $content
);

// Replace managerApprover history color classes
$content = str_replace(
    'managerApprover ? \'bg-neutral-100 text-neutral-800\' : \'bg-neutral-50 text-neutral-400\'',
    'managerApprover ? \'bg-success-50 text-success-600\' : \'bg-neutral-100 text-neutral-400\'',
    $content
);

// Replace stampsReceiver history color classes
$content = str_replace(
    'stampsReceiver ? \'bg-neutral-100 text-neutral-800\' : \'bg-neutral-50 text-neutral-400\'',
    'stampsReceiver ? \'bg-success-50 text-success-600\' : \'bg-neutral-100 text-neutral-400\'',
    $content
);

// Replace productionStarter history color classes
$content = str_replace(
    'productionStarter ? \'bg-neutral-100 text-neutral-800\' : \'bg-neutral-50 text-neutral-400\'',
    'productionStarter ? \'bg-success-50 text-success-600\' : \'bg-neutral-100 text-neutral-400\'',
    $content
);

// Replace completer history color classes
$content = str_replace(
    'completer ? \'bg-neutral-100 text-neutral-800\' : \'bg-neutral-50 text-neutral-400\'',
    'completer ? \'bg-success-50 text-success-600\' : \'bg-neutral-100 text-neutral-400\'',
    $content
);

file_put_contents($filePath, $content);
echo "SUCCESS";
