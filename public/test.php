<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Vendor Diagnostic Details</h2>";

$autoload = realpath(__DIR__ . '/../vendor/autoload.php') ?: (__DIR__ . '/../vendor/autoload.php');
echo "<p><strong>Autoload Path:</strong> " . htmlspecialchars($autoload) . "</p>";
echo "<p><strong>file_exists:</strong> " . (file_exists($autoload) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>is_file:</strong> " . (is_file($autoload) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>is_readable:</strong> " . (is_readable($autoload) ? 'YES' : 'NO') . "</p>";

$vendorDir = dirname($autoload);
echo "<p><strong>Vendor Dir:</strong> " . htmlspecialchars($vendorDir) . "</p>";
echo "<p><strong>is_dir(vendor):</strong> " . (is_dir($vendorDir) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>is_readable(vendor):</strong> " . (is_readable($vendorDir) ? 'YES' : 'NO') . "</p>";

if (is_dir($vendorDir)) {
    echo "<h3>Vendor contents (first 10):</h3>";
    $items = scandir($vendorDir);
    echo "<pre>" . print_r(array_slice($items, 0, 10), true) . "</pre>";
}

$parentDir = dirname($vendorDir);
echo "<h3>Parent Directory items:</h3>";
$parentItems = scandir($parentDir);
echo "<pre>" . print_r($parentItems, true) . "</pre>";
