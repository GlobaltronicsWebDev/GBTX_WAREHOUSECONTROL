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
} else {
    echo "<p style='color:red;'>✘ .env file DOES NOT EXIST at {$envFile}!</p>";
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

// 3. Test Laravel's configuration
echo "<h3>3. Laravel Database Configuration</h3>";
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

