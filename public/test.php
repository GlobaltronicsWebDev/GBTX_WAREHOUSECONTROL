<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Live Laravel Health & Error Inspector</h2>";

$basePath = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
$autoload = $basePath . '/vendor/autoload.php';

require $autoload;
$app = require_once $basePath . '/bootstrap/app.php';

// Clear config cache in-memory & on disk
@unlink($basePath . '/bootstrap/cache/config.php');
@unlink($basePath . '/bootstrap/cache/routes-v7.php');
@unlink($basePath . '/bootstrap/cache/events.php');

try {
    echo "<h3>1. Testing Database:</h3>";
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<p style='color:green;'>✔ <strong>Database Connected!</strong> Database Name: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>✘ <strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    echo "<h3>2. Testing Route '/' Dispatch:</h3>";
    config(['app.debug' => true]);
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = \Illuminate\Http\Request::create('/', 'GET');
    $response = $kernel->handle($request);
    
    echo "<p><strong>HTTP Status:</strong> " . $response->getStatusCode() . "</p>";
    if ($response->getStatusCode() === 200) {
        echo "<p style='color:green;'>✔ <strong>SUCCESS! Home page renders with 200 OK!</strong></p>";
    } else {
        echo "<div style='background:#fff0f0;border:2px solid red;padding:15px;'>" . $response->getContent() . "</div>";
    }
} catch (\Throwable $e) {
    echo "<h3 style='color:red;'>Caught Exception: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<pre style='background:#222;color:#ff8888;padding:10px;overflow-x:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

$logFile = $basePath . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "<h3>3. Recent Laravel Log Entries:</h3>";
    $lines = file($logFile);
    echo "<pre style='background:#1e1e1e;color:#00ff00;padding:15px;overflow-x:auto;'>" . htmlspecialchars(implode('', array_slice($lines, -40))) . "</pre>";
}
