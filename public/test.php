<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Laravel Diagnostic Details</h2>";

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Test DB connection
try {
    echo "<h3>Testing DB Connection:</h3>";
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<p style='color:green;'>✔ DB Connection OK! Database: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>✘ DB Connection FAILED: " . $e->getMessage() . "</p>";
}

// Check recent logs
$logFile = __DIR__ . '/../storage/logs/laravel.log';
echo "<h3>Laravel Error Log:</h3>";
if (file_exists($logFile)) {
    $lines = file($logFile);
    echo "<pre style='background:#1e1e1e;color:#00ff00;padding:15px;overflow-x:auto;'>" . htmlspecialchars(implode('', array_slice($lines, -60))) . "</pre>";
} else {
    echo "<p>No log file found at {$logFile}</p>";
}

// Execute request with full debug
try {
    config(['app.debug' => true]);
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = \Illuminate\Http\Request::create('/', 'GET');
    $response = $kernel->handle($request);
    echo "<h3>Request '/' Status: " . $response->getStatusCode() . "</h3>";
    if ($response->getStatusCode() !== 200) {
        echo "<div style='border:2px solid red;padding:10px;'>" . $response->getContent() . "</div>";
    }
} catch (\Throwable $e) {
    echo "<p style='color:red;'>Caught Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
