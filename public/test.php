<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Searching for autoload.php in real time...</h2>";

$basePath = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
echo "<p><strong>Base Path:</strong> " . htmlspecialchars($basePath) . "</p>";

// Check potential locations
$checks = [
    $basePath . '/vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    dirname($basePath) . '/vendor/autoload.php',
    dirname(dirname($basePath)) . '/vendor/autoload.php',
];

foreach ($checks as $path) {
    echo "<p>Checking <code>" . htmlspecialchars($path) . "</code>: " . (file_exists($path) ? "<strong style='color:green;'>FOUND!</strong>" : "<span style='color:red;'>NOT FOUND</span>") . "</p>";
}

echo "<h3>Scanning Base Path Directory (" . htmlspecialchars($basePath) . "):</h3>";
if (is_dir($basePath)) {
    $items = scandir($basePath);
    echo "<pre style='background:#f4f4f4;padding:10px;'>" . print_r($items, true) . "</pre>";
}
