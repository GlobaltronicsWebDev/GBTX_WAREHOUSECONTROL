<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Database & Session Inspector</h2>";

$basePath = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

// Bootstrap the HTTP kernel properly so facades work
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->bootstrap();

try {
    echo "<h3>1. Database Connection:</h3>";
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    echo "<p style='color:green;'>✔ Connected to database: <strong>{$dbName}</strong></p>";

    echo "<h3>2. Checking Database Tables:</h3>";
    $hasSessions = \Illuminate\Support\Facades\Schema::hasTable('sessions');
    $hasUsers = \Illuminate\Support\Facades\Schema::hasTable('users');
    $hasInventory = \Illuminate\Support\Facades\Schema::hasTable('inventory_items');

    echo "<p>Table 'sessions': " . ($hasSessions ? "<strong style='color:green;'>EXISTS</strong>" : "<strong style='color:red;'>MISSING!</strong>") . "</p>";
    echo "<p>Table 'users': " . ($hasUsers ? "<strong style='color:green;'>EXISTS</strong>" : "<strong style='color:red;'>MISSING!</strong>") . "</p>";
    echo "<p>Table 'inventory_items': " . ($hasInventory ? "<strong style='color:green;'>EXISTS</strong>" : "<strong style='color:red;'>MISSING!</strong>") . "</p>";

    if (!$hasSessions) {
        echo "<p style='color:orange;'>⚠ Missing 'sessions' table! Need to run: <code>php artisan session:table && php artisan migrate --force</code></p>";
    }
} catch (\Throwable $e) {
    echo "<p style='color:red;'>✘ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Print the real exception from log
$logFile = $basePath . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "<h3>3. Most Recent Log Exception Header:</h3>";
    $content = file_get_contents($logFile);
    preg_match_all('/\[\d{4}-\d{2}-\d{2} [^\]]+\] [^\n]+/', $content, $matches);
    if (!empty($matches[0])) {
        $lastHeaders = array_slice($matches[0], -5);
        echo "<pre style='background:#1e1e1e;color:#ff5555;padding:15px;overflow-x:auto;'>" . htmlspecialchars(implode("\n", $lastHeaders)) . "</pre>";
    }
}
