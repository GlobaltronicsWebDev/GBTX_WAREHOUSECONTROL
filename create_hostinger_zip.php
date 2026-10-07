<?php

$zipFile = 'c:/Users/marke/OneDrive/Desktop/GBTX_WAREHOUSE_HOSTINGER_DEPLOY.zip';

if (file_exists($zipFile)) {
    unlink($zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Cannot open <$zipFile>\n");
}

$baseDir = realpath(__DIR__);

$excludeDirs = [
    $baseDir . DIRECTORY_SEPARATOR . '.git',
    $baseDir . DIRECTORY_SEPARATOR . 'node_modules',
    $baseDir . DIRECTORY_SEPARATOR . 'tests',
    $baseDir . DIRECTORY_SEPARATOR . '.agents',
];

$excludeFiles = [
    $baseDir . DIRECTORY_SEPARATOR . 'create_hostinger_zip.php',
    $baseDir . DIRECTORY_SEPARATOR . '.phpunit.result.cache',
];

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$count = 0;
foreach ($files as $file) {
    $filePath = $file->getRealPath();

    // Check excluded directories
    $skip = false;
    foreach ($excludeDirs as $exDir) {
        if (strpos($filePath, $exDir) === 0) {
            $skip = true;
            break;
        }
    }
    if ($skip) {
        continue;
    }

    // Check excluded files
    if (in_array($filePath, $excludeFiles)) {
        continue;
    }

    $relativePath = substr($filePath, strlen($baseDir) + 1);
    // Convert backslashes for zip
    $relativePath = str_replace('\\', '/', $relativePath);

    if ($file->isDir()) {
        $zip->addEmptyDir($relativePath);
    } elseif ($file->isFile()) {
        $zip->addFile($filePath, $relativePath);
        $count++;
    }
}

// Make sure storage directories exist
$ensureDirs = [
    'storage/app/public',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];
foreach ($ensureDirs as $dir) {
    $zip->addEmptyDir($dir);
}

$zip->close();

echo "ZIP created successfully: {$zipFile}\n";
echo "Total files packed: {$count}\n";
echo "File size: " . number_format(filesize($zipFile) / (1024 * 1024), 2) . " MB\n";
