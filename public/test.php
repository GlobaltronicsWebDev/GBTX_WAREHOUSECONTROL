<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Vendor Status Inspection</h2>";

$base = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
$vendor = $base . '/vendor';
$autoload = $vendor . '/autoload.php';

echo "<p><strong>Base:</strong> " . htmlspecialchars($base) . "</p>";
echo "<p><strong>Vendor dir exists:</strong> " . (is_dir($vendor) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>autoload.php exists:</strong> " . (file_exists($autoload) ? 'YES' : 'NO') . "</p>";

if (is_dir($vendor)) {
    echo "<h3>Files inside vendor:</h3>";
    $files = scandir($vendor);
    echo "<pre style='background:#f4f4f4;padding:10px;'>" . print_r($files, true) . "</pre>";
}

if (file_exists($autoload)) {
    try {
        require $autoload;
        echo "<h3 style='color:green;'>SUCCESS: Autoload loaded successfully!</h3>";
        
        $app = require_once $base . '/bootstrap/app.php';
        echo "<h3 style='color:green;'>SUCCESS: App booted!</h3>";
    } catch (\Throwable $e) {
        echo "<h3 style='color:red;'>FAILED to require: " . $e->getMessage() . "</h3>";
    }
} else {
    echo "<p style='color:red;'>autoload.php does not exist yet. Composer install might still be running or was interrupted.</p>";
}
