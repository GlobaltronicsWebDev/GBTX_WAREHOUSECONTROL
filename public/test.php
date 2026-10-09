<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Globaltronics WMS Database & Environment Inspector</h2>";

$basePath = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
echo "<p><strong>Base Path:</strong> {$basePath}</p>";
echo "<p><strong>Current Working Dir:</strong> " . getcwd() . "</p>";

// 1. Check .env file directly
$envFile = $basePath . '/.env';
echo "<h3>1. Checking .env file</h3>";
if (!file_exists($envFile)) {
    echo "<p style='color:orange;'>⚠ .env was missing. Automatically creating now...</p>";
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
REDIS_PORT=6379

MAIL_MAILER=log
EOT;
    file_put_contents($envFile, $envContent);
    @chmod($envFile, 0644);
}

if (file_exists($envFile)) {
    echo "<p style='color:green;'>✔ .env file exists (" . filesize($envFile) . " bytes)</p>";
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<pre style='background:#f4f4f4;padding:10px;'>";
    foreach ($envLines as $line) {
        if (preg_match('/^(DB_|APP_|CACHE_|SESSION_)/', $line)) {
            if (str_starts_with($line, 'DB_PASSWORD=') || str_starts_with($line, 'APP_KEY=')) {
                echo substr($line, 0, 12) . "********\n";
            } else {
                echo htmlspecialchars($line) . "\n";
            }
        }
    }
    echo "</pre>";
}

// 2. Test direct PDO MySQL connection
echo "<h3>2. Direct MySQL Connection Test (bypass Laravel)</h3>";
$hostsToTry = ['127.0.0.1', 'localhost'];
$dbUser = 'u553953718_warehouse';
$dbPass = 'kVCfJRk~kS8';
$dbName = 'u553953718_warehouse2026';

foreach ($hostsToTry as $host) {
    try {
        $dsn = "mysql:host={$host};port=3306;dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        echo "<p style='color:green;'>✔ Direct PDO connection to MySQL on <strong>{$host}</strong> SUCCESSFUL!</p>";
        
        $tablesStmt = $pdo->query("SHOW TABLES");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tables found in <strong>{$dbName}</strong> (" . count($tables) . "): " . htmlspecialchars(implode(', ', array_slice($tables, 0, 15))) . "...</p>";
        break;
    } catch (\Throwable $e) {
        echo "<p style='color:red;'>✘ Direct PDO connection on <strong>{$host}</strong> failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// 3. Check and clean bootstrap cache
echo "<h3>3. Checking Bootstrap Cache</h3>";
$cacheFiles = glob($basePath . '/bootstrap/cache/*.php');
if (!empty($cacheFiles)) {
    echo "<p style='color:orange;'>⚠ Found cached files: " . htmlspecialchars(implode(', ', array_map('basename', $cacheFiles))) . "</p>";
    foreach ($cacheFiles as $cf) {
        @unlink($cf);
        echo "<p style='color:green;'>✔ Deleted " . htmlspecialchars(basename($cf)) . "</p>";
    }
} else {
    echo "<p style='color:green;'>✔ Bootstrap cache is completely clean (no stale config.php).</p>";
}

echo "<p>getenv('DB_CONNECTION'): <strong>" . var_export(getenv('DB_CONNECTION'), true) . "</strong></p>";
echo "<p>\$_SERVER['DB_CONNECTION']: <strong>" . var_export($_SERVER['DB_CONNECTION'] ?? null, true) . "</strong></p>";
echo "<p>\$_ENV['DB_CONNECTION']: <strong>" . var_export($_ENV['DB_CONNECTION'] ?? null, true) . "</strong></p>";

// 4. Test Laravel's configuration
echo "<h3>4. Laravel Database Configuration</h3>";
try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    echo "<p>Laravel default connection: <strong>" . config('database.default') . "</strong></p>";
    echo "<p>Laravel env('DB_CONNECTION'): <strong>" . env('DB_CONNECTION') . "</strong></p>";
    
    $defaultConn = config('database.default');
    $connConfig = config("database.connections.{$defaultConn}");
    if ($connConfig) {
        unset($connConfig['password']);
        echo "<pre style='background:#f4f4f4;padding:10px;'>Active Connection Config: " . htmlspecialchars(json_encode($connConfig, JSON_PRETTY_PRINT)) . "</pre>";
    }

    $db = \Illuminate\Support\Facades\DB::connection();
    $db->getPdo();
    echo "<p style='color:green;'>✔ Laravel DB::connection() Connected Successfully!</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>✘ Laravel Connection Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

