<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Laravel Diagnostic Log Inspector</h2>";

$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "<h3>Recent Laravel Logs:</h3>";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -60);
    echo "<pre style='background:#1e1e1e;color:#00ff00;padding:15px;border-radius:8px;overflow-x:auto;'>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
} else {
    echo "<h3>No log file found at: " . htmlspecialchars($logFile) . "</h3>";
}

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // Force debug mode
    config(['app.debug' => true]);
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = \Illuminate\Http\Request::create('/', 'GET');
    $response = $kernel->handle($request);
    
    echo "<h3>Direct Request Status: " . $response->getStatusCode() . "</h3>";
    if ($response->getStatusCode() !== 200) {
        echo "<div style='background:#fff0f0;border:2px solid red;padding:15px;'>" . $response->getContent() . "</div>";
    }
} catch (\Throwable $e) {
    echo "<h3 style='color:red;'>Caught Exception: " . $e->getMessage() . "</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
