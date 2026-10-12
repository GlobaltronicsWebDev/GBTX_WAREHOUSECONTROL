<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

echo "Total items: " . InventoryItem::count() . "\n";
echo "Categories summary:\n";
$cats = InventoryItem::select('category', DB::raw('count(*) as count'), DB::raw('sum(quantity) as qty'))
    ->groupBy('category')
    ->get();
foreach ($cats as $c) {
    echo "  - [{$c->category}]: {$c->count} models, {$c->qty} units\n";
}

echo "\nItems with manufacturer PHILIPS:\n";
$philMfg = InventoryItem::where('manufacturer', 'like', '%philips%')
    ->select('category', DB::raw('count(*) as count'), DB::raw('sum(quantity) as qty'))
    ->groupBy('category')
    ->get();
foreach ($philMfg as $c) {
    echo "  - [{$c->category}]: {$c->count} models, {$c->qty} units\n";
}
