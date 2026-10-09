<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3>Step 1: PHP is working: " . phpversion() . "</h3>";

try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "<h3>Step 2: Autoload OK</h3>";
} catch (\Throwable $e) {
    echo "<h3>Step 2 FAILED: " . $e->getMessage() . "</h3>";
    exit;
}

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "<h3>Step 3: Bootstrap App OK</h3>";
} catch (\Throwable $e) {
    echo "<h3>Step 3 FAILED: " . $e->getMessage() . "</h3>";
    exit;
}

try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request = \Illuminate\Http\Request::capture());
    echo "<h3>Step 4: Kernel Handle Status: " . $response->getStatusCode() . "</h3>";
} catch (\Throwable $e) {
    echo "<h3>Step 4 FAILED: " . $e->getMessage() . "</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<h3>All steps passed successfully!</h3>";
