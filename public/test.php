<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Vendor & File Path Inspector</h2>";

$parentDir = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
echo "<strong>Current Directory:</strong> " . __DIR__ . "<br>";
echo "<strong>Parent Directory:</strong> " . $parentDir . "<br>";

echo "<h3>Checking Parent Directory Contents:</h3>";
if (is_dir($parentDir)) {
    $files = scandir($parentDir);
    echo "<pre style='background:#f4f4f4;padding:10px;'>" . print_r($files, true) . "</pre>";
} else {
    echo "<p style='color:red;'>Parent dir is not a directory!</p>";
}

$vendorDir = $parentDir . '/vendor';
echo "<h3>Checking Vendor Directory:</h3>";
echo "<strong>is_dir(\$vendorDir):</strong> " . (is_dir($vendorDir) ? 'YES' : 'NO') . "<br>";
echo "<strong>is_readable(\$vendorDir):</strong> " . (is_readable($vendorDir) ? 'YES' : 'NO') . "<br>";

$autoloadFile = $vendorDir . '/autoload.php';
echo "<strong>file_exists(\$autoloadFile):</strong> " . (file_exists($autoloadFile) ? 'YES' : 'NO') . "<br>";
echo "<strong>is_readable(\$autoloadFile):</strong> " . (is_readable($autoloadFile) ? 'YES' : 'NO') . "<br>";

if (is_dir($vendorDir)) {
    echo "<h4>Vendor directory contents:</h4>";
    echo "<pre style='background:#f4f4f4;padding:10px;'>" . print_r(scandir($vendorDir), true) . "</pre>";
}
