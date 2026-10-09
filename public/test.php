<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Force debug mode to see exact error
config(['app.debug' => true]);

echo "<h2>Live Application Execution Test</h2>";

try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = \Illuminate\Http\Request::create('/', 'GET');
    $response = $kernel->handle($request);
    
    echo "<h3>HTTP Status Code: " . $response->getStatusCode() . "</h3>";
    echo "<div style='border:1px solid #ccc;padding:10px;'>" . $response->getContent() . "</div>";
} catch (\Throwable $e) {
    echo "<h3 style='color:red;'>Caught Exception: " . $e->getMessage() . "</h3>";
    echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<pre style='background:#f4f4f4;padding:10px;'>" . $e->getTraceAsString() . "</pre>";
}

$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "<h3>Recent Laravel Logs:</h3>";
    $lines = file($logFile);
    echo "<pre style='background:#222;color:#0f0;padding:10px;'>" . htmlspecialchars(implode('', array_slice($lines, -50))) . "</pre>";
} else {
    echo "<h3>No log file found.</h3>";
}
