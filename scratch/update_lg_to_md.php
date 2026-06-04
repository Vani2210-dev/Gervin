<?php

$filePath = 'd:/TAILIEUMONHOCS/TailwindApp/Gervin/resources/views/manufactures/show.blade.php';
$content = file_get_contents($filePath);

// Replace responsive classes in stepper and main layout
$content = str_replace('lg:flex-row', 'md:flex-row', $content);
$content = str_replace('lg:gap-0', 'md:gap-0', $content);
$content = str_replace('lg:block', 'md:block', $content);
$content = str_replace('lg:hidden', 'md:hidden', $content);
$content = str_replace('lg:flex-col', 'md:flex-col', $content);
$content = str_replace('lg:text-center', 'md:text-center', $content);
$content = str_replace('lg:w-[15%]', 'md:w-[15%]', $content);
$content = str_replace('lg:ml-0', 'md:ml-0', $content);
$content = str_replace('lg:mt-3', 'md:mt-3', $content);
$content = str_replace('lg:col-span-8', 'md:col-span-8', $content);
$content = str_replace('lg:col-span-4', 'md:col-span-4', $content);

file_put_contents($filePath, $content);
echo "SUCCESS";
