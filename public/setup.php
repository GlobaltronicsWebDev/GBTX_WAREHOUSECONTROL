<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Globaltronics WMS Automated Setup & Recovery</h1>";

$baseDir = realpath(__DIR__ . '/..');
echo "<p><strong>Base Directory:</strong> " . htmlspecialchars($baseDir) . "</p>";

// Step 1: Write .env
$envPath = $baseDir . '/.env';
$envContent = <<<EOT
APP_NAME="Globaltronics Warehouse"
APP_ENV=production
APP_KEY=base64:YZwCKsJPwmCbnFcIT0ZRrKOz0jY3voTgWn6h422TcIU=
APP_DEBUG=true
APP_URL=https://globaltronicswhics.globaltronics.net

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u553953718_warehouse2026
DB_USERNAME=u553953718_warehouse
DB_PASSWORD="kVCfJRk~kS8"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=file

MEMCACHED_HOST=127.0.0.1
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\${APP_NAME}"
EOT;

$envResult = file_put_contents($envPath, $envContent);
if ($envResult !== false) {
    echo "<p style='color:green;'>✔ <strong>.env file successfully written!</strong> (" . $envResult . " bytes)</p>";
} else {
    echo "<p style='color:red;'>✘ <strong>Failed to write .env file!</strong> Check directory permissions.</p>";
}

// Step 2: Ensure storage subdirectories exist
$dirsToCreate = [
    $baseDir . '/storage/logs',
    $baseDir . '/storage/framework/cache/data',
    $baseDir . '/storage/framework/sessions',
    $baseDir . '/storage/framework/views',
    $baseDir . '/bootstrap/cache',
];

foreach ($dirsToCreate as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    @chmod($dir, 0777);
}
echo "<p style='color:green;'>✔ <strong>Storage & Bootstrap cache directories verified!</strong></p>";

// Step 3: Run Composer Install if vendor is missing
$vendorAutoload = $baseDir . '/vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
    echo "<p style='color:orange;'>⏳ Vendor is missing. Running composer install via shell_exec...</p>";
    $output = shell_exec("cd " . escapeshellarg($baseDir) . " && composer install --no-dev --optimize-autoloader 2>&1");
    echo "<pre style='background:#222;color:#eee;padding:10px;'>" . htmlspecialchars($output ?: 'No output from composer') . "</pre>";
} else {
    echo "<p style='color:green;'>✔ <strong>vendor/autoload.php exists!</strong></p>";
}

// Step 4: Clear Laravel caches
$artisanClear = shell_exec("cd " . escapeshellarg($baseDir) . " && php artisan config:clear && php artisan cache:clear && php artisan view:clear 2>&1");
echo "<pre style='background:#f4f4f4;padding:10px;'>" . htmlspecialchars($artisanClear ?: 'Cache cleared') . "</pre>";

// Step 5: Test Laravel Boot
if (file_exists($vendorAutoload)) {
    require $vendorAutoload;
    $app = require_once $baseDir . '/bootstrap/app.php';
    echo "<p style='color:green;'>✔ <strong>Laravel App Booted Successfully!</strong></p>";
} else {
    echo "<p style='color:red;'>✘ vendor/autoload.php still not found.</p>";
}

echo "<h3>Setup check completed. Now visit <a href='/'>Home Page</a> or <a href='/admin/dashboard'>Dashboard</a></h3>";
