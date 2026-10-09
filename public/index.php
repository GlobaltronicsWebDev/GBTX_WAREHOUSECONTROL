<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = realpath(__DIR__.'/..') ?: dirname(__DIR__);

// Auto-restore .env if missing
if (!file_exists($basePath.'/.env')) {
    $defaultEnv = <<<EOT
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
    @file_put_contents($basePath.'/.env', $defaultEnv);
    @chmod($basePath.'/.env', 0644);
}

// Graceful check if vendor dependencies exist
if (!file_exists($basePath.'/vendor/autoload.php')) {
    http_response_code(503);
    echo '<div style="font-family:sans-serif;max-width:600px;margin:80px auto;padding:30px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;text-align:center;">'
        .'<h2 style="color:#0f172a;margin-bottom:8px;">Globaltronics Warehouse System</h2>'
        .'<p style="color:#64748b;font-size:14px;line-height:1.6;">Vendor packages are being initialized. Please run <code>composer install</code> in terminal or reload this page in a moment.</p>'
        .'</div>';
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());

