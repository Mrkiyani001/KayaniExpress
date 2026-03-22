<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$bloom = new App\Services\RedisBloomFilter('test:bloom_final');

$bloom->clear();

$bloom->add('03134309073');
echo "Added: 03134309073" . PHP_EOL;

echo "Has 03134309073: " . ($bloom->has('03134309073') ? 'TRUE' : 'FALSE') . PHP_EOL;
echo "Has 03134309072: " . ($bloom->has('03134309072') ? 'TRUE' : 'FALSE') . PHP_EOL;

$bloom->add(923134309072);
echo "Added: 923134309072 (int)" . PHP_EOL;
echo "Has 923134309072: " . ($bloom->has(923134309072) ? 'TRUE' : 'FALSE') . PHP_EOL;

$bloom->clear();