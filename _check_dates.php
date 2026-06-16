<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LocalSale;

echo 'Total: ' . LocalSale::count() . PHP_EOL;
echo 'With sale_date: ' . LocalSale::whereNotNull('sale_date')->count() . PHP_EOL;
echo 'Sample dates:' . PHP_EOL;
LocalSale::select('id', 'invoice_number', 'sale_date')->orderByDesc('id')->limit(10)->get()->each(function($s) {
    echo $s->id . ' | ' . $s->invoice_number . ' | ' . ($s->sale_date ?? 'NULL') . PHP_EOL;
});
